<?php

namespace App\Services\Scheduling;

use App\Enums\DependencyType;
use App\Enums\SchedulingMode;
use App\Enums\TaskConstraint;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Calculates the critical path of a project.
 *
 * The calculation runs on working minutes only: every date is moved with the
 * project calendar, so weekends, holidays and lunch breaks never consume
 * duration. Summary tasks are not scheduled; they roll up from their children.
 */
class CpmEngine
{
    /**
     * Default duration used by a leaf task that does not declare one.
     */
    public const DEFAULT_DURATION_MINUTES = 480;

    /**
     * @param  iterable<Task>  $tasks
     * @param  iterable<TaskDependency>  $dependencies
     */
    public function run(Project $project, iterable $tasks, iterable $dependencies, WorkingCalendar $calendar): ScheduleResult
    {
        $tasks = $tasks instanceof Collection ? $tasks->all() : array_values((array) $tasks);
        $dependencies = $dependencies instanceof Collection ? $dependencies->all() : array_values((array) $dependencies);

        $byId = [];
        $children = [];

        foreach ($tasks as $task) {
            $byId[$task->getKey()] = $task;

            if ($task->parent_id !== null) {
                $children[$task->parent_id][] = $task->getKey();
            }
        }

        $summaries = array_keys($children);
        $leafIds = array_values(array_diff(array_keys($byId), $summaries));

        [$incoming, $outgoing] = $this->buildEdges($byId, $children, $dependencies);
        $order = $this->topologicalOrder($leafIds, $outgoing, $byId);

        $projectStart = $calendar->startOfDay($project->start_date);

        $earliest = $this->forwardPass($byId, $order, $incoming, $projectStart, $calendar);

        $projectFinish = $projectStart;

        foreach ($earliest as [$start, $finish]) {
            $projectFinish = $finish->greaterThan($projectFinish) ? $finish : $projectFinish;
        }

        $latest = $this->backwardPass($byId, $order, $outgoing, $earliest, $projectFinish, $calendar);

        $schedules = [];

        foreach ($order as $taskId) {
            $task = $byId[$taskId];
            [$start, $finish] = $earliest[$taskId];
            [$latestStart] = $latest[$taskId];

            if ($task->constraint_type === TaskConstraint::AsLateAsPossible) {
                $start = $calendar->nextWorkingInstant($latestStart);
                $finish = $calendar->add($start, $duration);
            }

            $slack = $this->slackInMinutes($calendar, $start, $latestStart);
            $freeSlack = $this->freeSlack($task, $outgoing[$taskId] ?? [], $earliest, $latest[$taskId], $calendar);

            $schedules[$taskId] = new TaskSchedule(
                start: $start,
                finish: $finish,
                totalSlack: $slack,
                freeSlack: $freeSlack,
                critical: $slack <= 0,
            );
        }

        return new ScheduleResult(
            schedules: $schedules,
            summaries: $this->rollupSummaries($byId, $children, $schedules),
            finish: $projectFinish,
        );
    }

    /**
     * Expand summary tasks into their leaf descendants and index the edges.
     *
     * @param  array<int, Task>  $byId
     * @param  array<int, list<int>>  $children
     * @param  list<TaskDependency>  $dependencies
     * @return array{0: array<int, list<array{pred: int, type: DependencyType, lag: int}>>, 1: array<int, list<array{succ: int, type: DependencyType, lag: int}>>}
     */
    private function buildEdges(array $byId, array $children, array $dependencies): array
    {
        $incoming = [];
        $outgoing = [];

        foreach ($dependencies as $dependency) {
            if (! isset($byId[$dependency->predecessor_id]) || ! isset($byId[$dependency->successor_id])) {
                continue;
            }

            foreach ($this->descendantLeaves($dependency->predecessor_id, $children) as $predecessorId) {
                foreach ($this->descendantLeaves($dependency->successor_id, $children) as $successorId) {
                    if ($predecessorId === $successorId) {
                        continue;
                    }

                    $edge = [
                        'pred' => $predecessorId,
                        'type' => $dependency->type,
                        'lag' => $dependency->lag_minutes,
                    ];

                    $incoming[$successorId][] = $edge;
                    $outgoing[$predecessorId][] = $edge + ['succ' => $successorId];
                }
            }
        }

        return [$incoming, $outgoing];
    }

