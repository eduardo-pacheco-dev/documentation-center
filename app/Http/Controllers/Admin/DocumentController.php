<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShortLinkType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateDownloadLinkRequest;
use App\Http\Requests\MoveDocumentsRequest;
use App\Http\Requests\RenameDocumentRequest;
use App\Http\Requests\StoreDocumentsRequest;
use App\Models\Document;
use App\Models\Folder;
use App\Models\ShortLink;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * List every file owned by the authenticated user, inside the opened folder.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $folder = null;

        if ($request->filled('folder')) {
            $folder = $request->user()->folders()->whereKey($request->integer('folder'))->firstOrFail();
        }

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

        $foldersQuery = $request->user()->folders()
            ->where('parent_id', $folder?->getKey())
            ->withCount('documents');

        if ($search !== '') {
            $foldersQuery->where('name', 'like', "%{$search}%");
        }

        $query = Document::query()
            ->whereBelongsTo($request->user())
            ->where('folder_id', $folder?->getKey())
            ->withCount('shortLinks')
            ->with([
                'shortLinks' => fn (BelongsToMany $relation): BelongsToMany => $relation
                    ->whereBelongsTo($request->user())
                    ->latest(),
            ]);

        if ($search !== '') {
            $query->where('original_name', 'like', "%{$search}%");
        }

        $query->orderBy($sort, $direction)->orderBy('id', $direction);

        $view = in_array($request->query('view'), ['table', 'cards', 'trash'], true)
            ? $request->query('view')
            : 'table';

        $items = collect();

        if ($view === 'trash') {
            $trashedFolders = Folder::onlyTrashed()->whereBelongsTo($request->user())->get();
            $trashedDocuments = Document::onlyTrashed()->whereBelongsTo($request->user())->get();

            $items = $trashedFolders
                ->map(fn (Folder $folder): array => ['type' => 'folder', 'model' => $folder])
                ->concat($trashedDocuments->map(fn (Document $document): array => ['type' => 'document', 'model' => $document]))
                ->sortByDesc(fn (array $item): ?\DateTimeInterface => $item['model']->deleted_at)
                ->values();
        }

        return view('admin.files.index', [
            'documents' => $query->paginate(10)->withQueryString(),
            'folders' => $foldersQuery->orderBy('name')->get(),
            'folder' => $folder,
            'breadcrumbs' => $folder?->ancestors() ?? [],
            'search' => $search,
            'view' => $view,
            'folderTree' => $this->folderTree($request),
            'items' => $items,
        ]);
    }

    /**
     * Move files to a folder owned by the authenticated user.
     */
    public function move(MoveDocumentsRequest $request): RedirectResponse
    {
        $documentIds = $request->validated('document_ids');

        $request->user()->documents()->whereKey($documentIds)->update([
            'folder_id' => $request->validated('folder'),
        ]);

        return back()->with('status', count($documentIds).' arquivo(s) movido(s).');
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
                'folder_id' => $request->validated('folder'),
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
     * Stream a file owned by the authenticated user for in-browser preview.
     */
    public function preview(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        $disk = Storage::disk($document->disk);

        abort_unless($disk->exists($document->path), 404);

        return $disk->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type ?? 'application/octet-stream',
        ], 'inline');
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
     * Remove a file from the library, moving it to the trash.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Arquivo movido para a lixeira.');
    }

    /**
     * Build the nested folder tree used by the "move to" picker.
     *
     * @return list<array{id: int, name: string, children: list<array{id: int, name: string, children: list<mixed>}>}>
     */
    private function folderTree(Request $request): array
    {
        $grouped = $request->user()->folders()
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name'])
            ->groupBy(fn (Folder $folder): string => $folder->parent_id ?? 'root');

        $buildTree = function (string $parentId) use (&$buildTree, $grouped): array {
            return $grouped->get($parentId, collect())
                ->map(fn (Folder $folder): array => [
                    'id' => $folder->getKey(),
                    'name' => $folder->name,
                    'children' => $buildTree((string) $folder->getKey()),
                ])
                ->values()
                ->all();
        };

        return $buildTree('root');
    }
}
