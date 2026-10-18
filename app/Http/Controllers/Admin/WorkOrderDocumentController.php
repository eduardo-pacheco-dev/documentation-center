<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderDocumentRequest;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;

class WorkOrderDocumentController extends Controller
{
    /**
     * Store the files attached to the given work order item.
     */
    public function store(StoreWorkOrderDocumentRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('update', $workOrder);

        $item = $workOrder->items()->findOrFail($request->validated('item_id'));

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
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', $count.' anexo(s) enviado(s) com sucesso.');
    }
}
