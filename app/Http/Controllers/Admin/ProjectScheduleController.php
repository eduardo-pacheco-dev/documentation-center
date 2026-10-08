<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\Scheduling\ResourceLeveler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
