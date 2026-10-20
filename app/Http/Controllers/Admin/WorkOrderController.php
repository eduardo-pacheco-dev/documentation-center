<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WorkOrderItemStatus;
use App\Enums\WorkOrderStatus;
use App\Exports\WorkOrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderRequest;
use App\Http\Requests\Admin\UpdateWorkOrderDatesRequest;
use App\Http\Requests\Admin\UpdateWorkOrderDescriptionRequest;
use App\Http\Requests\Admin\UpdateWorkOrderItemsRequest;
use App\Http\Requests\Admin\UpdateWorkOrderNotesRequest;
use App\Http\Requests\Admin\UpdateWorkOrderRequest;
use App\Models\CatalogItem;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkOrderController extends Controller
{
    /**
     * List the work orders the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkOrder::class);

        ['search' => $search, 'status' => $status, 'sort' => $sort, 'direction' => $direction] = $this->resolveFilters($request);

        $perPage = in_array($request->integer('per_page'), [12, 24, 36], true)
            ? $request->integer('per_page')
            : 12;

        $workOrders = WorkOrder::query()
            ->ownedBy($request->user())
            ->with(['client:id,name', 'erb:id,site_id,code', 'radioLink:id,code,erb_a_id,erb_b_id', 'radioLink.erbA:id,site_id,code', 'radioLink.erbB:id,site_id,code'])
            ->withCount('items')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('number', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('client', fn (Builder $client) => $client->where('name', 'like', "%{$search}%"))))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        $owned = fn () => WorkOrder::query()->ownedBy($request->user());

        $stats = [
            'total' => $owned()->count(),
            'open' => $owned()->where('status', WorkOrderStatus::Open->value)->count(),
            'inProgress' => $owned()->where('status', WorkOrderStatus::InProgress->value)->count(),
            'onHold' => $owned()->where('status', WorkOrderStatus::OnHold->value)->count(),
            'completed' => $owned()->where('status', WorkOrderStatus::Completed->value)->count(),
        ];

        return view('admin.work-orders.index', [
            'workOrders' => $workOrders,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
            'view' => $view,
            'perPage' => $perPage,
            'stats' => $stats,
            ...$this->formData($request->user()),
        ]);
    }

    /**
     * Show the form to create a work order.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', WorkOrder::class);

        return view('admin.work-orders.create', $this->formData($request->user()));
    }

    /**
     * Download the work orders, honoring the current list filters, as a spreadsheet.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', WorkOrder::class);

        ['search' => $search, 'status' => $status, 'sort' => $sort, 'direction' => $direction] = $this->resolveFilters($request);

        return Excel::download(
            new WorkOrdersExport($request->user(), $search, $status, $sort, $direction),
            'ordens-de-servico.xlsx',
        );
    }

    /**
     * Create a work order owned by the authenticated user.
     */
    public function store(StoreWorkOrderRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $items = $attributes['items'];
        $clientOrders = $attributes['client_orders'] ?? [];
        unset($attributes['items'], $attributes['client_orders']);

        $attributes['erb_id'] = $request->input('erb_id') ?: null;
        $attributes['radio_link_id'] = $request->input('radio_link_id') ?: null;

        $status = WorkOrderStatus::from($attributes['status'] ?? WorkOrderStatus::Open->value);
        $attributes['status'] = $status->value;
        $attributes['completed_at'] = $this->resolveCompletedAt($status, null);

        $workOrder = DB::transaction(function () use ($request, $attributes, $items, $clientOrders): WorkOrder {
            $attributes['number'] = WorkOrder::generateNumber($request->user());

            $workOrder = $request->user()->workOrders()->create($attributes);
            $this->syncClientOrders($workOrder, $clientOrders);
            $workOrder->items()->createMany($this->normalizeItems($request->user(), $items));
            $workOrder->update(['total' => $workOrder->items()->sum('total')]);

            return $workOrder;
        });

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', 'Ordem de serviço criada com sucesso.');
    }

    /**
     * Show the work order details.
     */
    public function show(Request $request, WorkOrder $workOrder): View
    {
        $this->authorize('view', $workOrder);

        $comments = $workOrder->comments()
            ->with(['user', 'documents'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $workOrder->load(['client', 'erb', 'radioLink', 'clientOrders', 'items.catalogItem', 'items.documents', 'project']);

        return view('admin.work-orders.show', [
            ...$this->formData($request->user()),
            'workOrder' => $workOrder,
            'comments' => $comments,
        ]);
    }

    /**
     * Show the form to edit the work order.
     */
    public function edit(Request $request, WorkOrder $workOrder): View
    {
        $this->authorize('update', $workOrder);

        $comments = $workOrder->comments()
            ->with(['user', 'documents'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $workOrder->load(['client', 'erb', 'radioLink', 'clientOrders', 'items.catalogItem', 'items.documents', 'project']);

        return view('admin.work-orders.show', [
            ...$this->formData($request->user()),
            'workOrder' => $workOrder,
            'comments' => $comments,
            'openEditModal' => true,
        ]);
    }

    /**
     * Update the work order.
     */
    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $attributes = $request->validated();
        $items = $attributes['items'];
        $clientOrders = $attributes['client_orders'] ?? [];
        unset($attributes['items'], $attributes['client_orders']);

        $attributes['erb_id'] = $request->input('erb_id') ?: null;
        $attributes['radio_link_id'] = $request->input('radio_link_id') ?: null;

        $status = WorkOrderStatus::from($attributes['status'] ?? $workOrder->status->value);
        $attributes['status'] = $status->value;
        $attributes['completed_at'] = $this->resolveCompletedAt($status, $workOrder->completed_at);

        DB::transaction(function () use ($request, $workOrder, $attributes, $items, $clientOrders): void {
            $workOrder->update($attributes);

            $workOrder->items()->delete();
            $this->syncClientOrders($workOrder, $clientOrders);

            $workOrder->items()->createMany($this->normalizeItems($request->user(), $items));
            $workOrder->update(['total' => $workOrder->items()->sum('total')]);
        });

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', 'Ordem de serviço atualizada com sucesso.');
    }

    /**
     * Update only the line items of the work order.
     */
    public function updateItems(UpdateWorkOrderItemsRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $items = $request->validated('items');

        DB::transaction(function () use ($request, $workOrder, $items): void {
            $workOrder->items()->delete();
            $workOrder->items()->createMany($this->normalizeItems($request->user(), $items));
            $workOrder->update(['total' => $workOrder->items()->sum('total')]);
        });

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', 'Itens da ordem de serviço atualizados com sucesso.');
    }

    /**
     * Update only the dates of the work order.
     */
    public function updateDates(UpdateWorkOrderDatesRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $workOrder->update([
            'opened_at' => $request->validated('opened_at'),
            'due_at' => $request->validated('due_at'),
        ]);

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', 'Datas atualizadas com sucesso.');
    }

    /**
     * Update only the description of the work order.
     */
    public function updateDescription(UpdateWorkOrderDescriptionRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $workOrder->update(['description' => $request->validated('description')]);

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', 'Descrição atualizada com sucesso.');
    }

    /**
     * Update only the notes of the work order.
     */
    public function updateNotes(UpdateWorkOrderNotesRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $workOrder->update(['notes' => $request->validated('notes')]);

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('status', 'Observações atualizadas com sucesso.');
    }

    /**
     * Delete the work order.
     */
    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('delete', $workOrder);

        $workOrder->delete();

        return redirect()
            ->route('admin.work-orders.index')
            ->with('status', 'Ordem de serviço excluída com sucesso.');
    }

    /**
     * Resolve the supported list filters and ordering from the request.
     *
     * @return array{search: string, status: ?string, sort: string, direction: string}
     */
    private function resolveFilters(Request $request): array
    {
        $search = (string) $request->query('search', '');

        $statuses = array_column(WorkOrderStatus::cases(), 'value');

        $status = in_array($request->query('status'), $statuses, true)
            ? $request->query('status')
            : null;

        $sortableColumns = ['number', 'priority', 'status', 'opened_at', 'due_at', 'total', 'updated_at'];

        if (in_array($request->query('sort'), $sortableColumns, true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'updated_at' ? 'desc' : 'asc');
        } else {
            $sort = 'updated_at';
            $direction = 'desc';
        }

        return [
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    /**
     * Build the data shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(User $user): array
    {
        $clients = $user->clients()->orderBy('name')->get(['id', 'name']);

        $catalogItems = $user->catalogItems()->orderBy('name')->get(['id', 'name', 'unit', 'price', 'type']);

        $erbs = $user->erbs()->orderBy('name')->get(['id', 'code', 'name']);

        $radioLinks = $user->radioLinks()
            ->with(['erbA:id,code', 'erbB:id,code'])
            ->orderBy('code')
            ->get(['id', 'code', 'erb_a_id', 'erb_b_id']);

        return [
            'clients' => $clients,
            'catalogItems' => $catalogItems,
            'erbs' => $erbs,
            'radioLinks' => $radioLinks,
            'catalogData' => $catalogItems->map(fn (CatalogItem $item): array => [
                'id' => $item->getKey(),
                'name' => $item->name,
                'unit' => $item->unit,
                'price' => (float) $item->price,
            ])->values(),
        ];
    }

    /**
     * Normalize the submitted line items and compute their totals.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(User $user, array $items): array
    {
        $catalog = $user->catalogItems()->get(['id', 'name', 'unit', 'price'])->keyBy('id');

        return array_map(function (array $item) use ($catalog): array {
            $catalogItem = $catalog->get($item['catalog_item_id']);
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) ($catalogItem?->price ?? 0);

            return [
                'catalog_item_id' => $catalogItem?->getKey(),
                'description' => $catalogItem?->name ?? 'Item',
                'client_os' => filled($item['client_os'] ?? null) ? (string) $item['client_os'] : null,
                'unit' => $catalogItem?->unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => round($quantity * $unitPrice, 2),
                'status' => WorkOrderItemStatus::tryFrom((string) ($item['status'] ?? '')) ?? WorkOrderItemStatus::Pending,
            ];
        }, array_values($items));
    }

    /**
     * Replace the client work orders of the work order.
     *
     * @param  array<int, array{number: string, description: ?string}>  $clientOrders
     */
    private function syncClientOrders(WorkOrder $workOrder, array $clientOrders): void
    {
        $workOrder->clientOrders()->delete();

        foreach ($clientOrders as $clientOrder) {
            $workOrder->clientOrders()->create([
                'number' => $clientOrder['number'],
                'description' => $clientOrder['description'] ?? null,
            ]);
        }
    }

    /**
     * Resolve the completion date based on the work order status.
     */
    private function resolveCompletedAt(WorkOrderStatus $status, ?Carbon $current): ?string
    {
        if ($status !== WorkOrderStatus::Completed) {
            return null;
        }

        return $current?->toDateString() ?? now()->toDateString();
    }
}