    /**
     * The leaf tasks covered by a task, or the task itself when it is a leaf.
     *
     * @param  array<int, list<int>>  $children
     * @return list<int>
     */
    private function descendantLeaves(int $taskId, array $children): array
    {
        if (! isset($children[$taskId])) {
            return [$taskId];
        }

        $leaves = [];

        foreach ($children[$taskId] as $childId) {
            foreach ($this->descendantLeaves($childId, $children) as $leafId) {
                $leaves[] = $leafId;
            }
        }

        return $leaves === [] ? [$taskId] : $leaves;
    }

    /**
     * Order the tasks so that every task comes after its predecessors.
     *
     * @param  list<int>  $nodeIds
     * @param  array<int, list<array{succ: int, type: DependencyType, lag: int}>>  $outgoing
     * @param  array<int, Task>  $byId
     * @return list<int>
     */
    private function topologicalOrder(array $nodeIds, array $outgoing, array $byId): array
    {
        $nodeSet = array_flip($nodeIds);
        $indegree = array_fill_keys($nodeIds, 0);
        $adjacency = [];

        foreach ($outgoing as $predecessorId => $edges) {
            if (! isset($nodeSet[$predecessorId])) {
                continue;
            }

            foreach ($edges as $edge) {
                $successorId = $edge['succ'];

                if (! isset($nodeSet[$successorId])) {
                    continue;
                }

                $adjacency[$predecessorId][] = $successorId;
                $indegree[$successorId]++;
            }
        }

        $queue = array_values(array_filter($nodeIds, fn (int $id) => $indegree[$id] === 0));
        sort($queue);

        $order = [];

        while ($queue !== []) {
            $current = array_shift($queue);
            $order[] = $current;

            foreach ($adjacency[$current] ?? [] as $successorId) {
                $indegree[$successorId]--;

                if ($indegree[$successorId] === 0) {
                    $queue[] = $successorId;
                }
            }

            sort($queue);
        }

        if (count($order) !== count($nodeIds)) {
            $blocked = array_values(array_diff($nodeIds, $order));

            throw CircularDependencyException::fromTasks(array_map(
                fn (int $id) => $byId[$id]->name,
                $blocked,
            ));
        }

        return $order;
    }

    /**
     * Calculate the earliest start and finish of every task.
     *
     * @param  array<int, Task>  $byId
     * @param  list<int>  $order
     * @param  array<int, list<array{pred: int, type: DependencyType, lag: int}>>  $incoming
     * @return array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function forwardPass(array $byId, array $order, array $incoming, CarbonImmutable $projectStart, WorkingCalendar $calendar): array
    {
        $dates = [];

        foreach ($order as $taskId) {
            $task = $byId[$taskId];
            $duration = $this->durationOf($task);

            if ($task->scheduling_mode === SchedulingMode::Manual && $task->start_at !== null && $task->finish_at !== null) {
                $dates[$taskId] = [
                    CarbonImmutable::instance($task->start_at),
                    CarbonImmutable::instance($task->finish_at),
                ];

                continue;
            }

            $start = $this->earliestStart($task, $duration, $incoming[$taskId] ?? [], $dates, $projectStart, $calendar);

            if ($task->level_delay_minutes > 0) {
                $start = $calendar->nextWorkingInstant($calendar->add($start, $task->level_delay_minutes));
            }

            $finish = $calendar->add($start, $duration);

            $dates[$taskId] = [$start, $finish];
        }

        return $dates;
    }

    /**
     * Resolve the earliest start of a task from dependencies and constraints.
     *
     * @param  list<array{pred: int, type: DependencyType, lag: int}>  $edges
     * @param  array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>  $dates
     */
    private function earliestStart(Task $task, int $duration, array $edges, array $dates, CarbonImmutable $projectStart, WorkingCalendar $calendar): CarbonImmutable
    {
        $start = $projectStart;

        if ($task->constraint_date !== null) {
            $constraintDate = CarbonImmutable::instance($task->constraint_date);

            $floored = match ($task->constraint_type) {
                TaskConstraint::MustStartOn => $calendar->nextWorkingInstant($constraintDate),
                TaskConstraint::StartNoEarlierThan => $constraintDate,
                TaskConstraint::FinishNoEarlierThan => $duration === 0
                    ? $constraintDate
                    : $calendar->subtract($constraintDate, $duration),
                default => null,
            };

            if ($floored !== null && $floored->greaterThan($start)) {
                $start = $floored;
            }
        }

        foreach ($edges as $edge) {
            [$predecessorStart, $predecessorFinish] = $dates[$edge['pred']];
            $candidate = $this->dependencyStart($edge['type'], $predecessorStart, $predecessorFinish, $edge['lag'], $duration, $calendar);

            if ($candidate->greaterThan($start)) {
                $start = $candidate;
            }
        }

        if ($task->constraint_type === TaskConstraint::MustStartOn && $task->constraint_date !== null) {
            $start = $calendar->nextWorkingInstant(CarbonImmutable::instance($task->constraint_date));
        }

        if ($task->constraint_type === TaskConstraint::StartNoLaterThan && $task->constraint_date !== null) {
            $limit = CarbonImmutable::instance($task->constraint_date);

            if ($start->greaterThan($limit)) {
                $start = $limit;
            }
        }

        return $calendar->nextWorkingInstant($start);
    }

