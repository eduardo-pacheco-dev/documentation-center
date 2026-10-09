<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientDocumentRequest;
use App\Models\Client;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientDocumentController extends Controller
{
    /**
     * Store the files attached to the given client.
     */
    public function store(StoreClientDocumentRequest $request, Client $client): RedirectResponse
    {
        $count = 0;

        foreach ($request->file('documents') as $file) {
            $client->documents()->create([
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
    public function destroy(Request $request, Client $client, Document $document): RedirectResponse
    {
        $this->authorize('view', $client);

        abort_unless($document->client_id === $client->getKey(), 404);

        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Anexo removido.');
    }
}
