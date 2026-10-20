<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEquipmentDocumentRequest;
use App\Models\Document;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EquipmentDocumentController extends Controller
{
    /**
     * Store the datasheets attached to the given equipment.
     */
    public function store(StoreEquipmentDocumentRequest $request, Equipment $equipment): RedirectResponse
    {
        $count = 0;

        foreach ($request->file('documents') as $file) {
            $equipment->documents()->create([
                'user_id' => $request->user()->getKey(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $count++;
        }

        return back()->with('status', $count.' datasheet(s) anexado(s) com sucesso.');
    }

    /**
     * Remove an attached datasheet, sending it to the trash.
     */
    public function destroy(Request $request, Equipment $equipment, Document $document): RedirectResponse
    {
        $this->authorize('view', $equipment);

        abort_unless($document->equipment_id === $equipment->getKey(), 404);

        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Datasheet removido.');
    }
}
