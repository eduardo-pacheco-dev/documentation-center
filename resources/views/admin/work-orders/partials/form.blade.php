@php
    $workOrder = $workOrder ?? null;
    $oldItems = old('items');

    $itemStatus = fn ($value): string => \App\Enums\WorkOrderItemStatus::tryFrom((string) $value)?->value ?? \App\Enums\WorkOrderItemStatus::Pending->value;

    $clientOrders = $workOrder?->clientOrders ?? collect();

    $oldClientOrders = old('client_orders');

    if (is_array($oldClientOrders)) {
        $initialClientOrders = collect($oldClientOrders)->map(fn ($order, $key): array => [
            'key' => (string) $key,
            'number' => (string) ($order['number'] ?? ''),
            'description' => (string) ($order['description'] ?? ''),
        ])->values()->all();
    } else {
        $initialClientOrders = $clientOrders->values()->map(fn ($order, $index): array => [
            'key' => 'client-order-'.$index,
            'number' => $order->number,
            'description' => $order->description,
        ])->all();
    }

    if (is_array($oldItems)) {
        $initialItems = array_values(array_map(fn ($item) => [
            'catalog_item_id' => (string) ($item['catalog_item_id'] ?? ''),
            'quantity' => $item['quantity'] ?? 1,
            'status' => $itemStatus($item['status'] ?? null),
            'client_os' => (string) ($item['client_os'] ?? ''),
        ], $oldItems));
    } elseif ($workOrder) {
        $initialItems = $workOrder->items->map(fn ($item) => [
            'catalog_item_id' => (string) ($item->catalog_item_id ?? ''),
            'quantity' => (float) $item->quantity,
            'status' => $item->status->value,
            'client_os' => (string) ($item->client_os ?? ''),
        ])->values()->all();
    } else {
        $initialItems = [];
    }

    $itemErrors = collect($errors->keys())
        ->filter(fn ($key) => str_starts_with($key, 'items.'))
        ->flatMap(fn ($key) => $errors->get($key))
        ->values()
        ->all();

    $clientOrderErrors = collect($errors->keys())
        ->filter(fn ($key) => $key === 'client_orders' || str_starts_with($key, 'client_orders.'))
        ->flatMap(fn ($key) => $errors->get($key))
        ->values()
        ->all();

    $wizard = $wizard ?? false;
    $panelClass = fn (int $index): string => trim('grid gap-5 sm:grid-cols-2 '.($wizard && $index > 0 ? 'hidden' : ''));

    $selectedErb = old('erb_id', $workOrder?->erb_id);
    $selectedRadioLink = old('radio_link_id', $workOrder?->radio_link_id);
    $binding = $selectedErb ? 'erb' : ($selectedRadioLink ? 'radio' : '');
@endphp

