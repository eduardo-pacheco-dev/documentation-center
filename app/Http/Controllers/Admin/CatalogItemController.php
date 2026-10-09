<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CatalogItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCatalogItemRequest;
use App\Http\Requests\Admin\UpdateCatalogItemRequest;
use App\Models\CatalogItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogItemController extends Controller
{
    /**
     * List the catalog items the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CatalogItem::class);

        $search = (string) $request->query('search', '');

        $type = in_array($request->query('type'), ['product', 'service'], true)
            ? $request->query('type')
            : null;

        $sortableColumns = ['name', 'code', 'type', 'unit', 'price', 'cost', 'stock_quantity', 'status', 'created_at', 'updated_at'];

        if (in_array($request->query('sort'), $sortableColumns, true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'updated_at' ? 'desc' : 'asc');
        } else {
            $sort = 'updated_at';
            $direction = 'desc';
        }

        $perPage = in_array($request->integer('per_page'), [12, 24, 36], true)
            ? $request->integer('per_page')
            : 12;

        $catalogItems = CatalogItem::query()
            ->ownedBy($request->user())
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($type !== null, fn (Builder $query) => $query->where('type', $type))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.catalog.index', [
            'catalogItems' => $catalogItems,
            'search' => $search,
            'type' => $type,
            'view' => $view,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form to create a catalog item.
     */
    public function create(): View
    {
        $this->authorize('create', CatalogItem::class);

        return view('admin.catalog.create', [
            'types' => CatalogItemType::cases(),
        ]);
    }

    /**
     * Create a catalog item owned by the authenticated user.
     */
    public function store(StoreCatalogItemRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['status'] ??= 'active';

        if ($attributes['type'] === CatalogItemType::Service->value) {
            $attributes['stock_quantity'] = null;
        }

        $catalogItem = $request->user()->catalogItems()->create($attributes);

        return redirect()
            ->route('admin.catalog.show', $catalogItem)
            ->with('status', 'Item criado com sucesso.');
    }

    /**
     * Show the catalog item details.
     */
    public function show(CatalogItem $catalogItem): View
    {
        $this->authorize('view', $catalogItem);

        return view('admin.catalog.show', ['catalogItem' => $catalogItem]);
    }

    /**
     * Show the form to edit the catalog item details.
     */
    public function edit(CatalogItem $catalogItem): View
    {
        $this->authorize('update', $catalogItem);

        return view('admin.catalog.edit', [
            'catalogItem' => $catalogItem,
            'types' => CatalogItemType::cases(),
        ]);
    }

    /**
     * Update the catalog item details.
     */
    public function update(UpdateCatalogItemRequest $request, CatalogItem $catalogItem): RedirectResponse
    {
        $attributes = $request->validated();

        if (($attributes['type'] ?? $catalogItem->type->value) === CatalogItemType::Service->value) {
            $attributes['stock_quantity'] = null;
        }

        $catalogItem->update($attributes);

        return back()->with('status', 'Item atualizado com sucesso.');
    }

    /**
     * Delete the catalog item.
     */
    public function destroy(CatalogItem $catalogItem): RedirectResponse
    {
        $this->authorize('delete', $catalogItem);

        $catalogItem->delete();

        return redirect()
            ->route('admin.catalog.index')
            ->with('status', 'Item excluído com sucesso.');
    }
}
