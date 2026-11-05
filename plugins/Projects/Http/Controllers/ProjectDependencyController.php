<?php

namespace Plugins\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Plugins\Projects\Enums\DependencyType;
use Plugins\Projects\Http\Requests\DependencyRequest;
use Plugins\Projects\Models\Project;
use Plugins\Projects\Models\TaskDependency;
use Plugins\Projects\Services\Scheduling\ProjectScheduler;

class ProjectDependencyController extends Controller
{
    public function __construct(private readonly ProjectScheduler $scheduler) {}

    /**
     * Link two tasks of the project plan.
     */
    public function store(DependencyRequest $request, Project $project): RedirectResponse
    {
        $project->dependencies()->create([
            ...$request->validated(),
            'type' => $request->validated('type') ?? DependencyType::FinishToStart,
            'lag_minutes' => $request->lagMinutes(),
        ]);

        $this->scheduler->schedule($project);

        return back()->with('status', 'Dependência criada com sucesso.');
    }

    /**
     * Remove a dependency between two tasks.
     */
    public function destroy(Request $request, Project $project, TaskDependency $dependency): RedirectResponse
    {
        $this->authorize('plan', $project);

        abort_unless($dependency->project_id === $project->getKey(), 404);

        $dependency->delete();

        $this->scheduler->schedule($project);

        return back()->with('status', 'Dependência removida.');
    }
}
