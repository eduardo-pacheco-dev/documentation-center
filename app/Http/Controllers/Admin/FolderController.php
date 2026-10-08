<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;

class FolderController extends Controller
{
    /**
     * Create a folder owned by the authenticated user.
     */
    public function store(StoreFolderRequest $request): RedirectResponse
    {
        $request->user()->folders()->create([
            'name' => $request->validated('name'),
            'parent_id' => $request->validated('parent_id'),
        ]);

        return back()->with('status', 'Pasta criada com sucesso.');
    }

    /**
     * Rename a folder owned by the authenticated user.
     */
    public function update(UpdateFolderRequest $request, Folder $folder): RedirectResponse
    {
        $folder->update(['name' => $request->validated('name')]);

        return back()->with('status', 'Pasta renomeada com sucesso.');
    }

    /**
     * Move a folder to the trash. Child folders and files go with it and can
     * be restored together from the trash.
     */
    public function destroy(Folder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        $folder->delete();

        return back()->with('status', 'Pasta movida para a lixeira.');
    }
}
