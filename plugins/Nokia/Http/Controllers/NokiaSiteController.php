<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaSiteRequest;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;

class NokiaSiteController extends Controller
{
    /**
     * List the registered sites.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $query = NokiaSite::query();

        if ($search !== '') {
            $query->matching($search);
        }

        $sites = $query
            ->latest('updated_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $total = NokiaSite::query()->count();
        $completed = NokiaSite::query()->completed()->count();

        $editSite = old('modal') === 'edit' && old('editing_id')
            ? NokiaSite::query()->find(old('editing_id'))
            : null;

        return view('nokia::index', [
            'sites' => $sites,
            'search' => $search,
            'editSite' => $editSite,
            'createHasErrors' => old('modal') === 'create' && session('errors')?->any(),
            'editHasErrors' => old('modal') === 'edit' && session('errors')?->any(),
            'stats' => [
                'total' => $total,
                'completed' => $completed,
                'pending' => $total - $completed,
            ],
        ]);
    }

    /**
     * Show the form to create a site.
     */
    public function create(): View
    {
        return view('nokia::create');
    }

    /**
     * Show the given site with its checklist and attachments.
     */
    public function show(NokiaSite $site): View
    {
        $site->load('attachments');

        $attachments = collect(NokiaSiteAttachment::categories())
            ->map(fn (string $label, string $category): array => [
                'category' => $category,
                'label' => $label,
                'files' => $site->attachments
                    ->where('category', $category)
                    ->values(),
            ])
            ->values();

        return view('nokia::show', [
            'site' => $site,
            'attachments' => $attachments,
        ]);
    }

    /**
     * Store a newly created site.
     */
    public function store(StoreNokiaSiteRequest $request): RedirectResponse
    {
        NokiaSite::query()->create($request->validated());

        return redirect()
            ->route('admin.nokia.sites.index')
            ->with('status', 'Site cadastrado com sucesso.');
    }

    /**
     * Show the form to edit the given site.
     */
    public function edit(NokiaSite $site): View
    {
        return view('nokia::edit', [
            'site' => $site,
        ]);
    }

    /**
     * Update the given site.
     */
    public function update(UpdateNokiaSiteRequest $request, NokiaSite $site): RedirectResponse
    {
        $site->update($request->validated());

        return redirect()
            ->route('admin.nokia.sites.index')
            ->with('status', 'Site atualizado com sucesso.');
    }

    /**
     * Remove the given site.
     */
    public function destroy(NokiaSite $site): RedirectResponse
    {
        $site->delete();

        return redirect()
            ->route('admin.nokia.sites.index')
            ->with('status', 'Site excluído com sucesso.');
    }
}
