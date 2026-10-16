<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WorkOrderItemStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWorkOrderItemStatusRequest;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Http\RedirectResponse;

class WorkOrderItemController extends Controller
{
    /**
     * Update the status of a single work order item.
     */
    public function updateStatus(UpdateWorkOrderItemStatusRequest $request, WorkOrder $workOrder, WorkOrderItem $item): RedirectResponse
    {
        abort_unless($item->work_order_id === $workOrder->getKey(), 404);

        $item->update([
            'status' => WorkOrderItemStatus::from($request->validated('status')),
        ]);

        return redirect()
            ->route('admin.work-orders.show', ['workOrder' => $workOrder, 'item' => $item->getKey()])
            ->with('status', 'Status do item atualizado.');
    }
}