    /**
     * The start a single dependency demands of its successor.
     */
    private function dependencyStart(DependencyType $type, CarbonImmutable $predecessorStart, CarbonImmutable $predecessorFinish, int $lag, int $duration, WorkingCalendar $calendar): CarbonImmutable
    {
        $laggedFinish = $calendar->add($predecessorFinish, $lag);
        $laggedStart = $calendar->add($predecessorStart, $lag);

        return match ($type) {
            DependencyType::FinishToStart => $laggedFinish,
            DependencyType::StartToStart => $laggedStart,
            DependencyType::FinishToFinish => $duration === 0
                ? $laggedFinish
                : $calendar->subtract($laggedFinish, $duration),
            DependencyType::StartToFinish => $duration === 0
                ? $laggedStart
                : $calendar->subtract($laggedStart, $duration),
        };
    }

    /**
     * Calculate the latest start and finish of every task.
     *
     * @param  array<int, Task>  $byId
     * @param  list<int>  $order
     * @param  array<int, list<array{succ: int, type: DependencyType, lag: int}>>  $outgoing
     * @param  array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>  $earliest
     * @return array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function backwardPass(array $byId, array $order, array $outgoing, array $earliest, CarbonImmutable $projectFinish, WorkingCalendar $calendar): array
    {
        $dates = [];

        foreach (array_reverse($order) as $taskId) {
            $task = $byId[$taskId];
            $duration = $this->durationOf($task);
            $finish = $projectFinish;

            $edges = $outgoing[$taskId] ?? [];

            if ($edges !== []) {
                $finish = null;

                foreach ($edges as $edge) {
                    [$successorStart, $successorFinish] = $dates[$edge['succ']];
                    $candidate = $this->dependencyFinish($edge['type'], $successorStart, $successorFinish, $edge['lag'], $duration, $calendar);

                    if ($finish === null || $candidate->lessThan($finish)) {
                        $finish = $candidate;
                    }
                }
            }

            if ($task->constraint_date !== null) {
                $constraintDate = CarbonImmutable::instance($task->constraint_date);
                $limited = $duration === 0
                    ? $constraintDate
                    : $calendar->add($constraintDate, $duration);

                $finish = match ($task->constraint_type) {
                    TaskConstraint::MustFinishOn => $calendar->nextWorkingInstant($constraintDate),
                    TaskConstraint::FinishNoLaterThan => $constraintDate->lessThan($finish) ? $constraintDate : $finish,
                    TaskConstraint::StartNoLaterThan => $limited->lessThan($finish) ? $limited : $finish,
                    TaskConstraint::AsLateAsPossible => $projectFinish->lessThan($finish) ? $projectFinish : $finish,
                    default => $finish,
                };
            }

            if ($task->scheduling_mode === SchedulingMode::Manual && $task->start_at !== null && $task->finish_at !== null) {
                $finish = CarbonImmutable::instance($task->finish_at);
            }

            $start = $duration === 0 ? $finish : $calendar->subtract($finish, $duration);

            $dates[$taskId] = [$start, $finish];
        }

        return $dates;
    }

    /**
     * The latest finish a single dependency demands of its predecessor.
     */
    private function dependencyFinish(DependencyType $type, CarbonImmutable $successorStart, CarbonImmutable $successorFinish, int $lag, int $duration, WorkingCalendar $calendar): CarbonImmutable
    {
        $laggedStart = $calendar->add($successorStart, $lag);
        $laggedFinish = $calendar->add($successorFinish, $lag);

        return match ($type) {
            DependencyType::FinishToStart => $laggedStart,
            DependencyType::StartToStart => $duration === 0
                ? $laggedStart
                : $calendar->add($laggedStart, $duration),
            DependencyType::FinishToFinish => $laggedFinish,
            DependencyType::StartToFinish => $duration === 0
                ? $laggedFinish
                : $calendar->add($laggedFinish, $duration),
        };
    }

