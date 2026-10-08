<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskConstraint;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\Scheduling\WbsCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectGanttController extends Controller
{
    /**
     * Map the dependency types to the link arrows drawn by the chart.
     *
     * @var array<string, int>
     */
    private const LINK_TYPES = [
        'fs' => 0,
        'ss' => 1,
        'ff' => 2,
        'sf' => 3,
    ];

    public function __construct(
        private readonly ProjectScheduler $scheduler,
        private readonly WbsCalculator $wbs,
    ) {}

    /**
     * Feed the chart with the tasks and links of the project.
     */
    public function data(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $minutesPerDay = max(1, $project->defaultCalendar()->minutes_per_day);
        $tasks = $project->tasks()->with('resources:id,name')->orderBy('wbs')->orderBy('sort_order')->orderBy('id')->get();

        $hasChildren = $tasks->pluck('parent_id')->filter()->flip();

        return response()->json([
            'data' => $tasks->map(fn (Task $task) => [
                'id' => $task->getKey(),
                'text' => $task->name,
                'wbs' => $task->wbs,
                'start_date' => $task->start_at?->format('Y-m-d H:i'),
                'duration' => $this->chartDuration($task, $minutesPerDay, $hasChildren),
                'progress' => ((float) $task->percent_complete) / 100,
                'parent' => $task->parent_id ?? 0,
                'type' => $this->chartType($task, $hasChildren),
                'open' => true,
                'critical' => (bool) $task->critical,
                'slack' => ((int) $task->total_slack_minutes) / $minutesPerDay,
                'resource' => $task->resources->pluck('name')->implode(', '),
            ])->values(),
            'links' => $project->dependencies()->get()->map(fn ($dependency) => [
                'id' => $dependency->getKey(),
                'source' => $dependency->predecessor_id,
                'target' => $dependency->successor_id,
                'type' => self::LINK_TYPES[$dependency->type->value] ?? 0,
                'lag' => ((int) $dependency->lag_minutes) / $minutesPerDay,
            ])->values(),
        ]);
    }

    /**
     * Apply the dates changed by dragging a bar on the chart.
     */
    public function move(Request $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('plan', $project);

        abort_unless($task->project_id === $project->getKey(), 404);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'duration_days' => ['required', 'numeric', 'min:0', 'max:3650'],
        ]);

        $minutesPerDay = $project->defaultCalendar()->minutes_per_day;

        $task->update([
            'constraint_type' => TaskConstraint::StartNoEarlierThan,
            'constraint_date' => $validated['start_date'],
            'duration_minutes' => (int) round((float) $validated['duration_days'] * $minutesPerDay),
        ]);

        $this->wbs->recalculate($project);
        $this->scheduler->schedule($project);

        return response()->json(['status' => 'ok']);
    }

    /**
     * The duration the chart shows, in whole days.
     */
    private function chartDuration(Task $task, int $minutesPerDay, mixed $hasChildren): int
    {
        if ($task->is_milestone || $task->parent_id !== null && $hasChildren->has($task->getKey())) {
            return 0;
        }

        return max(1, (int) ceil(((int) $task->duration_minutes) / $minutesPerDay));
    }

    /**
     * The shape the chart draws for the task.
     */
    private function chartType(Task $task, mixed $hasChildren): string
    {
        if ($task->parent_id !== null && $hasChildren->has($task->getKey())) {
            return 'summary';
        }

        if ($task->is_milestone) {
            return 'milestone';
        }

        return 'task';
    }
}
