<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShortLinkType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateDownloadLinkRequest;
use App\Http\Requests\RenameDocumentRequest;
use App\Http\Requests\StoreDocumentsRequest;
use App\Models\Document;
use App\Models\ShortLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * List every file owned by the authenticated user.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $sortableColumns = ['original_name', 'size', 'short_links_count', 'created_at'];

        if (in_array($request->query('sort'), $sortableColumns, true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'created_at' ? 'desc' : 'asc');
        } else {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $query = Document::query()
            ->whereBelongsTo($request->user())
            ->withCount('shortLinks');

        if ($search !== '') {
            $query->where('original_name', 'like', "%{$search}%");
        }

        $query->orderBy($sort, $direction)->orderBy('id', $direction);

        return view('admin.files.index', [
            'documents' => $query->paginate(10)->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Store files uploaded by the authenticated user.
     */
    public function store(StoreDocumentsRequest $request): RedirectResponse
    {
        $count = 0;

        foreach ($request->file('documents') as $file) {
            $request->user()->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $count++;
        }

        return back()->with('status', $count.' arquivo(s) enviado(s) com sucesso.');
    }

    /**
     * Create a download link that shares the selected files.
     */
    public function generateLink(GenerateDownloadLinkRequest $request): RedirectResponse
    {
        $shortLink = ShortLink::create([
            'user_id' => $request->user()->getKey(),
            'code' => ShortLink::createCode(),
            'type' => ShortLinkType::Download,
            'title' => $request->input('title') ?: 'Downloads de arquivos',
            'is_active' => true,
        ]);

        $shortLink->documents()->attach($request->validated('document_ids'));

        return redirect()
            ->route('admin.links.edit', $shortLink)
            ->with('status', 'Link de download criado com '.count($request->validated('document_ids')).' arquivo(s).');
    }

    /**
     * Rename a file owned by the authenticated user.
     */
    public function update(RenameDocumentRequest $request, Document $document): RedirectResponse
    {
        $document->update($request->validated());

        return back()->with('status', 'Nome do arquivo atualizado com sucesso.');
    }

    /**
     * Remove a file and the underlying storage file.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Arquivo excluído com sucesso.');
    }
}
