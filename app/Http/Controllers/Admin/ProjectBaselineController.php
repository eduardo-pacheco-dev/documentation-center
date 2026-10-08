<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Baseline;
use App\Models\Project;
use App\Services\Baseline\BaselineService;
use App\Services\Scheduling\ProjectScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectBaselineController extends Controller
{
    public function __construct(
        private readonly BaselineService $baselines,
        private readonly ProjectScheduler $scheduler,
    ) {}

    /**
     * Freeze the current plan as a new baseline.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('plan', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->scheduler->schedule($project);
        $this->baselines->save($project, $validated['name'], $request->user());

        return back()->with('status', 'Linha de base salva com sucesso.');
    }

    /**
     * Write a baseline back into the live plan.
     */
    public function restore(Request $request, Project $project, Baseline $baseline): RedirectResponse
    {
        $this->authorize('plan', $project);

        abort_unless($baseline->project_id === $project->getKey(), 404);

        $restored = $this->baselines->restore($project, $baseline);
        $this->scheduler->schedule($project);

        return back()->with('status', "Linha de base restaurada em {$restored} tarefa(s).");
    }
}
