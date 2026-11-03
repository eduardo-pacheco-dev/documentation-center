<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\Stations\Http\Requests\StoreStationAttachmentRequest;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationAttachment;
use Plugins\Stations\Support\StationStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StationAttachmentController extends Controller
{
    public function __construct(private readonly StationStorage $storage) {}

    /**
     * Store one or more files attached to the station.
     */
    public function store(StoreStationAttachmentRequest $request, Station $station): RedirectResponse
    {
        $category = $request->string('category')->toString();
        $diskName = $this->storage->name();
        $disk = $this->storage->disk($diskName);

        foreach ($request->file('files', []) as $file) {
            $path = $disk->putFile("stations/{$station->getKey()}/attachments", $file);

            if ($path === false) {
                return back()->withErrors(['files' => 'Não foi possível armazenar o arquivo enviado.']);
            }

            $station->attachments()->create([
                'uploaded_by' => $request->user()->getKey(),
                'category' => $category,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => $diskName,
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

        $disk = $this->storage->disk($attachment->disk ?: StationStorage::DISK_LOCAL);

        abort_unless($disk->exists($attachment->path), 404);

        return $disk->download($attachment->path, $attachment->original_name);
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
