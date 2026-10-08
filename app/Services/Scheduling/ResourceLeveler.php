<?php

namespace App\Services\Scheduling;

use App\Models\Project;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Task;
use Carbon\CarbonImmutable;

/**
 * Removes resource overallocations by pushing tasks later on the calendar.
 *
 * Leveling only delays tasks: it never moves a task earlier, never breaks a
 * dependency and never touches manually scheduled tasks. The delay is stored on
 * the task so that later recalculations keep the leveled position.
 */
class ResourceLeveler
{
    /**
     * Safety valve for pathological plans.
     */
    private const MAX_ITERATIONS = 60;

    /**
     * The priority above which a task is never moved while leveling.
     */
    public const PROTECTED_PRIORITY = 1000;

    public function __construct(private readonly ProjectScheduler $scheduler, private readonly WorkingCalendarFactory $calendars) {}

    /**
     * Level the project resources.
     *
     * @return array{shifted: int, iterations: int, remaining: int}
     */
    public function level(Project $project): array
    {
        $project->tasks()->where('level_delay_minutes', '>', 0)->update(['level_delay_minutes' => 0]);
        $this->scheduler->schedule($project);

        $calendar = $this->calendars->for($project);
        $shifted = 0;
        $iterations = 0;

        while ($iterations < self::MAX_ITERATIONS) {
            $iterations++;

            $conflict = $this->firstResolvableConflict($project, $calendar);

            if ($conflict === null) {
                break;
            }

            [$taskId, $shift] = $conflict;

            Task::query()->whereKey($taskId)->increment('level_delay_minutes', $shift);
            $this->scheduler->schedule($project);

            $shifted++;
        }

        return [
            'shifted' => $shifted,
            'iterations' => $iterations,
            'remaining' => count($this->conflicts($project)),
        ];
    }

    /**
     * The first conflict that a delay can still resolve, with the task to move.
     *
     * @return array{0: int, 1: int}|null
     */
    private function firstResolvableConflict(Project $project, WorkingCalendar $calendar): ?array
    {
        foreach ($this->conflicts($project) as $conflict) {
            $task = $this->taskToShift($conflict['tasks']);

            if ($task === null) {
                continue;
            }

            $shift = $calendar->minutesBetween($task->start_at, $conflict['to']);

            if ($shift <= 0) {
                continue;
            }

            return [$task->getKey(), $shift];
        }

        return null;
    }

    /**
     * The overallocated windows of every schedulable resource.
     *
     * @return list<array{resource: resource, tasks: list<Task>, from: CarbonImmutable, to: CarbonImmutable}>
     */
    private function conflicts(Project $project): array
    {
        $tasks = $project->tasks()->get()->keyBy('id');
        $assignments = ResourceAssignment::query()->whereIn('task_id', $tasks->keys())->get();

        $byResource = $assignments->groupBy('resource_id');
        $resources = Resource::query()
            ->whereBelongsTo($project)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $conflicts = [];

        foreach ($byResource as $resourceId => $rows) {
            $resource = $resources->get($resourceId);

            if ($resource === null || ! $resource->type->isSchedulable()) {
                continue;
            }

            $capacity = (float) $resource->max_units;

            if ($capacity <= 0) {
                continue;
            }

            $slots = [];

            foreach ($rows as $row) {
                $task = $tasks->get($row->task_id);

                if ($task === null || $task->start_at === null || $task->finish_at === null || $task->is_milestone) {
                    continue;
                }

                $slots[] = [
                    'task' => $task,
                    'units' => (float) $row->units,
                    'from' => $task->start_at,
                    'to' => $task->finish_at,
                ];
            }

            if (count($slots) < 2) {
                continue;
            }

            $boundaries = [];

            foreach ($slots as $slot) {
                $boundaries[] = $slot['from']->getTimestamp();
                $boundaries[] = $slot['to']->getTimestamp();
            }

            $boundaries = array_values(array_unique($boundaries));
            sort($boundaries);

            for ($index = 0; $index < count($boundaries) - 1; $index++) {
                $from = $boundaries[$index];
                $to = $boundaries[$index + 1];

                $inUse = 0;
                $used = [];

                foreach ($slots as $slot) {
                    if ($slot['from']->getTimestamp() <= $from && $slot['to']->getTimestamp() >= $to) {
                        $inUse += $slot['units'];
                        $used[] = $slot['task'];
                    }
                }

                if ($inUse > $capacity + 0.001 && count($used) > 1) {
                    $conflicts[] = [
                        'resource' => $resource,
                        'tasks' => $used,
                        'from' => CarbonImmutable::createFromTimestamp($from),
                        'to' => CarbonImmutable::createFromTimestamp($to),
                    ];
                }
            }
        }

        usort($conflicts, fn (array $a, array $b) => [$a['from']->getTimestamp(), $a['resource']->id] <=> [$b['from']->getTimestamp(), $b['resource']->id]);

        return $conflicts;
    }

    /**
     * The task that should absorb the delay of a conflicting group.
     *
     * @param  list<Task>  $tasks
     */
    private function taskToShift(array $tasks): ?Task
    {
        $candidates = array_filter(
            $tasks,
            fn (Task $task) => ! $task->is_milestone
                && $task->scheduling_mode->isAutoScheduled()
                && (int) $task->priority < self::PROTECTED_PRIORITY,
        );

        if ($candidates === []) {
            return null;
        }

        usort($candidates, function (Task $a, Task $b): int {
            return ($a->priority <=> $b->priority)
                ?: ($b->total_slack_minutes <=> $a->total_slack_minutes)
                ?: ($b->start_at <=> $a->start_at)
                ?: ($b->id <=> $a->id);
        });

        return $candidates[0] ?? null;
    }
}
