<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderItemDocumentRequest;
use App\Models\Document;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkOrderItemDocumentController extends Controller
{
    /**
     * Store the files attached to the given work order item.
     */
    public function store(StoreWorkOrderItemDocumentRequest $request, WorkOrder $workOrder, WorkOrderItem $item): RedirectResponse
    {
        $this->authorize('update', $workOrder);
        $this->ensureItemBelongsToWorkOrder($workOrder, $item);

        $count = 0;

        foreach ($request->file('documents') as $file) {
            $item->documents()->create([
                'user_id' => $request->user()->getKey(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $count++;
        }

        return redirect()
            ->route('admin.work-orders.show', ['workOrder' => $workOrder, 'item' => $item->getKey()])
            ->with('status', $count.' anexo(s) enviado(s) com sucesso.');
    }

    /**
     * Remove an attached file, sending it to the trash.
     */
    public function destroy(Request $request, WorkOrder $workOrder, WorkOrderItem $item, Document $document): RedirectResponse
    {
        $this->authorize('update', $workOrder);
        $this->ensureItemBelongsToWorkOrder($workOrder, $item);

        abort_unless($document->work_order_item_id === $item->getKey(), 404);

        $this->authorize('delete', $document);

        $document->delete();

        return redirect()
            ->route('admin.work-orders.show', ['workOrder' => $workOrder, 'item' => $item->getKey()])
            ->with('status', 'Anexo removido.');
    }

    /**
     * Ensure the given item belongs to the work order.
     */
    protected function ensureItemBelongsToWorkOrder(WorkOrder $workOrder, WorkOrderItem $item): void
    {
        abort_unless($item->work_order_id === $workOrder->getKey(), 404);
    }
}