<script type="application/json" id="work-order-catalog-data">@json($catalogData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
<script type="application/json" id="work-order-initial-items">@json($initialItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
<script type="application/json" id="work-order-initial-client-orders">@json($initialClientOrders, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('workOrderClientOrders', {
            orders: JSON.parse(document.getElementById('work-order-initial-client-orders').textContent || '[]'),
            add() {
                this.orders.push({
                    key: 'client-order-' + Math.random().toString(36).slice(2, 10),
                    number: '',
                    description: '',
                });
            },
            remove(index) {
                this.orders.splice(index, 1);
            },
        });
    });
</script>

<div class="space-y-5">
    <div data-wizard-panel data-wizard-title="Identificação" class="{{ $panelClass(0) }}">
    <div class="sm:col-span-2">
        <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
        <x-searchable-select
            class="mt-1"
            name="client_id"
            id="client_id"
            required
            :selected="old('client_id', $workOrder?->client_id)"
            placeholder="Busque pelo nome do cliente..."
            empty-message="Nenhum cliente encontrado."
            :options="$clients->map(fn ($client) => ['value' => $client->getKey(), 'label' => $client->name])->all()"
        />
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

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="client_os" class="block text-sm font-medium text-gray-700 dark:text-gray-300">OS Cliente Principal</label>
            <input
                type="text"
                name="client_os"
                id="client_os"
                maxlength="60"
                value="{{ old('client_os', $workOrder?->client_os) }}"
                placeholder="Número da OS principal do cliente (opcional)"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            >
            @error('client_os')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="oc" class="block text-sm font-medium text-gray-700 dark:text-gray-300">OC (ordem complexa)</label>
            <input
                type="text"
                name="oc"
                id="oc"
                maxlength="60"
                inputmode="numeric"
                pattern="[0-9]*"
                value="{{ old('oc', $workOrder?->oc) }}"
                placeholder="Somente números (opcional)"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            >
            @error('oc')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="chave" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CHAVE</label>
            <input
                type="text"
                name="chave"
                id="chave"
                maxlength="60"
                value="{{ old('chave', $workOrder?->chave) }}"
                placeholder="Chave (opcional)"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            >
            @error('chave')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="sm:col-span-2" x-data="{ binding: @js($binding) }">
        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vínculo</span>
        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Vincule a OS a um site (ERB) ou a um radio link. Você pode escolher apenas um.</p>

        <div class="mt-2 inline-flex flex-wrap rounded-xl border border-gray-200/70 p-1 dark:border-gray-800" role="group" aria-label="Tipo de vínculo">
            <button
                type="button"
                @click="binding = ''"
                :class="binding === '' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition"
            >
                <x-icon name="minus-circle" class="h-4 w-4" />
                Nenhum
            </button>
            <button
                type="button"
                @click="binding = 'erb'"
                :class="binding === 'erb' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition"
            >
                <x-icon name="signal" class="h-4 w-4" />
                Site (ERB)
            </button>
            <button
                type="button"
                @click="binding = 'radio'"
                :class="binding === 'radio' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition"
            >
                <x-icon name="arrows-right-left" class="h-4 w-4" />
                Radio link
            </button>
        </div>

        <template x-if="binding === 'erb'">
            <div class="mt-3">
                <label for="erb_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ERB (Estação Rádio Base)</label>
                <x-searchable-select
                    class="mt-1"
                    name="erb_id"
                    id="erb_id"
                    :selected="$selectedErb"
                    placeholder="Busque pelo código ou nome da ERB..."
                    empty-message="Nenhuma ERB encontrada."
                    :options="$erbs->map(fn ($erb) => ['value' => $erb->getKey(), 'label' => trim((($erb->site_id ?: $erb->code) ? ($erb->site_id ?: $erb->code).' — ' : '').$erb->name)])->all()"
                />
                @error('erb_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @if ($erbs->isEmpty())
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                        Você ainda não tem ERBs cadastradas.
                        <a href="{{ route('admin.erbs.create') }}" class="underline">Cadastre uma ERB</a>.
                    </p>
                @endif
            </div>
        </template>

        <template x-if="binding === 'radio'">
            <div class="mt-3">
                <label for="radio_link_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Radio link</label>
                <x-searchable-select
                    class="mt-1"
                    name="radio_link_id"
                    id="radio_link_id"
                    :selected="$selectedRadioLink"
                    placeholder="Busque pelo código do radio link..."
                    empty-message="Nenhum radio link encontrado."
                    :options="$radioLinks->map(fn ($link) => ['value' => $link->getKey(), 'label' => trim($link->code.($link->erbA || $link->erbB ? ' — '.($link->erbA?->code ?? '?').' a '.($link->erbB?->code ?? '?') : ''))])->all()"
                />
                @error('radio_link_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @if ($radioLinks->isEmpty())
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                        Você ainda não tem radio links cadastrados.
                        <a href="{{ route('admin.radio-links.index') }}" class="underline">Cadastre um radio link</a>.
                    </p>
                @endif
            </div>
        </template>
    </div>
    </div>

    <div data-wizard-panel data-wizard-title="OS do cliente" class="{{ $panelClass(1) }}">
    <div class="sm:col-span-2" x-data>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">OS do cliente</span>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Adicione as OS informadas pelo cliente e vincule cada item da OS a uma delas.</p>
            </div>

            <button
                type="button"
                @click="$store.workOrderClientOrders.add()"
                class="inline-flex items-center gap-1.5 rounded-xl border border-dashed border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-400"
            >
                <x-icon name="plus" class="h-3.5 w-3.5" />
                Adicionar OS do cliente
            </button>
        </div>

        @if (! empty($clientOrderErrors))
            <div class="mt-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc space-y-0.5 pl-4">
                    @foreach ($clientOrderErrors as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <template x-if="$store.workOrderClientOrders.orders.length === 0">
            <p class="mt-2 rounded-xl border border-dashed border-gray-200 bg-gray-50/50 px-3 py-4 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800/20 dark:text-gray-400">
                Nenhuma OS do cliente adicionada.
            </p>
        </template>

        <div class="mt-2 space-y-2">
            <template x-for="(order, index) in $store.workOrderClientOrders.orders" :key="order.key">
                <div class="flex flex-wrap items-start gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800 sm:flex-nowrap">
                    <div class="w-full sm:w-40">
                        <label class="sr-only" x-bind:for="'client-order-number-' + order.key">OS do cliente</label>
                        <input
                            type="text"
                            maxlength="60"
                            placeholder="Número da OS"
                            x-bind:id="'client-order-number-' + order.key"
                            x-bind:name="'client_orders[' + order.key + '][number]'"
                            x-model="order.number"
                            class="w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        >
                    </div>

                    <div class="min-w-0 flex-1">
                        <label class="sr-only" x-bind:for="'client-order-description-' + order.key">Descrição</label>
                        <input
                            type="text"
                            maxlength="2000"
                            placeholder="Descrição"
                            x-bind:id="'client-order-description-' + order.key"
                            x-bind:name="'client_orders[' + order.key + '][description]'"
                            x-model="order.description"
                            class="w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        >
                    </div>

                    <button
                        type="button"
                        @click="$store.workOrderClientOrders.remove(index)"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15 dark:hover:text-red-400"
                        aria-label="Remover OS do cliente"
                    >
                        <x-icon name="trash" class="h-4 w-4" />
                    </button>
                </div>
            </template>
        </div>
    </div>
    </div>

    <div data-wizard-panel data-wizard-title="Planejamento" class="{{ $panelClass(2) }}">
    <div>
        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridade</label>
        <select
            name="priority"
            id="priority"
            required
            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
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
            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
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
            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
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
            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
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
            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
        >{{ old('description', $workOrder?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
    </div>

    <div data-wizard-panel data-wizard-title="Itens" class="{{ $panelClass(3) }}">
    <div
        class="sm:col-span-2"
        x-data="{
            catalog: JSON.parse(document.getElementById('work-order-catalog-data').textContent),
            items: JSON.parse(document.getElementById('work-order-initial-items').textContent),
            search: '',
            openResults: false,
            get results() {
                const term = this.search.trim().toLowerCase();
                const matches = term === ''
                    ? this.catalog
                    : this.catalog.filter((entry) => entry.name.toLowerCase().includes(term));
                return matches.slice(0, 20);
            },
            entry(item) {
                return this.catalog.find((option) => String(option.id) === String(item.catalog_item_id)) ?? null;
            },
            name(item) {
                const found = this.entry(item);
                return found ? found.name : 'Selecione um item do catálogo';
            },
            unit(item) {
                const found = this.entry(item);
                return found ? (found.unit ?? '') : '';
            },
            price(item) {
                const found = this.entry(item);
                return found ? Number(found.price) : 0;
            },
            lineTotal(item) {
                return (Number(item.quantity) || 0) * this.price(item);
            },
            get total() {
                return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
            },
            get hasItems() {
                return this.items.length > 0;
            },
            addFromCatalog(option) {
                const existing = this.items.find((item) => String(item.catalog_item_id) === String(option.id));
                if (existing) {
                    existing.quantity = (Number(existing.quantity) || 0) + 1;
                } else {
                    this.items.push({ catalog_item_id: String(option.id), quantity: 1, status: 'pending', client_os: '' });
                }
                this.search = '';
                this.openResults = false;
            },
            removeItem(index) {
                this.items.splice(index, 1);
            },
            money(value) {
                return 'R$ ' + (Number(value) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        }"
    >
        <label for="work-order-item-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adicionar item do catálogo</label>

        <div class="relative" @click.away="openResults = false">
            <div class="relative mt-1">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="text"
                    id="work-order-item-search"
                    x-model="search"
                    @focus="openResults = true"
                    @click="openResults = true"
                    placeholder="Busque pelo nome do item..."
                    autocomplete="off"
                    class="block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 pl-9 pr-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                >
            </div>

            <div
                x-show="openResults"
                x-cloak
                class="mt-1 max-h-64 w-full overflow-auto rounded-xl border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-900 dark:ring-white/10"
            >
                <template x-for="option in results" :key="option.id">
                    <button
                        type="button"
                        @click="addFromCatalog(option)"
                        class="flex w-full items-center gap-3 px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <span class="min-w-0 flex-1 truncate text-sm text-gray-700 dark:text-gray-200" x-text="option.name"></span>
                        <span class="text-xs text-gray-400 dark:text-gray-500" x-text="option.unit ?? ''"></span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="money(option.price)"></span>
                        <x-icon name="plus" class="h-4 w-4 shrink-0 text-indigo-500" />
                    </button>
                </template>

                <p x-show="results.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                    @if ($catalogItems->isEmpty())
                        Você ainda não tem itens no catálogo.
                        <a href="{{ route('admin.catalog.index') }}" class="underline">Cadastre um item</a>.
                    @else
                        Nenhum item encontrado.
                    @endif
                </p>
            </div>
        </div>

        @if (! empty($itemErrors))
            <div class="mt-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc space-y-0.5 pl-4">
                    @foreach ($itemErrors as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-4">
            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Itens da OS</span>

            <template x-if="! hasItems">
                <p class="mt-2 rounded-xl border border-dashed border-gray-200 bg-gray-50/50 px-3 py-4 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800/20 dark:text-gray-400">
                    Nenhum item adicionado. Busque um item do catálogo acima.
                </p>
            </template>

            <div class="mt-2 space-y-2">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800 sm:flex-nowrap">
                        <input type="hidden" :name="'items[' + index + '][catalog_item_id]'" :value="item.catalog_item_id">

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" x-text="name(item)"></p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="unit(item)"></span>
                                <span x-show="unit(item) !== ''"> &middot; </span>
                                <span x-text="money(price(item))"></span>
                            </p>
                        </div>

                        <div>
                            <label class="sr-only" x-bind:for="'item-quantity-' + index">Quantidade</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                x-bind:id="'item-quantity-' + index"
                                x-bind:name="'items[' + index + '][quantity]'"
                                x-model.number="item.quantity"
                                class="w-24 rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            >
                        </div>

                        <div>
                            <label class="sr-only" x-bind:for="'item-status-' + index">Status do item</label>
                            <select
                                x-bind:id="'item-status-' + index"
                                x-bind:name="'items[' + index + '][status]'"
                                x-model="item.status"
                                class="w-36 rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            >
                                @foreach (\App\Enums\WorkOrderItemStatus::cases() as $lineStatus)
                                    <option value="{{ $lineStatus->value }}">{{ $lineStatus->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="sr-only" x-bind:for="'item-client-os-' + index">OS do cliente</label>
                            <input
                                type="text"
                                maxlength="60"
                                placeholder="OS do cliente"
                                x-bind:id="'item-client-os-' + index"
                                x-bind:name="'items[' + index + '][client_os]'"
                                x-model="item.client_os"
                                class="w-40 rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-2 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            >
                        </div>

                        <span class="w-24 text-right text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="money(lineTotal(item))"></span>

                        <button
                            type="button"
                            @click="removeItem(index)"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15 dark:hover:text-red-400"
                            aria-label="Remover item"
                        >
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
            <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="money(total)"></span>
        </div>
    </div>
    </div>

    <div data-wizard-panel data-wizard-title="Observações" class="{{ $panelClass(4) }}">
    <div class="sm:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
        <textarea
            name="notes"
            id="notes"
            rows="3"
            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
        >{{ old('notes', $workOrder?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
    </div>
</div>
