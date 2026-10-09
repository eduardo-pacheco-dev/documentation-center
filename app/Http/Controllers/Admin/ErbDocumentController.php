<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreErbDocumentRequest;
use App\Models\Document;
use App\Models\Erb;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ErbDocumentController extends Controller
{
    /**
     * Store the files attached to the given ERB.
     */
    public function store(StoreErbDocumentRequest $request, Erb $erb): RedirectResponse
    {
        $count = 0;

        foreach ($request->file('documents') as $file) {
            $erb->documents()->create([
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
    public function destroy(Request $request, Erb $erb, Document $document): RedirectResponse
    {
        $this->authorize('view', $erb);

        abort_unless($document->erb_id === $erb->getKey(), 404);

        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Anexo removido.');
    }
}
