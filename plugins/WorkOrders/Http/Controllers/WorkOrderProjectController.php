<?php

namespace Plugins\WorkOrders\Http\Controllers;

use Plugins\Projects\Enums\CalendarType;
use Plugins\Projects\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use Plugins\Projects\Models\Calendar;
use Plugins\Projects\Models\Project;
use Plugins\WorkOrders\Models\WorkOrder;
use Plugins\Projects\Services\Scheduling\WbsCalculator;
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
            'name' => $workOrder->number,
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
