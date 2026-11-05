<?php

namespace Plugins\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Plugins\Projects\Models\Project;
use Plugins\Projects\Services\Scheduling\ProjectScheduler;
use Plugins\Projects\Services\Scheduling\ResourceLeveler;

class ProjectScheduleController extends Controller
{
    public function __construct(
        private readonly ProjectScheduler $scheduler,
        private readonly ResourceLeveler $leveler,
    ) {}

    /**
     * Recalculate the critical path of the project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('plan', $project);

        $this->scheduler->schedule($project);

        return back()->with('status', 'Cronograma recalculado.');
    }

    /**
     * Remove the resource overallocations delaying the lowest priority tasks.
     */
    public function level(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('plan', $project);

        $outcome = $this->leveler->level($project);

        $message = $outcome['shifted'] === 0
            ? 'Nenhuma tarefa precisou ser atrasada.'
            : "{$outcome['shifted']} tarefa(s) atrasada(s) para remover a sobrecarga de recursos.";

        if ($outcome['remaining'] > 0) {
            $message .= " {$outcome['remaining']} conflito(s) ainda não resolvido(s).";
        }

        return back()->with('status', $message);
    }
}
