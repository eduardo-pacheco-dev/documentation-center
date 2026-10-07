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

        $documents = $shortLink->documents()->latest()->get(['id', 'original_name', 'size', 'created_at']);

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
            $shortLink->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return back()->with('status', 'Documentos enviados com sucesso.');
    }

    /**
     * Download a document attached to an accessible link.
     */
    public function download(Document $document): StreamedResponse
    {
        $shortLink = $document->shortLink()->first();

        abort_unless(
            $shortLink !== null
                && $shortLink->isUsable()
                && (! $shortLink->needsPassword() || $this->isUnlocked($shortLink)),
            404,
        );

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
