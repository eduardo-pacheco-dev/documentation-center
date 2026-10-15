<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRadioLinkDocumentRequest;
use App\Models\Document;
use App\Models\RadioLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RadioLinkDocumentController extends Controller
{
    /**
     * Store the files attached to the given radio link.
     */
    public function store(StoreRadioLinkDocumentRequest $request, RadioLink $radioLink): RedirectResponse
    {
        $count = 0;

        foreach ($request->file('documents') as $file) {
            $radioLink->documents()->create([
                'user_id' => $request->user()->getKey(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $count++;
        }

        return back()->with('status', $count.' anexo(s) enviado(s) com sucesso.');
    }

    /**
     * Remove an attached file, sending it to the trash.
     */
    public function destroy(Request $request, RadioLink $radioLink, Document $document): RedirectResponse
    {
        $this->authorize('view', $radioLink);

        abort_unless($document->radio_link_id === $radioLink->getKey(), 404);

        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Anexo removido.');
    }
}
