<?php

namespace App\Http\Controllers\Public;

use App\Enums\ShortLinkType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentsRequest;
use App\Http\Requests\UnlockLinkRequest;
use App\Models\Document;
use App\Models\ShortLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LinkController extends Controller
{
    /**
     * Open the public page for a short link.
     */
    public function show(string $code): View|Response
    {
        $shortLink = ShortLink::query()->where('code', $code)->firstOrFail();

        if (! $shortLink->isUsable()) {
            return response()->view('public.links.unavailable', [
                'reason' => $this->unavailableReason($shortLink),
            ], 410);
        }

        if ($shortLink->needsPassword() && ! $this->isUnlocked($shortLink)) {
            return view('public.links.password', ['shortLink' => $shortLink]);
        }

        $shortLink->recordAccess();

        $documents = $this->documentsFor($shortLink);

        return $shortLink->type === ShortLinkType::Upload
            ? view('public.links.upload', ['shortLink' => $shortLink, 'documents' => $documents])
            : view('public.links.download', ['shortLink' => $shortLink, 'documents' => $documents]);
    }

    /**
     * Unlock a password-protected link for the current session.
     */
    public function unlock(UnlockLinkRequest $request, string $code): RedirectResponse
    {
        $shortLink = ShortLink::query()->where('code', $code)->firstOrFail();

        if (! Hash::check($request->string('password'), (string) $shortLink->password)) {
            return back()->withErrors(['password' => 'A senha informada está incorreta.']);
        }

        session([$this->unlockSessionKey($shortLink) => true]);

        return redirect()->route('public.short-links.show', $shortLink->code);
    }

    /**
     * Receive documents uploaded through an upload-type link.
     */
    public function storeDocuments(StoreDocumentsRequest $request, string $code): RedirectResponse
    {
        $shortLink = ShortLink::query()->where('code', $code)->firstOrFail();

        abort_unless(
            $shortLink->isUsable() && $shortLink->type === ShortLinkType::Upload,
            404,
        );

        abort_unless(
            ! $shortLink->needsPassword() || $this->isUnlocked($shortLink),
            403,
        );

        foreach ($request->file('documents') as $file) {
            $shortLink->user->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_via_short_link_id' => $shortLink->getKey(),
            ]);
        }

        return back()->with('status', 'Documentos enviados com sucesso.');
    }

    /**
     * Download a document shared through an accessible link.
     */
    public function download(Document $document): StreamedResponse
    {
        abort_unless($this->isDocumentShareable($document), 404);

        $disk = Storage::disk($document->disk);

        abort_unless($disk->exists($document->path), 404);

        return $disk->download($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type ?? 'application/octet-stream',
        ]);
    }

    /**
     * The human-readable reason a link is unavailable.
     */
    private function unavailableReason(ShortLink $shortLink): string
    {
        if (! $shortLink->is_active) {
            return 'Este link foi desativado pelo responsável.';
        }

        if ($shortLink->isExpired()) {
            return 'Este link expirou.';
        }

        return 'Este link atingiu o limite de acessos permitidos.';
    }

    /**
     * The documents shown on the public page for the given link.
     */
    private function documentsFor(ShortLink $shortLink): Collection
    {
        $documents = $shortLink->type === ShortLinkType::Upload
            ? $shortLink->receivedDocuments()
            : $shortLink->documents();

        return $documents->latest('documents.created_at')->get(['documents.id', 'documents.original_name', 'documents.size', 'documents.created_at']);
    }

    /**
     * Whether the document can currently be downloaded through an accessible link.
     */
    private function isDocumentShareable(Document $document): bool
    {
        $shareable = $document->shortLinks->contains(function (ShortLink $link) {
            return $link->type === ShortLinkType::Download
                && $link->isUsable()
                && (! $link->needsPassword() || $this->isUnlocked($link));
        });

        $receivedVia = $document->uploadedVia;

        return $shareable || (
            $receivedVia !== null
            && $receivedVia->type === ShortLinkType::Upload
            && $receivedVia->isUsable()
            && (! $receivedVia->needsPassword() || $this->isUnlocked($receivedVia))
        );
    }

    /**
     * The session key that stores whether the link was unlocked.
     */
    private function unlockSessionKey(ShortLink $shortLink): string
    {
        return 'short-link-unlock-'.$shortLink->code;
    }

    /**
     * Whether the current session already unlocked the link.
     */
    private function isUnlocked(ShortLink $shortLink): bool
    {
        return (bool) session($this->unlockSessionKey($shortLink), false);
    }
}
