<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrashController extends Controller
{
    /**
     * List every trashed folder and file owned by the authenticated user.
     */
    public function index(Request $request): View
    {
        $documents = Document::onlyTrashed()->whereBelongsTo($request->user())->get();
        $folders = Folder::onlyTrashed()->whereBelongsTo($request->user())->get();

        $items = $folders
            ->map(fn (Folder $folder): array => ['type' => 'folder', 'model' => $folder])
            ->concat($documents->map(fn (Document $document): array => ['type' => 'document', 'model' => $document]))
            ->sortByDesc(fn (array $item): ?\DateTimeInterface => $item['model']->deleted_at)
            ->values();

        return view('admin.trash.index', ['items' => $items]);
    }

    /**
     * Restore a trashed file, bringing back any ancestor folder still in the trash.
     */
    public function restoreDocument(Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $document->restoreWithAncestors();

        return back()->with('status', 'Arquivo restaurado.');
    }

    /**
     * Permanently delete a trashed file and its storage copy.
     */
    public function forceDestroyDocument(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->forceDelete();

        return back()->with('status', 'Arquivo excluído definitivamente.');
    }

    /**
     * Restore a trashed folder together with its descendants and ancestor chain.
     */
    public function restoreFolder(Folder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        $folder->restoreTree();

        return back()->with('status', 'Pasta restaurada.');
    }

    /**
     * Permanently delete a trashed folder, its contents and their storage files.
     */
    public function forceDestroyFolder(Folder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        $folder->forceDelete();

        return back()->with('status', 'Pasta excluída definitivamente.');
    }

    /**
     * Empty the trash of the authenticated user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Folder::onlyTrashed()->whereBelongsTo($request->user())->get()->each->forceDelete();
        Document::onlyTrashed()->whereBelongsTo($request->user())->get()->each->forceDelete();

        return back()->with('status', 'Lixeira esvaziada.');
    }
}
