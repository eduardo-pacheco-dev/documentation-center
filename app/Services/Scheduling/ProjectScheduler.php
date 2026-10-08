<?php

namespace App\Services\Scheduling;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Runs the critical path calculation of a project and writes the outcome back
 * to the plan: task dates, slack, critical flags, summary rollups and the
 * project level dates and progress.
 */
class ProjectScheduler
{
    public function __construct(private readonly CpmEngine $engine, private readonly WorkingCalendarFactory $calendars) {}

    /**
     * Schedule the project and persist the calculated dates.
     */
    public function schedule(Project $project): Project
    {
        $calendar = $this->calendars->for($project);
        $tasks = $project->tasks()->get();
        $dependencies = TaskDependency::query()->whereBelongsTo($project)->get();

        $result = $this->engine->run($project, $tasks, $dependencies, $calendar);

        $this->persist($project, $tasks, $result);

        return $project->refresh();
    }

    /**
     * Record progress on a task and reschedule the plan around it.
     */
    public function applyProgress(Project $project, Task $task, float $percentComplete, ?CarbonImmutable $actualStart, ?CarbonImmutable $actualFinish): Task
    {
        $percentComplete = max(0, min(100, $percentComplete));
        $finished = $percentComplete >= 100;

        $task->forceFill([
            'percent_complete' => $percentComplete,
            'actual_start_at' => $actualStart ?? $task->actual_start_at ?? ($percentComplete > 0 ? now() : null),
            'actual_finish_at' => $finished ? ($actualFinish ?? $task->finish_at ?? now()) : null,
        ])->save();

        if ($finished && $task->actual_duration_minutes === null) {
            $task->forceFill([
                'actual_duration_minutes' => $this->elapsedMinutes($project, $task),
            ])->save();
        }

        $this->schedule($project);

        return $task->refresh();
    }

    /**
     * Write the calculated dates, rollups and progress to the database.
     *
     * @param  Collection<int, Task>  $tasks
     */
    private function persist(Project $project, Collection $tasks, ScheduleResult $result): void
    {
        $tasksByParent = $tasks->groupBy('parent_id');
        $depths = $this->depths($tasks);
        $ordered = $tasks->sortBy(fn (Task $task) => $depths[$task->getKey()] ?? 0, SORT_NUMERIC, true)->values();

        foreach ($ordered as $task) {
            $schedule = $result->has($task->getKey())
                ? $result->for($task->getKey())
                : ($result->summaryRollups()[$task->getKey()] ?? null);

            if ($schedule === null) {
                continue;
            }

            $attributes = [
                'start_at' => $schedule->start,
                'finish_at' => $schedule->finish,
                'total_slack_minutes' => $schedule->totalSlack,
                'free_slack_minutes' => $schedule->freeSlack,
                'critical' => $schedule->critical,
            ];

            if (isset($tasksByParent[$task->getKey()])) {
                $attributes += $this->rollup($task, $tasksByParent);
            }

            $task->forceFill($attributes)->save();
        }

        $project->forceFill([
            'start_date' => $result->start()->toDateString(),
            'finish_date' => $result->finish()->toDateString(),
            'percent_complete' => $this->projectPercent($tasks),
            'scheduled_at' => now(),
        ])->save();
    }

    /**
     * Roll the child values up into a summary task.
     *
     * @param  Collection<int, Task>  $tasksByParent
     * @return array<string, mixed>
     */
    private function rollup(Task $task, Collection $tasksByParent): array
    {
        $children = $tasksByParent->get($task->getKey());

        if ($children === null || $children->isEmpty()) {
            return [];
        }

        $weightedPercent = 0;
        $totalDuration = 0;

        foreach ($children as $child) {
            $duration = (int) ($child->duration_minutes ?? 0);
            $weightedPercent += ((float) $child->percent_complete) * $duration;
            $totalDuration += $duration;
        }

        return [
            'percent_complete' => $totalDuration > 0
                ? round($weightedPercent / $totalDuration, 2)
                : round($children->avg('percent_complete'), 2),
            'budget_cost' => $children->sum('budget_cost'),
            'actual_cost' => $children->sum('actual_cost'),
            'work_minutes' => $children->sum('work_minutes'),
        ];
    }

    /**
     * The progress of the project weighted by the duration of each leaf task.
     *
     * @param  Collection<int, Task>  $tasks
     */
    private function projectPercent(Collection $tasks): float
    {
        $leaves = $tasks->reject(fn (Task $task) => $tasks->contains(fn (Task $other) => $other->parent_id === $task->getKey()));

        if ($leaves->isEmpty()) {
            return 0;
        }

        $weighted = 0;
        $totalDuration = 0;

        foreach ($leaves as $leaf) {
            $duration = (int) ($leaf->duration_minutes ?? CpmEngine::DEFAULT_DURATION_MINUTES);
            $weighted += ((float) $leaf->percent_complete) * $duration;
            $totalDuration += $duration;
        }

        return $totalDuration > 0 ? round($weighted / $totalDuration, 2) : 0.0;
    }

    /**
     * The outline depth of every task, keyed by task id.
     *
     * @param  Collection<int, Task>  $tasks
     * @return array<int, int>
     */
    private function depths(Collection $tasks): array
    {
        $parents = $tasks->keyBy('id');
        $depths = [];

        foreach ($tasks as $task) {
            $depth = 0;
            $cursor = $task;

            while ($cursor->parent_id !== null && isset($parents[$cursor->parent_id]) && $depth < 100) {
                $depth++;
                $cursor = $parents[$cursor->parent_id];
            }

            $depths[$task->getKey()] = $depth;
        }

        return $depths;
    }

    /**
     * The working minutes between the actual start and the finish of a task.
     */
    private function elapsedMinutes(Project $project, Task $task): ?int
    {
        $end = $task->actual_finish_at ?? $task->finish_at;

        if ($task->actual_start_at === null || $end === null) {
            return null;
        }

        return max(0, $this->calendars->for($project)->minutesBetween($task->actual_start_at, $end));
    }
}
