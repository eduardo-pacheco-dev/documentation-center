<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SchedulingMode;
use App\Enums\TaskConstraint;
use App\Enums\TaskType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\Scheduling\WbsCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function __construct(private readonly ProjectScheduler $scheduler, private readonly WbsCalculator $wbs) {}

    /**
     * Create a task inside the project plan.
     */
    public function store(TaskRequest $request, Project $project): RedirectResponse
    {
        $project->tasks()->create($this->taskAttributes($request) + [
            'sort_order' => $this->nextSortOrder($project, $request->validated('parent_id')),
            'wbs' => '0',
            'outline_level' => 1,
        ]);

        $this->wbs->recalculate($project);
        $this->scheduler->schedule($project);

        return back()->with('status', 'Tarefa criada com sucesso.');
    }

    /**
     * Update a task of the project plan.
     */
    public function update(TaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $task->update($this->taskAttributes($request, $task));

        $this->wbs->recalculate($project);
        $this->scheduler->schedule($project);

        return back()->with('status', 'Tarefa atualizada com sucesso.');
    }

    /**
     * Delete a task and the tasks nested inside it.
     */
    public function destroy(Request $request, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('plan', $project);

        $this->deleteTree($task);

        $this->wbs->recalculate($project);
        $this->scheduler->schedule($project);

        return back()->with('status', 'Tarefa excluída com sucesso.');
    }

    /**
     * Record how far a task has gone and reschedule the plan around it.
     */
    public function progress(Request $request, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('plan', $project);

        $validated = $request->validate([
            'percent_complete' => ['required', 'numeric', 'min:0'],
            'actual_start_at' => ['nullable', 'date'],
            'actual_finish_at' => ['nullable', 'date', 'after_or_equal:actual_start_at'],
        ]);

        $this->scheduler->applyProgress(
            $project,
            $task,
            (float) $validated['percent_complete'],
            isset($validated['actual_start_at']) ? CarbonImmutable::parse($validated['actual_start_at']) : null,
            isset($validated['actual_finish_at']) ? CarbonImmutable::parse($validated['actual_finish_at']) : null,
        );

        return back()->with('status', 'Progresso da tarefa registrado.');
    }

    /**
     * The validated task fields, with the start date expressed as a constraint.
     */
    private function taskAttributes(TaskRequest $request, ?Task $task = null): array
    {
        $validated = $request->validated();

        $attributes = [
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? $task?->parent_id,
            'task_type' => $validated['task_type'] ?? $task?->task_type?->value ?? TaskType::FixedDuration->value,
            'scheduling_mode' => $validated['scheduling_mode'] ?? $task?->scheduling_mode?->value ?? SchedulingMode::Auto->value,
            'is_milestone' => $validated['is_milestone'] ?? $task?->is_milestone ?? false,
            'constraint_type' => $validated['constraint_type'] ?? $task?->constraint_type?->value ?? TaskConstraint::AsSoonAsPossible->value,
            'constraint_date' => $validated['constraint_date'] ?? $task?->constraint_date,
            'priority' => $validated['priority'] ?? $task?->priority ?? 500,
            'percent_complete' => $validated['percent_complete'] ?? $task?->percent_complete ?? 0,
            'budget_cost' => $validated['budget_cost'] ?? $task?->budget_cost ?? 0,
            'notes' => $validated['notes'] ?? $task?->notes,
        ];

        if (array_key_exists('duration_days', $validated)) {
            $attributes['duration_minutes'] = $request->durationMinutes();
        }

        if (array_key_exists('start_date', $validated)) {
            $attributes['constraint_type'] = $validated['start_date'] === null
                ? TaskConstraint::AsSoonAsPossible->value
                : TaskConstraint::StartNoEarlierThan->value;
            $attributes['constraint_date'] = $validated['start_date'];
        }

        return $attributes;
    }

    /**
     * Delete a task together with everything nested inside it.
     */
    private function deleteTree(Task $task): void
    {
        $task->children()->get()->each(fn (Task $child) => $this->deleteTree($child));

        $task->delete();
    }

    /**
     * The position a new task takes among its siblings.
     */
    private function nextSortOrder(Project $project, ?int $parentId): int
    {
        return (int) $project->tasks()->where('parent_id', $parentId)->max('sort_order') + 1;
    }
}
