<?php

namespace App\Services\Baseline;

use App\Models\Baseline;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Saves, restores and compares the baseline of a project.
 */
class BaselineService
{
    /**
     * Freeze the current plan as a new baseline.
     */
    public function save(Project $project, string $name, ?User $user = null): Baseline
    {
        $baseline = $project->baselines()->create([
            'name' => $name,
            'created_by' => $user?->getKey(),
            'saved_at' => now(),
        ]);

        foreach ($project->tasks()->get() as $task) {
            $baseline->tasks()->create([
                'task_id' => $task->getKey(),
                'start_at' => $task->start_at,
                'finish_at' => $task->finish_at,
                'duration_minutes' => $task->duration_minutes,
                'work_minutes' => $task->work_minutes,
                'budget_cost' => $task->budget_cost,
                'percent_complete' => $task->percent_complete,
            ]);
        }

        return $baseline;
    }

    /**
     * Write a baseline back into the live plan.
     *
     * @return int the number of tasks restored
     */
    public function restore(Project $project, Baseline $baseline): int
    {
        $restored = 0;

        $baseline->tasks()->with('task')->get()->each(function ($baselineTask) use (&$restored): void {
            $task = $baselineTask->task;

            if ($task === null) {
                return;
            }

            $task->forceFill([
                'start_at' => $baselineTask->start_at,
                'finish_at' => $baselineTask->finish_at,
                'duration_minutes' => $baselineTask->duration_minutes,
                'work_minutes' => $baselineTask->work_minutes,
                'budget_cost' => $baselineTask->budget_cost,
                'level_delay_minutes' => 0,
            ])->save();

            $restored++;
        });

        return $restored;
    }

    /**
     * Compare the plan against a baseline, task by task.
     *
     * @return list<array{task: Task, baseline: ?object, variance_days: float}>
     */
    public function variances(Project $project, Baseline $baseline): array
    {
        $snapshots = $baseline->tasks()->get()->keyBy('task_id');
        $rows = [];

        foreach ($project->tasks()->orderBy('wbs')->get() as $task) {
            $snapshot = $snapshots->get($task->getKey());
            $variance = 0.0;

            if ($snapshot?->finish_at !== null && $task->finish_at !== null) {
                $variance = ($task->finish_at->getTimestamp() - $snapshot->finish_at->getTimestamp()) / 86400;
            }

            $rows[] = [
                'task' => $task,
                'baseline' => $snapshot,
                'variance_days' => round($variance, 2),
            ];
        }

        return $rows;
    }

    /**
     * The planned value of a task at a status date, from a baseline.
     */
    public static function plannedValueAt(?CarbonImmutable $start, ?CarbonImmutable $finish, float $budget, CarbonImmutable $statusDate): float
    {
        if ($start === null || $finish === null || $budget <= 0) {
            return 0.0;
        }

        if ($finish->lte($statusDate)) {
            return $budget;
        }

        if ($start->greaterThan($statusDate)) {
            return 0.0;
        }

        $total = $finish->getTimestamp() - $start->getTimestamp();

        if ($total <= 0) {
            return $budget;
        }

        $elapsed = $statusDate->getTimestamp() - $start->getTimestamp();

        return round($budget * min(1, max(0, $elapsed / $total)), 2);
    }
}