    /**
     * The working minutes a task may slip before it delays a successor.
     *
     * @param  array<int, list<array{succ: int, type: DependencyType, lag: int}>>  $outgoing
     * @param  array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>  $earliest
     * @param  array{0: CarbonImmutable, 1: CarbonImmutable}  $latest
     */
    private function freeSlack(Task $task, array $edges, array $earliest, array $latest, WorkingCalendar $calendar): int
    {
        if ($edges === []) {
            return $this->slackInMinutes($calendar, $earliest[$task->getKey()][0], $latest[0]);
        }

        [$start, $finish] = $earliest[$task->getKey()];
        $slack = null;

        foreach ($edges as $edge) {
            [$successorStart, $successorFinish] = $earliest[$edge['succ']];

            $available = match ($edge['type']) {
                DependencyType::FinishToStart => $this->slackInMinutes($calendar, $calendar->add($finish, $edge['lag']), $successorStart),
                DependencyType::StartToStart => $this->slackInMinutes($calendar, $calendar->add($start, $edge['lag']), $successorStart),
                DependencyType::FinishToFinish => $this->slackInMinutes($calendar, $calendar->add($finish, $edge['lag']), $successorFinish),
                DependencyType::StartToFinish => $this->slackInMinutes($calendar, $calendar->add($start, $edge['lag']), $successorFinish),
            };

            if ($slack === null || $available < $slack) {
                $slack = $available;
            }
        }

        return $slack ?? 0;
    }

    /**
     * The working minutes between two instants, never negative.
     */
    private function slackInMinutes(WorkingCalendar $calendar, CarbonImmutable $from, CarbonImmutable $to): int
    {
        if ($to->lessThan($from)) {
            return 0;
        }

        return $calendar->minutesBetween($from, $to);
    }

    /**
     * Roll the calculated dates up to the summary tasks.
     *
     * @param  array<int, Task>  $byId
     * @param  array<int, list<int>>  $children
     * @param  array<int, TaskSchedule>  $schedules
     * @return array<int, TaskSchedule>
     */
    private function rollupSummaries(array $byId, array $children, array $schedules): array
    {
        $depths = [];

        foreach ($byId as $taskId => $task) {
            $depths[$taskId] = $this->depthOf($task, $byId);
        }

        $rollups = [];
        $pending = array_keys($children);

        usort($pending, fn (int $a, int $b) => $depths[$b] <=> $depths[$a]);

        foreach ($pending as $taskId) {
            $start = null;
            $finish = null;
            $slack = null;
            $critical = false;

            foreach ($children[$taskId] as $childId) {
                $child = $rollups[$childId] ?? $schedules[$childId] ?? null;

                if ($child === null) {
                    continue;
                }

                $start = $start === null || $child->start->lessThan($start) ? $child->start : $start;
                $finish = $finish === null || $child->finish->greaterThan($finish) ? $child->finish : $finish;
                $slack = $slack === null || $child->totalSlack < $slack ? $child->totalSlack : $slack;
                $critical = $critical || $child->critical;
            }

            if ($start === null || $finish === null) {
                continue;
            }

            $rollups[$taskId] = new TaskSchedule(
                start: $start,
                finish: $finish,
                totalSlack: $slack ?? 0,
                freeSlack: $slack ?? 0,
                critical: $critical,
            );
        }

        return $rollups;
    }

    /**
     * The distance of a task from the root of the outline.
     */
    private function depthOf(Task $task, array $byId): int
    {
        $depth = 0;
        $current = $task;

        while ($current->parent_id !== null && isset($byId[$current->parent_id]) && $depth < 100) {
            $depth++;
            $current = $byId[$current->parent_id];
        }

        return $depth;
    }

    /**
     * The duration of a task in working minutes.
     */
    private function durationOf(Task $task): int
    {
        if ($task->is_milestone) {
            return 0;
        }

        return $task->duration_minutes ?? self::DEFAULT_DURATION_MINUTES;
    }
}
