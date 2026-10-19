<?php

namespace App\Services\Agenda;

use App\Enums\AgendaRecurrenceFrequency;
use App\Models\AgendaEvent;
use App\Models\AgendaEventException;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Expands agenda events, including their recurrence rules, into the concrete
 * occurrences that fall inside a date window.
 */
class AgendaEventExpander
{
    /**
     * The hard limit of generated candidate dates per recurring event.
     */
    private const ITERATION_LIMIT = 750;

    /**
     * Expand a set of events into the occurrences inside the given window.
     *
     * @param  Collection<int, AgendaEvent>  $events
     * @return list<AgendaOccurrence>
     */
    public function expandMany(Collection $events, CarbonInterface $from, CarbonInterface $to): array
    {
        $occurrences = [];

        foreach ($events as $event) {
            foreach ($this->expand($event, $from, $to) as $occurrence) {
                $occurrences[] = $occurrence;
            }
        }

        usort($occurrences, fn (AgendaOccurrence $a, AgendaOccurrence $b): int => $a->start <=> $b->start);

        return $occurrences;
    }

    /**
     * Expand a single event into the occurrences inside the given window.
     *
     * @return list<AgendaOccurrence>
     */
    public function expand(AgendaEvent $event, CarbonInterface $from, CarbonInterface $to): array
    {
        $event->loadMissing(['exceptions', 'calendar']);

        if (! $event->isRecurring()) {
            return $this->overlaps($event->start_at, $event->end_at, $from, $to)
                ? [new AgendaOccurrence($event, $event->start_at, $event->end_at, false)]
                : [];
        }

        $exceptions = $event->exceptions->keyBy(
            fn (AgendaEventException $exception): string => $exception->recurrence_id->toDateTimeString(),
        );

        $duration = $event->durationInMinutes();
        $occurrences = [];

        foreach ($this->occurrenceStarts($event, $to) as $start) {
            $recurrenceId = $start->copy();
            $exception = $exceptions->get($recurrenceId->toDateTimeString());

            if ($exception !== null) {
                if ($exception->is_cancelled) {
                    continue;
                }

                $overrides = $exception->overrides ?? [];
                $instanceStart = isset($overrides['start_at']) ? Carbon::parse($overrides['start_at']) : $start->copy();
                $instanceEnd = isset($overrides['end_at'])
                    ? Carbon::parse($overrides['end_at'])
                    : $instanceStart->copy()->addMinutes($duration);

                if ($this->overlaps($instanceStart, $instanceEnd, $from, $to)) {
                    $occurrences[] = new AgendaOccurrence($event, $instanceStart, $instanceEnd, true, $recurrenceId, $overrides);
                }

                continue;
            }

            $end = $start->copy()->addMinutes($duration);

            if ($this->overlaps($start, $end, $from, $to)) {
                $occurrences[] = new AgendaOccurrence($event, $start->copy(), $end, true, $recurrenceId);
            }
        }

        return $occurrences;
    }

    /**
     * Generate the start date-times of the recurring occurrences up to the window end.
     *
     * @return list<Carbon>
     */
    private function occurrenceStarts(AgendaEvent $event, CarbonInterface $to): array
    {
        $recurrence = $event->recurrence ?? [];
        $frequency = AgendaRecurrenceFrequency::tryFrom($recurrence['frequency'] ?? '') ?? AgendaRecurrenceFrequency::Daily;
        $interval = max(1, (int) ($recurrence['interval'] ?? 1));
        $count = isset($recurrence['count']) ? max(1, (int) $recurrence['count']) : null;
        $until = $event->recurrence_until?->copy()->endOfDay()
            ?? (isset($recurrence['until']) && $recurrence['until'] !== null ? Carbon::parse($recurrence['until'])->endOfDay() : null);
        $weekdays = array_map('intval', $recurrence['byweekday'] ?? []);
        $monthDays = array_map('intval', $recurrence['bymonthday'] ?? []);

        $anchor = $event->start_at->copy();
        $starts = [];

        for ($period = 0; $period < $this->periodLimit($anchor, $to, $frequency, $interval); $period++) {
            foreach ($this->candidatesFor($anchor, $period, $frequency, $interval, $weekdays, $monthDays) as $candidate) {
                if ($candidate->lessThan($anchor)) {
                    continue;
                }

                if ($until !== null && $candidate->greaterThan($until)) {
                    continue;
                }

                $starts[] = $candidate;

                if ($count !== null && count($starts) >= $count) {
                    return $starts;
                }

                if ($candidate->greaterThan($to)) {
                    return $starts;
                }
            }
        }

        return $starts;
    }

