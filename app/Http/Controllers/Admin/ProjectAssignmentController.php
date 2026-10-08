<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignmentRequest;
use App\Models\Project;
use App\Models\ResourceAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectAssignmentController extends Controller
{
    /**
     * Assign a resource to a task.
     */
    public function store(AssignmentRequest $request, Project $project): RedirectResponse
    {
        $task = $project->tasks()->findOrFail($request->validated('task_id'));
        $resource = $project->resources()->findOrFail($request->validated('resource_id'));

        $assignment = ResourceAssignment::query()->updateOrCreate(
            ['task_id' => $task->getKey(), 'resource_id' => $resource->getKey()],
            [
                'units' => $request->validated('units') ?? 100,
                'work_minutes' => $task->duration_minutes,
                'cost' => $request->validated('cost') ?? 0,
            ],
        );

        $this->syncCost($assignment);

        return back()->with('status', 'Recurso alocado na tarefa.');
    }

    /**
     * Remove a resource from a task.
     */
    public function destroy(Request $request, Project $project, ResourceAssignment $assignment): RedirectResponse
    {
        $this->authorize('plan', $project);

        abort_unless($assignment->task->project_id === $project->getKey(), 404);

        $assignment->delete();

        return back()->with('status', 'Alocação removida.');
    }

    /**
     * Charge the resource rate to the task based on the allocated time.
     */
    private function syncCost(ResourceAssignment $assignment): void
    {
        $resource = $assignment->resource;
        $hours = ((int) $assignment->work_minutes) / 60;
        $cost = $hours * (float) $resource->cost_per_hour;

        if ($cost !== (float) $assignment->cost) {
            $assignment->update(['cost' => $cost]);
        }
    }
}
