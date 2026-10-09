@php
    $workOrder = $workOrder ?? null;
    $oldItems = old('items');

    if (is_array($oldItems)) {
        $initialItems = array_values(array_map(fn ($item) => [
            'catalog_item_id' => $item['catalog_item_id'] ?? '',
            'description' => $item['description'] ?? '',
            'unit' => $item['unit'] ?? '',
            'quantity' => $item['quantity'] ?? 1,
            'unit_price' => $item['unit_price'] ?? 0,
        ], $oldItems));
    } elseif ($workOrder) {
        $initialItems = $workOrder->items->map(fn ($item) => [
            'catalog_item_id' => $item->catalog_item_id ?? '',
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
        ])->values()->all();
    } else {
        $initialItems = [];
    }

    if ($initialItems === []) {
        $initialItems = [['catalog_item_id' => '', 'description' => '', 'unit' => '', 'quantity' => 1, 'unit_price' => 0]];
    }

    $itemErrors = collect($errors->keys())
        ->filter(fn ($key) => str_starts_with($key, 'items.'))
        ->flatMap(fn ($key) => $errors->get($key))
        ->values()
        ->all();
@endphp

<script type="application/json" id="work-order-catalog-data">@json($catalogData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
<script type="application/json" id="work-order-initial-items">@json($initialItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
        <select
            name="client_id"
            id="client_id"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="">Selecione um cliente</option>
            @foreach ($clients as $client)
                <option value="{{ $client->getKey() }}" @selected((string) old('client_id', $workOrder?->client_id) === (string) $client->getKey())>
                    {{ $client->name }}
                </option>
            @endforeach
        </select>
        @error('client_id')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @if ($clients->isEmpty())
            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                Você ainda não tem clientes cadastrados.
                <a href="{{ route('admin.clients.create') }}" class="underline">Cadastre um cliente</a>
                antes de abrir uma ordem de serviço.
            </p>
        @endif
    </div>

    <div class="sm:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
        <input
            type="text"
            name="title"
            id="title"
            value="{{ old('title', $workOrder?->title) }}"
            required
            placeholder="Ex.: Manutenção elétrica do galpão"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('title')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridade</label>
        <select
            name="priority"
            id="priority"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach (\App\Enums\WorkOrderPriority::cases() as $priority)
                <option value="{{ $priority->value }}" @selected(old('priority', $workOrder?->priority?->value ?? 'normal') === $priority->value)>
                    {{ $priority->label() }}
                </option>
            @endforeach
        </select>
        @error('priority')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select
            name="status"
            id="status"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach (\App\Enums\WorkOrderStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $workOrder?->status?->value ?? 'open') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="opened_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de abertura</label>
        <input
            type="date"
            name="opened_at"
            id="opened_at"
            required
            value="{{ old('opened_at', $workOrder?->opened_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('opened_at')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="due_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Previsão de entrega</label>
        <input
            type="date"
            name="due_at"
            id="due_at"
            value="{{ old('due_at', $workOrder?->due_at?->format('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('due_at')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
        <textarea
            name="description"
            id="description"
            rows="3"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >{{ old('description', $workOrder?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div
        class="sm:col-span-2"
        x-data="{
            catalog: JSON.parse(document.getElementById('work-order-catalog-data').textContent),
            items: JSON.parse(document.getElementById('work-order-initial-items').textContent),
            addItem() {
                this.items.push({ catalog_item_id: '', description: '', unit: '', quantity: 1, unit_price: 0 });
            },
            removeItem(index) {
                this.items.splice(index, 1);
                if (this.items.length === 0) {
                    this.addItem();
                }
            },
            applyCatalog(index) {
                const item = this.items[index];
                const selected = this.catalog.find((entry) => String(entry.id) === String(item.catalog_item_id));
                if (selected) {
                    item.description = selected.name;
                    item.unit = selected.unit ?? '';
                    item.unit_price = selected.price;
                }
            },
            lineTotal(item) {
                return (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
            },
            get total() {
                return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
            },
            money(value) {
                return 'R$ ' + (Number(value) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        }"
    >
        <div class="flex items-center justify-between">
            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Itens</span>
            <button
                type="button"
                @click="addItem()"
                class="inline-flex items-center gap-1 rounded-md border border-gray-300 dark:border-gray-700 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                <x-icon name="plus" class="h-3.5 w-3.5" />
                Adicionar item
            </button>
        </div>

        @if (! empty($itemErrors))
            <div class="mt-2 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc space-y-0.5 pl-4">
                    @foreach ($itemErrors as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-2 space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                    <div class="grid gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-4">
                            <label class="block text-xs text-gray-500 dark:text-gray-400" x-bind:for="'item-catalog-' + index">Item do catálogo</label>
                            <select
                                x-bind:id="'item-catalog-' + index"
                                x-bind:name="'items[' + index + '][catalog_item_id]'"
                                x-model="item.catalog_item_id"
                                @change="applyCatalog(index)"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                                <option value="">— Item livre —</option>
                                @foreach ($catalogItems as $catalogItem)
                                    <option value="{{ $catalogItem->getKey() }}">{{ $catalogItem->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-5">
                            <label class="block text-xs text-gray-500 dark:text-gray-400" x-bind:for="'item-description-' + index">Descrição</label>
                            <input
                                type="text"
                                x-bind:id="'item-description-' + index"
                                x-bind:name="'items[' + index + '][description]'"
                                x-model="item.description"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-xs text-gray-500 dark:text-gray-400" x-bind:for="'item-unit-' + index">Unidade</label>
                            <input
                                type="text"
                                x-bind:id="'item-unit-' + index"
                                x-bind:name="'items[' + index + '][unit]'"
                                x-model="item.unit"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-xs text-gray-500 dark:text-gray-400" x-bind:for="'item-quantity-' + index">Quantidade</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                x-bind:id="'item-quantity-' + index"
                                x-bind:name="'items[' + index + '][quantity]'"
                                x-model.number="item.quantity"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                        </div>

                        <div class="sm:col-span-4">
                            <label class="block text-xs text-gray-500 dark:text-gray-400" x-bind:for="'item-price-' + index">Valor unitário (R$)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                x-bind:id="'item-price-' + index"
                                x-bind:name="'items[' + index + '][unit_price]'"
                                x-model.number="item.unit_price"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                        </div>

                        <div class="flex items-end justify-between gap-2 sm:col-span-5">
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Subtotal</span>
                                <span class="mt-1 block text-sm font-medium text-gray-900 dark:text-gray-100" x-text="money(lineTotal(item))"></span>
                            </div>

                            <button
                                type="button"
                                @click="removeItem(index)"
                                class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15 dark:hover:text-red-400"
                                aria-label="Remover item"
                            >
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
            <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="money(total)"></span>
        </div>
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
        <textarea
            name="notes"
            id="notes"
            rows="3"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >{{ old('notes', $workOrder?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
