<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderRequest;
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

class WorkOrderController extends Controller
{
    /**
     * List the work orders the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkOrder::class);

        $search = (string) $request->query('search', '');

        $statuses = array_column(WorkOrderStatus::cases(), 'value');

        $status = in_array($request->query('status'), $statuses, true)
            ? $request->query('status')
            : null;

        $sortableColumns = ['number', 'title', 'priority', 'status', 'opened_at', 'due_at', 'total', 'updated_at'];

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

        $workOrders = WorkOrder::query()
            ->ownedBy($request->user())
            ->with('client')
            ->withCount('items')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('number', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhereHas('client', fn (Builder $client) => $client->where('name', 'like', "%{$search}%"))))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.work-orders.index', [
            'workOrders' => $workOrders,
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'perPage' => $perPage,
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
     * Create a work order owned by the authenticated user.
     */
    public function store(StoreWorkOrderRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $items = $attributes['items'];
        unset($attributes['items']);

        $status = WorkOrderStatus::from($attributes['status'] ?? WorkOrderStatus::Open->value);
        $attributes['status'] = $status->value;
        $attributes['completed_at'] = $this->resolveCompletedAt($status, null);

        $workOrder = DB::transaction(function () use ($request, $attributes, $items): WorkOrder {
            $attributes['number'] = $this->nextNumber($request->user());

            $workOrder = $request->user()->workOrders()->create($attributes);
            $workOrder->items()->createMany($this->normalizeItems($items));
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
    public function show(WorkOrder $workOrder): View
    {
        $this->authorize('view', $workOrder);

        $workOrder->load(['client', 'items.catalogItem']);

        return view('admin.work-orders.show', ['workOrder' => $workOrder]);
    }

    /**
     * Show the form to edit the work order.
     */
    public function edit(Request $request, WorkOrder $workOrder): View
    {
        $this->authorize('update', $workOrder);

        $workOrder->load('items');

        return view('admin.work-orders.edit', [
            ...$this->formData($request->user()),
            'workOrder' => $workOrder,
        ]);
    }

    /**
     * Update the work order.
     */
    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $attributes = $request->validated();
        $items = $attributes['items'];
        unset($attributes['items']);

        $status = WorkOrderStatus::from($attributes['status'] ?? $workOrder->status->value);
        $attributes['status'] = $status->value;
        $attributes['completed_at'] = $this->resolveCompletedAt($status, $workOrder->completed_at);

        DB::transaction(function () use ($workOrder, $attributes, $items): void {
            $workOrder->update($attributes);

            $workOrder->items()->delete();
            $workOrder->items()->createMany($this->normalizeItems($items));
            $workOrder->update(['total' => $workOrder->items()->sum('total')]);
        });

        return back()->with('status', 'Ordem de serviço atualizada com sucesso.');
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
     * Build the data shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(User $user): array
    {
        $clients = $user->clients()->orderBy('name')->get(['id', 'name']);

        $catalogItems = $user->catalogItems()->orderBy('name')->get(['id', 'name', 'unit', 'price', 'type']);

        return [
            'clients' => $clients,
            'catalogItems' => $catalogItems,
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
    private function normalizeItems(array $items): array
    {
        return array_map(function (array $item): array {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            return [
                'catalog_item_id' => $item['catalog_item_id'] ?? null,
                'description' => $item['description'],
                'unit' => $item['unit'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => round($quantity * $unitPrice, 2),
            ];
        }, array_values($items));
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

    /**
     * Generate the next sequential number for the user's work orders.
     */
    private function nextNumber(User $user): string
    {
        $sequence = $user->workOrders()->withTrashed()->count() + 1;

        do {
            $number = 'OS-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($user->workOrders()->withTrashed()->where('number', $number)->exists());

        return $number;
    }
}
