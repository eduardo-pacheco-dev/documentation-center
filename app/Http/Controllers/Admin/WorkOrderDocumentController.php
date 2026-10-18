<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderDocumentRequest;
use App\Models\Document;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkOrderDocumentController extends Controller
{
    /**
     * Store the files attached to the work order or to one of its items.
     */
    public function store(StoreWorkOrderDocumentRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('update', $workOrder);

        $item = $this->resolveItem($workOrder, $request->validated('item_id'));

        $count = 0;

        foreach ($request->file('documents') as $file) {
            $document = new Document([
                'user_id' => $request->user()->getKey(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            if ($item !== null) {
                $item->documents()->save($document);
            } else {
                $workOrder->documents()->save($document);
            }

            $count++;
        }

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', $count.' anexo(s) enviado(s) com sucesso.');
    }

    /**
     * Remove a file attached directly to the work order, sending it to the trash.
     */
    public function destroy(Request $request, WorkOrder $workOrder, Document $document): RedirectResponse
    {
        $this->authorize('update', $workOrder);

        abort_unless($document->work_order_id === $workOrder->getKey() && $document->work_order_item_id === null, 404);

        $this->authorize('delete', $document);

        $document->delete();

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', 'Anexo removido.');
    }

    /**
     * Resolve the item the files must be attached to, when one was given.
     */
    protected function resolveItem(WorkOrder $workOrder, ?int $itemId): ?WorkOrderItem
    {
        if ($itemId === null) {
            return null;
        }

        return $workOrder->items()->findOrFail($itemId);
    }
}
