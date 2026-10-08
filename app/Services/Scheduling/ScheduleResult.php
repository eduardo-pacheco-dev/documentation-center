<?php

namespace App\Services\Scheduling;

use Carbon\CarbonImmutable;
use OverflowException;

/**
 * The outcome of a critical path calculation for a whole project.
 */
class ScheduleResult
{
    /**
     * @param  array<int, TaskSchedule>  $schedules  dates keyed by task id
     * @param  array<int, list<TaskSchedule>>  $summaries  rollups keyed by summary task id
     */
    public function __construct(
        private readonly array $schedules,
        private readonly array $summaries,
        private readonly CarbonImmutable $finish,
    ) {}

    /**
     * The dates of a task that took part in the calculation.
     */
    public function for(int $taskId): TaskSchedule
    {
        if (! isset($this->schedules[$taskId])) {
            throw new OverflowException("A tarefa {$taskId} não participou do cálculo do cronograma.");
        }

        return $this->schedules[$taskId];
    }

    /**
     * Whether the task took part in the calculation.
     */
    public function has(int $taskId): bool
    {
        return isset($this->schedules[$taskId]);
    }

    /**
     * The dates of every scheduled task, keyed by task id.
     *
     * @return array<int, TaskSchedule>
     */
    public function all(): array
    {
        return $this->schedules;
    }

    /**
     * The rollup dates of every summary task, keyed by task id.
     *
     * @return array<int, TaskSchedule>
     */
    public function summaryRollups(): array
    {
        return $this->summaries;
    }

    /**
     * The earliest date at which the whole project can finish.
     */
    public function finish(): CarbonImmutable
    {
        return $this->finish;
    }

    /**
     * The earliest date at which the project can start.
     */
    public function start(): CarbonImmutable
    {
        if ($this->schedules === []) {
            return $this->finish;
        }

        $starts = array_map(fn (TaskSchedule $schedule) => $schedule->start, $this->schedules);

        return collect($starts)->min();
    }
}
