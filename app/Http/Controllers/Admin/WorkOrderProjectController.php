<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CalendarType;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\Project;
use App\Models\WorkOrder;
use App\Services\Scheduling\WbsCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkOrderProjectController extends Controller
{
    /**
     * Create a project from the work order and open it.
     */
    public function store(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('view', $workOrder);
        $this->authorize('create', Project::class);

        $existing = $workOrder->project;

        if ($existing !== null) {
            return redirect()
                ->route('admin.projects.show', $existing)
                ->with('status', 'Esta ordem de serviço já possui um projeto.');
        }

        $project = $request->user()->projects()->create([
            'work_order_id' => $workOrder->getKey(),
            'name' => $workOrder->title,
            'status' => ProjectStatus::Active,
            'start_date' => $workOrder->opened_at,
            'finish_date' => $workOrder->due_at,
            'budget' => $workOrder->total,
        ]);

        Calendar::createDefault($project, CalendarType::Standard, 'Calendário padrão');

        (new WbsCalculator)->recalculate($project);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', "Projeto criado a partir da {$workOrder->number}.");
    }
}
