<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteAttachmentRequest;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NokiaSiteAttachmentController extends Controller
{
    /**
     * Store the uploaded files for the given site.
     */
    public function store(StoreNokiaSiteAttachmentRequest $request, NokiaSite $site): RedirectResponse
    {
        $storage = plugin_storage('nokia');
        $category = $request->string('category')->toString();
        $files = $request->file('files', []);

        foreach ($files as $file) {
            $path = $storage->store($file, 'sites/'.$site->getKey().'/'.$category);

            $site->attachments()->create([
                'category' => $category,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize() ?? 0,
                'mime_type' => $file->getMimeType(),
            ]);
        }

        $count = count($files);

        return back()->with('status', $count === 1
            ? 'Anexo enviado com sucesso.'
            : "{$count} anexos enviados com sucesso.");
    }

    /**
     * Download the given attachment.
     */
    public function download(NokiaSite $site, NokiaSiteAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->nokia_site_id === $site->getKey(), 404);

        $storage = plugin_storage('nokia');

        abort_unless($storage->exists($attachment->path), 404);

        return $storage->download($attachment->path, $attachment->original_name);
    }

    /**
     * Remove the given attachment and its file.
     */
    public function destroy(NokiaSite $site, NokiaSiteAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->nokia_site_id === $site->getKey(), 404);

        $attachment->delete();

        return back()->with('status', 'Anexo removido com sucesso.');
    }
}
