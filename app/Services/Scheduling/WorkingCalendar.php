<?php

namespace App\Services\Scheduling;

use App\Models\Calendar;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Schedules instants using the working windows of a project calendar.
 *
 * All arithmetic is done on an absolute minute timeline so that a day may hold
 * more than one working window (a lunch break, or a night shift that crosses
 * midnight) and so that exceptions such as holidays replace a day wholesale.
 */
class WorkingCalendar
{
    /**
     * Upper bound of days walked before the calendar is treated as unusable.
     */
    private const MAX_DAYS = 3660;

    /**
     * Working windows per ISO weekday (1 = Monday ... 7 = Sunday).
     *
     * @var array<int, list<array{0: int, 1: int}>>
     */
    private array $week = [];

    /**
     * Working windows per date, overriding the weekday definition.
     *
     * @var array<string, list<array{0: int, 1: int}>>
     */
    private array $exceptions = [];

    public function __construct(private readonly Calendar $calendar)
    {
        foreach ($calendar->days as $day) {
            $this->week[(int) $day->day_of_week] = $this->normalizeSegments($day->segments ?? []);
        }

        foreach ($calendar->exceptions as $exception) {
            $this->exceptions[$exception->date->toDateString()] = $this->normalizeSegments($exception->segments ?? []);
        }
    }

    /**
     * The calendar the working windows come from.
     */
    public function calendar(): Calendar
    {
        return $this->calendar;
    }

    /**
     * The working windows of a given day.
     *
     * @return list<array{0: int, 1: int}>
     */
    public function segmentsFor(CarbonInterface $date): array
    {
        $key = $date->toDateString();

        if (array_key_exists($key, $this->exceptions)) {
            return $this->exceptions[$key];
        }

        return $this->week[(int) $date->isoFormat('E')] ?? [];
    }

    /**
     * Whether the given day has working time.
     */
    public function isWorkingDay(CarbonInterface $date): bool
    {
        return $this->segmentsFor($date) !== [];
    }

    /**
     * The working minutes available on the given day.
     */
    public function minutesOn(CarbonInterface $date): int
    {
        return array_sum(array_map(
            fn (array $segment) => $segment[1] - $segment[0],
            $this->segmentsFor($date),
        ));
    }

    /**
     * The number of working minutes between two instants.
     */
    public function minutesBetween(CarbonInterface $start, CarbonInterface $end): int
    {
        $from = $this->toMinute($start);
        $to = $this->toMinute($end);

        if ($to <= $from) {
            return 0;
        }

        $total = 0;

        foreach ($this->overlappingIntervals($from, $to) as [$intervalStart, $intervalEnd]) {
            $total += $intervalEnd - $intervalStart;
        }

        return $total;
    }

    /**
     * Move an instant forward by the given amount of working minutes.
     */
    public function add(CarbonInterface $start, int $minutes): CarbonImmutable
    {
        $minute = $this->toMinute($start);

        if ($minutes < 0) {
            return $this->subtract($start, -$minutes);
        }

        if ($minutes === 0) {
            return $this->toCarbon($minute);
        }

        $remaining = $minutes;
        $day = $this->toCarbon($minute)->startOfDay();

        for ($dayIndex = 0; $dayIndex < self::MAX_DAYS; $dayIndex++) {
            $dayMinute = $this->toMinute($day);

            foreach ($this->segmentsFor($day) as [$segmentStart, $segmentEnd]) {
                $segmentStartMinute = $dayMinute + $segmentStart;
                $segmentEndMinute = $dayMinute + $segmentEnd;

                if ($minute >= $segmentEndMinute) {
                    continue;
                }

                if ($minute < $segmentStartMinute) {
                    $minute = $segmentStartMinute;
                }

                $available = $segmentEndMinute - $minute;

                if ($available >= $remaining) {
                    return $this->toCarbon($minute + $remaining);
                }

                $remaining -= $available;
                $minute = $segmentEndMinute;
            }

            $day = $day->addDay();
        }

        throw new InvalidArgumentException('O calendário não possui horário útil suficiente para concluir o cálculo.');
    }

    /**
     * Move an instant backward by the given amount of working minutes.
     */
    public function subtract(CarbonInterface $end, int $minutes): CarbonImmutable
    {
        $minute = $this->toMinute($end);

        if ($minutes < 0) {
            return $this->add($end, -$minutes);
        }

        if ($minutes === 0) {
            return $this->toCarbon($minute);
        }

        $remaining = $minutes;
        $day = $this->toCarbon($minute)->startOfDay();

        for ($dayIndex = 0; $dayIndex < self::MAX_DAYS; $dayIndex++) {
            $dayMinute = $this->toMinute($day);

            foreach (array_reverse($this->segmentsFor($day)) as [$segmentStart, $segmentEnd]) {
                $segmentStartMinute = $dayMinute + $segmentStart;
                $segmentEndMinute = $dayMinute + $segmentEnd;

                if ($minute <= $segmentStartMinute) {
                    continue;
                }

                if ($minute > $segmentEndMinute) {
                    $minute = $segmentEndMinute;
                }

                $available = $minute - $segmentStartMinute;

                if ($available >= $remaining) {
                    return $this->toCarbon($minute - $remaining);
                }

                $remaining -= $available;
                $minute = $segmentStartMinute;
            }

            $day = $day->subDay();
        }

        throw new InvalidArgumentException('O calendário não possui horário útil suficiente para concluir o cálculo.');
    }

