<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Plugins\Stations\Http\Requests\StoreStationAttachmentRequest;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationAttachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StationAttachmentController extends Controller
{
    /**
     * Store one or more files attached to the station.
     */
    public function store(StoreStationAttachmentRequest $request, Station $station): RedirectResponse
    {
        $category = $request->string('category')->toString();

        foreach ($request->file('files', []) as $file) {
            $path = $file->store("stations/{$station->getKey()}/attachments", 'local');

            $station->attachments()->create([
                'uploaded_by' => $request->user()->getKey(),
                'category' => $category,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return back()->with('status', $category === StationAttachment::CATEGORY_PPI_TSSR
            ? 'Arquivos de PPI/TSSR anexados com sucesso.'
            : 'Anexos enviados com sucesso.');
    }

    /**
     * Download the given attachment.
     */
    public function download(Station $station, StationAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->station_id === $station->getKey(), 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    /**
     * Remove the given attachment.
     */
    public function destroy(Station $station, StationAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->station_id === $station->getKey(), 404);

        $attachment->delete();

        return back()->with('status', 'Anexo removido com sucesso.');
    }
}
