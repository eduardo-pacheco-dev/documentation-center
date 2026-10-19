<?php

namespace App\Services\Agenda;

use Carbon\CarbonInterface;

/**
 * Arranges agenda occurrences into the daily cells and positioned time slots
 * used by the month, week and day views.
 */
class AgendaGridBuilder
{
    /**
     * The pixel height of a single hour in the week and day grids.
     */
    public const HOUR_HEIGHT = 48;

    /**
     * Build the day cells for the given window.
     *
     * @param  list<AgendaOccurrence>  $occurrences
     * @return array<string, array{
     *     date: CarbonInterface,
     *     is_today: bool,
     *     occurrences: list<AgendaOccurrence>,
     *     all_day: list<AgendaOccurrence>,
     *     timed: list<array<string, mixed>>
     * }>
     */
    public function build(array $occurrences, CarbonInterface $start, CarbonInterface $end): array
    {
        $days = [];
        $cursor = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($last)) {
            $days[$cursor->toDateString()] = [
                'date' => $cursor->copy(),
                'is_today' => $cursor->isToday(),
                'occurrences' => [],
                'all_day' => [],
                'timed_raw' => [],
            ];
            $cursor->addDay();
        }

        foreach ($occurrences as $occurrence) {
            $this->place($days, $occurrence);
        }

        foreach ($days as $key => $day) {
            usort($day['occurrences'], fn (AgendaOccurrence $a, AgendaOccurrence $b): int => $a->start <=> $b->start);
            $days[$key]['timed'] = $this->layout($day['timed_raw']);
            unset($days[$key]['timed_raw']);
        }

        return $days;
    }

    /**
     * Place a single occurrence on each day it touches.
     *
     * @param  array<string, array<string, mixed>>  $days
     */
    private function place(array &$days, AgendaOccurrence $occurrence): void
    {
        $firstDay = $occurrence->start->copy()->startOfDay();
        $lastDay = $occurrence->end->copy()->subSecond()->startOfDay();
        $spansDays = ! $occurrence->start->isSameDay($occurrence->end);
        $isBand = $occurrence->isAllDay() || $spansDays;

        $cursor = $firstDay->copy();

        while ($cursor->lessThanOrEqualTo($lastDay)) {
            $key = $cursor->toDateString();

            if (! isset($days[$key])) {
                $cursor->addDay();

                continue;
            }

            $days[$key]['occurrences'][] = $occurrence;

            if ($isBand) {
                $days[$key]['all_day'][] = $occurrence;
            } else {
                $dayStart = $cursor->copy()->startOfDay();
                $dayEndExclusive = $cursor->copy()->addDay()->startOfDay();
                $segmentStart = $occurrence->start->greaterThan($dayStart) ? $occurrence->start->copy() : $dayStart;
                $segmentEnd = $occurrence->end->lessThan($dayEndExclusive) ? $occurrence->end->copy() : $dayEndExclusive;

                $startMinutes = ($segmentStart->hour * 60) + $segmentStart->minute;
                $endMinutes = $segmentEnd->isSameDay($dayStart)
                    ? ($segmentEnd->hour * 60) + $segmentEnd->minute
                    : 1440;

                $days[$key]['timed_raw'][] = [
                    'occurrence' => $occurrence,
                    'start' => max(0, $startMinutes),
                    'end' => min(1440, max($startMinutes + 15, $endMinutes)),
                ];
            }

            $cursor->addDay();
        }
    }

    /**
     * Lay out the timed segments of a day into columns.
     *
     * @param  list<array<string, mixed>>  $segments
     * @return list<array<string, mixed>>
     */
    private function layout(array $segments): array
    {
        usort($segments, fn (array $a, array $b): int => [$a['start'], $a['end']] <=> [$b['start'], $b['end']]);

        $result = [];
        $cluster = [];
        $clusterEnd = null;

        foreach ($segments as $segment) {
            if ($clusterEnd !== null && $segment['start'] >= $clusterEnd) {
                $result = array_merge($result, $this->assignColumns($cluster));
                $cluster = [];
                $clusterEnd = null;
            }

            $cluster[] = $segment;
            $clusterEnd = max($clusterEnd ?? 0, $segment['end']);
        }

        if ($cluster !== []) {
            $result = array_merge($result, $this->assignColumns($cluster));
        }

        return $result;
    }

    /**
     * Assign each segment of a cluster to a column.
     *
     * @param  list<array<string, mixed>>  $cluster
     * @return list<array<string, mixed>>
     */
    private function assignColumns(array $cluster): array
    {
        $columns = [];
        $placed = [];

        foreach ($cluster as $segment) {
            $column = null;

            foreach ($columns as $index => $endMinutes) {
                if ($endMinutes <= $segment['start']) {
                    $column = $index;

                    break;
                }
            }

            if ($column === null) {
                $column = count($columns);
            }

            $columns[$column] = $segment['end'];
            $placed[] = ['segment' => $segment, 'column' => $column];
        }

        $count = max(1, count($columns));
        $width = 100 / $count;
        $timed = [];

        foreach ($placed as $item) {
            $segment = $item['segment'];
            $timed[] = [
                'occurrence' => $segment['occurrence'],
                'start' => $segment['start'],
                'end' => $segment['end'],
                'top' => $segment['start'] / 60 * self::HOUR_HEIGHT,
                'height' => max(18, ($segment['end'] - $segment['start']) / 60 * self::HOUR_HEIGHT),
                'left' => $item['column'] * $width,
                'width' => $width,
            ];
        }

        return $timed;
    }
}