    /**
     * Snap an instant forward to the next moment inside a working window.
     */
    public function nextWorkingInstant(CarbonInterface $moment): CarbonImmutable
    {
        $minute = $this->toMinute($moment);
        $day = $this->toCarbon($minute)->startOfDay();

        for ($dayIndex = 0; $dayIndex < self::MAX_DAYS; $dayIndex++) {
            $dayMinute = $this->toMinute($day);

            foreach ($this->segmentsFor($day) as [$segmentStart, $segmentEnd]) {
                $segmentStartMinute = $dayMinute + $segmentStart;
                $segmentEndMinute = $dayMinute + $segmentEnd;

                if ($minute < $segmentStartMinute) {
                    return $this->toCarbon($segmentStartMinute);
                }

                if ($minute < $segmentEndMinute) {
                    return $this->toCarbon($minute);
                }
            }

            $day = $day->addDay();
        }

        throw new InvalidArgumentException('O calendário não possui nenhum horário útil configurado.');
    }

    /**
     * Snap an instant backward to the previous moment inside a working window.
     */
    public function previousWorkingInstant(CarbonInterface $moment): CarbonImmutable
    {
        $minute = $this->toMinute($moment);
        $day = $this->toCarbon($minute)->startOfDay();

        for ($dayIndex = 0; $dayIndex < self::MAX_DAYS; $dayIndex++) {
            $dayMinute = $this->toMinute($day);

            foreach (array_reverse($this->segmentsFor($day)) as [$segmentStart, $segmentEnd]) {
                $segmentStartMinute = $dayMinute + $segmentStart;
                $segmentEndMinute = $dayMinute + $segmentEnd;

                if ($minute > $segmentEndMinute) {
                    return $this->toCarbon($segmentEndMinute);
                }

                if ($minute > $segmentStartMinute) {
                    return $this->toCarbon($minute);
                }
            }

            $day = $day->subDay();
        }

        throw new InvalidArgumentException('O calendário não possui nenhum horário útil configurado.');
    }

    /**
     * The first working instant on or after the start of a day.
     */
    public function startOfDay(CarbonInterface $date): CarbonImmutable
    {
        return $this->nextWorkingInstant($date->toImmutable()->startOfDay());
    }

    /**
     * The last working instant on or before the end of a day.
     */
    public function endOfDay(CarbonInterface $date): CarbonImmutable
    {
        $day = $date->toImmutable()->startOfDay();

        return $this->isWorkingDay($day)
            ? $this->add($day, $this->minutesOn($day))
            : $this->previousWorkingInstant($day);
    }

    /**
     * The working days covered by two dates, inclusive.
     *
     * @return list<string>
     */
    public function workingDaysBetween(CarbonInterface $start, CarbonInterface $end): array
    {
        $days = [];
        $cursor = $start->toImmutable()->startOfDay();
        $last = $end->toImmutable()->startOfDay();

        for ($dayIndex = 0; $cursor->lte($last) && $dayIndex < self::MAX_DAYS; $dayIndex++) {
            if ($this->isWorkingDay($cursor)) {
                $days[] = $cursor->toDateString();
            }

            $cursor = $cursor->addDay();
        }

        return $days;
    }

    /**
     * The working intervals that overlap an absolute minute range.
     *
     * @return Generator<array{0: int, 1: int}>
     */
    private function overlappingIntervals(int $from, int $to): \Generator
    {
        $day = $this->toCarbon($from)->startOfDay();

        for ($dayIndex = 0; $dayIndex < self::MAX_DAYS; $dayIndex++) {
            $dayMinute = $this->toMinute($day);

            if ($dayMinute >= $to) {
                break;
            }

            foreach ($this->segmentsFor($day) as [$segmentStart, $segmentEnd]) {
                $segmentStartMinute = $dayMinute + $segmentStart;
                $segmentEndMinute = $dayMinute + $segmentEnd;

                if ($segmentEndMinute <= $from) {
                    continue;
                }

                if ($segmentStartMinute >= $to) {
                    break;
                }

                yield [max($segmentStartMinute, $from), min($segmentEndMinute, $to)];
            }

            $day = $day->addDay();
        }
    }

    /**
     * Sort and clean the working windows of a day.
     *
     * @param  array<mixed>  $segments
     * @return list<array{0: int, 1: int}>
     */
    private function normalizeSegments(array $segments): array
    {
        $normalized = [];

        foreach ($segments as $segment) {
            $start = (int) ($segment[0] ?? 0);
            $end = (int) ($segment[1] ?? 0);

            if ($end > $start) {
                $normalized[] = [$start, $end];
            }
        }

        usort($normalized, fn (array $a, array $b) => $a[0] <=> $b[0]);

        return $normalized;
    }

    /**
     * Convert an instant to absolute minutes since the epoch.
     */
    private function toMinute(CarbonInterface $moment): int
    {
        return (int) floor($moment->getTimestamp() / 60);
    }

    /**
     * Convert absolute minutes since the epoch back to an instant.
     */
    private function toCarbon(int $minute): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp($minute * 60);
    }
}
