<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Plugins\Stations\Http\Requests\StoreStationDocumentRequest;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationDocument;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StationDocumentController extends Controller
{
    /**
     * Upload attachments to the given station.
     */
    public function store(StoreStationDocumentRequest $request, Station $station): RedirectResponse
    {
        $disk = (string) config('stations.documents.disk', 'local');
        $directory = (string) config('stations.documents.path', 'stations/documents');

        foreach ((array) $request->file('documents') as $file) {
            $station->documents()->create([
                'user_id' => $request->user()->getKey(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store($directory, $disk),
                'disk' => $disk,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return back()->with('status', 'Documentos anexados com sucesso.');
    }

    /**
     * Download an attachment.
     */
    public function download(Request $request, Station $station, StationDocument $document): StreamedResponse
    {
        $this->authorize('view', $station);

        abort_unless($document->station_id === $station->getKey(), 404);
        abort_unless($document->user_id === $request->user()->getKey(), 403);

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    /**
     * Remove an attachment.
     */
    public function destroy(Request $request, Station $station, StationDocument $document): RedirectResponse
    {
        $this->authorize('update', $station);

        abort_unless($document->station_id === $station->getKey(), 404);
        abort_unless($document->user_id === $request->user()->getKey(), 403);

        Storage::disk($document->disk)->delete($document->path);

        $document->delete();

        return back()->with('status', 'Documento removido.');
    }
}
