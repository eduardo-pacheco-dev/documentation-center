<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShortLinkType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentsRequest;
use App\Http\Requests\StoreShortLinkRequest;
use App\Http\Requests\UpdateShortLinkRequest;
use App\Models\Document;
use App\Models\ShortLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ShortLinkController extends Controller
{
    /**
     * List every link owned by the authenticated user.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $type = in_array($request->query('type'), array_column(ShortLinkType::cases(), 'value'), true)
            ? $request->query('type')
            : null;

        $status = in_array($request->query('status'), ['active', 'inactive', 'expired', 'limit'], true)
            ? $request->query('status')
            : null;

        $perPage = in_array($request->integer('per_page'), [10, 25, 50], true)
            ? $request->integer('per_page')
            : 10;

        $sortableColumns = ['code', 'title', 'type', 'used_count', 'documents', 'created_at'];

        if (in_array($request->query('sort'), $sortableColumns, true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'created_at' ? 'desc' : 'asc');
        } else {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $query = ShortLink::query()
            ->whereBelongsTo($request->user())
            ->with('documents')
            ->withCount(['documents', 'receivedDocuments']);

        $document = null;

        if ($request->filled('document')) {
            $document = $request->user()->documents()->whereKey($request->integer('document'))->firstOrFail();

            $query->whereHas('documents', fn (Builder $builder): Builder => $builder->whereKey($document->getKey()));
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type !== null) {
            $query->where('type', $type);
        }

        if ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'expired') {
            $query->where('is_active', true)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now());
        } elseif ($status === 'limit') {
            $query->where('is_active', true)
                ->where(fn (Builder $builder): Builder => $builder->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->whereNotNull('max_uses')
                ->whereColumn('used_count', '>=', 'max_uses');
        } elseif ($status === 'active') {
            $query->where('is_active', true)
                ->where(fn (Builder $builder): Builder => $builder->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->where(fn (Builder $builder): Builder => $builder->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses'));
        }

        if ($sort === 'documents') {
            $query->orderByRaw("(documents_count + received_documents_count) {$direction}");
        } else {
            $query->orderBy($sort, $direction);
        }

        $query->orderBy('id', $direction);

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.links.index', [
            'shortLinks' => $query->paginate($perPage)->withQueryString(),
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'document' => $document,
            'view' => $view,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form to create a new link.
     */
    public function create(): View
    {
        return view('admin.links.create');
    }

    /**
     * Create a new short link for the authenticated user.
     */
    public function store(StoreShortLinkRequest $request): RedirectResponse
    {
        $attributes = $request->safe()->except(['password', 'document_ids']);

        $attributes['user_id'] = $request->user()->getKey();
        $attributes['code'] = ShortLink::createCode();
        $attributes['is_active'] = $request->boolean('is_active');
        $attributes['redirect_url'] = $request->input('type') === ShortLinkType::Redirect->value
            ? $request->string('redirect_url')->toString()
            : null;

        if ($request->filled('password')) {
            $attributes['password'] = Hash::make($request->string('password'));
        }

        $shortLink = ShortLink::create($attributes);

        if ($shortLink->type === ShortLinkType::Download && $request->filled('document_ids')) {
            $shortLink->documents()->attach($request->input('document_ids'));
        }

        return redirect()
            ->route('admin.links.index')
            ->with('status', 'Link criado com sucesso.');
    }

    /**
     * Update an existing short link.
     */
    public function update(UpdateShortLinkRequest $request, ShortLink $shortLink): RedirectResponse
    {
        $attributes = $request->safe()->except(['password', 'is_active']);

        if ($request->has('is_active')) {
            $attributes['is_active'] = $request->boolean('is_active');
        }

        if ($request->has('password')) {
            $attributes['password'] = $request->filled('password')
                ? Hash::make($request->string('password'))
                : null;
        }

        if ($request->has('type')) {
            $attributes['redirect_url'] = $request->input('type') === ShortLinkType::Redirect->value
                ? $request->string('redirect_url')->toString()
                : null;
        }

        $shortLink->update($attributes);

        return back()->with('status', 'Link atualizado com sucesso.');
    }

    /**
     * Remove a short link and its attached documents.
     */
    public function destroy(ShortLink $shortLink): RedirectResponse
    {
        $this->authorize('delete', $shortLink);

        $shortLink->delete();

        return redirect()->route('admin.links.index')->with('status', 'Link excluído com sucesso.');
    }

    /**
     * Attach uploaded documents to a link.
     */
    public function storeDocuments(StoreDocumentsRequest $request, ShortLink $shortLink): RedirectResponse
    {
        $this->authorize('update', $shortLink);

        $count = 0;

        foreach ($request->file('documents') as $file) {
            $document = $request->user()->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_via_short_link_id' => $shortLink->type === ShortLinkType::Upload ? $shortLink->getKey() : null,
            ]);

            $shortLink->documents()->attach($document);

            $count++;
        }

        return back()
            ->with('status', $count.' documento(s) adicionado(s) ao link.')
            ->with('open_link_edit', $shortLink->getKey());
    }

    /**
     * Remove a single document from a link.
     */
    public function destroyDocument(ShortLink $shortLink, Document $document): RedirectResponse
    {
        $this->authorize('update', $shortLink);

        if ($shortLink->type === ShortLinkType::Upload) {
            abort_unless($document->uploaded_via_short_link_id === $shortLink->getKey(), 404);

            $document->delete();

            return back()
                ->with('status', 'Documento recebido movido para a lixeira.')
                ->with('open_link_edit', $shortLink->getKey());
        }

        abort_unless($shortLink->documents()->whereKey($document->getKey())->exists(), 404);

        $shortLink->documents()->detach($document);

        return back()
            ->with('status', 'Documento removido do link.')
            ->with('open_link_edit', $shortLink->getKey());
    }
}