    /**
     * The candidate dates produced by a recurrence period.
     *
     * @param  list<int>  $weekdays
     * @param  list<int>  $monthDays
     * @return list<Carbon>
     */
    private function candidatesFor(
        Carbon $anchor,
        int $period,
        AgendaRecurrenceFrequency $frequency,
        int $interval,
        array $weekdays,
        array $monthDays,
    ): array {
        return match ($frequency) {
            AgendaRecurrenceFrequency::Daily => [$anchor->copy()->addDays($period * $interval)],
            AgendaRecurrenceFrequency::Weekly => $this->weeklyCandidates($anchor, $period, $interval, $weekdays),
            AgendaRecurrenceFrequency::Monthly => $this->monthlyCandidates($anchor, $period, $interval, $monthDays),
            AgendaRecurrenceFrequency::Yearly => [$anchor->copy()->addYearsNoOverflow($period * $interval)],
        };
    }

    /**
     * @param  list<int>  $weekdays
     * @return list<Carbon>
     */
    private function weeklyCandidates(Carbon $anchor, int $period, int $interval, array $weekdays): array
    {
        $weekStart = $anchor->copy()->startOfWeek()->addWeeks($period * $interval);

        if ($weekdays === []) {
            return [$anchor->copy()->addWeeks($period * $interval)];
        }

        sort($weekdays);
        $candidates = [];

        foreach ($weekdays as $weekday) {
            $candidate = $weekStart->copy()->addDays($weekday - 1);
            $candidate->setTime($anchor->hour, $anchor->minute, $anchor->second);
            $candidates[] = $candidate;
        }

        return $candidates;
    }

    /**
     * @param  list<int>  $monthDays
     * @return list<Carbon>
     */
    private function monthlyCandidates(Carbon $anchor, int $period, int $interval, array $monthDays): array
    {
        $monthStart = $anchor->copy()->startOfMonth()->addMonthsNoOverflow($period * $interval);

        if ($monthDays === []) {
            return [$anchor->copy()->addMonthsNoOverflow($period * $interval)];
        }

        sort($monthDays);
        $candidates = [];

        foreach ($monthDays as $monthDay) {
            $day = min(max(1, $monthDay), $monthStart->daysInMonth);
            $candidate = $monthStart->copy()->addDays($day - 1);
            $candidate->setTime($anchor->hour, $anchor->minute, $anchor->second);
            $candidates[] = $candidate;
        }

        return $candidates;
    }

    /**
     * How many recurrence periods must be generated to reach the window end.
     */
    private function periodLimit(Carbon $anchor, CarbonInterface $to, AgendaRecurrenceFrequency $frequency, int $interval): int
    {
        if ($to->lessThanOrEqualTo($anchor)) {
            return 1;
        }

        $periods = match ($frequency) {
            AgendaRecurrenceFrequency::Daily => $anchor->diffInDays($to),
            AgendaRecurrenceFrequency::Weekly => $anchor->diffInWeeks($to),
            AgendaRecurrenceFrequency::Monthly => $anchor->diffInMonths($to),
            AgendaRecurrenceFrequency::Yearly => $anchor->diffInYears($to),
        };

        return (int) min(self::ITERATION_LIMIT, ceil($periods / $interval) + 2);
    }

    /**
     * Determine whether a time span intersects the given window.
     */
    private function overlaps(CarbonInterface $start, CarbonInterface $end, CarbonInterface $from, CarbonInterface $to): bool
    {
        return $start->lessThan($to) && $end->greaterThan($from);
    }
}
