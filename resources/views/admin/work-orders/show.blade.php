<x-layouts.admin :title="$workOrder->number">
    @php
        $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');
        $quantity = fn ($value): string => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');

        $rawEditorItems = old('items');

        $itemStatus = fn ($value): string => \App\Enums\WorkOrderItemStatus::tryFrom((string) $value)?->value ?? \App\Enums\WorkOrderItemStatus::Pending->value;

        if (! is_array($rawEditorItems)) {
            $rawEditorItems = $workOrder->items->map(fn ($item) => [
                'catalog_item_id' => (string) ($item->catalog_item_id ?? ''),
                'quantity' => (float) $item->quantity,
                'status' => $item->status->value,
                'client_os' => (string) ($item->client_os ?? ''),
            ])->all();
        }

        $editorItems = array_values(array_map(fn ($item) => [
            'catalog_item_id' => (string) ($item['catalog_item_id'] ?? ''),
            'quantity' => $item['quantity'] ?? 1,
            'status' => $itemStatus($item['status'] ?? null),
            'client_os' => (string) ($item['client_os'] ?? ''),
        ], $rawEditorItems));

        $itemsErrors = collect($errors->keys())
            ->filter(fn ($key) => $key === 'items' || str_starts_with($key, 'items.'))
            ->flatMap(fn ($key) => $errors->get($key))
            ->values()
            ->all();

        $isClosed = $workOrder->status->isClosed();
        $dueDays = ($workOrder->due_at !== null && ! $isClosed)
            ? (int) now()->startOfDay()->diffInDays($workOrder->due_at->copy()->startOfDay(), false)
            : null;

        $itemsTotal = $workOrder->items->count();
        $itemsCompleted = $workOrder->items->filter(fn ($item) => $item->status->isCompleted())->count();
        $itemsProgress = $itemsTotal > 0 ? (int) round(($itemsCompleted / $itemsTotal) * 100) : 0;
        $itemsProgressLabel = $itemsCompleted.' de '.$itemsTotal.' '.($itemsTotal === 1 ? 'item concluído' : 'itens concluídos');
    @endphp

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 p-6 text-white shadow-xl shadow-indigo-600/10 sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-violet-400/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <a
                    href="{{ route('admin.work-orders.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-100/90 transition hover:text-white"
                >
                    <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    Ordens de serviço
                </a>

                <h1 class="mt-2 flex items-center gap-2.5 text-2xl font-semibold tracking-tight sm:text-3xl">
                    <x-icon name="clipboard-document-list" class="h-6 w-6 text-white/80" />
                    {{ $workOrder->number }}
                </h1>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <x-work-order-status :work-order="$workOrder" />
                    <x-work-order-priority :work-order="$workOrder" />

                    @if ($dueDays !== null)
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $dueDays < 0 ? 'bg-red-500/20 text-red-50' : 'bg-white/15 text-white/90' }}">
                            <x-icon name="clock" class="h-3.5 w-3.5" />
                            @if ($dueDays < 0)
                                Atrasada há {{ abs($dueDays) }} {{ abs($dueDays) === 1 ? 'dia' : 'dias' }}
                            @elseif ($dueDays === 0)
                                Entrega hoje
                            @else
                                Faltam {{ $dueDays }} {{ $dueDays === 1 ? 'dia' : 'dias' }}
                            @endif
                        </span>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('admin.clients.show', $workOrder->client) }}"
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur transition hover:bg-white/20"
                    >
                        <x-icon name="building-office" class="h-3.5 w-3.5" />
                        {{ $workOrder->client->name }}
                    </a>
                    @if ($workOrder->erb)
                        @php
                            $siteId = $workOrder->erb->site_id ?: $workOrder->erb->code;
                        @endphp
                        <button
                            type="button"
                            data-copy="{{ $siteId }}"
                            data-copy-message="Site ID {{ $siteId }} copiado"
                            title="Copiar Site ID"
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur transition hover:bg-white/20"
                        >
                            <x-icon name="signal" class="h-3.5 w-3.5" />
                            {{ $siteId }}
                        </button>
                        <a
                            href="{{ route('admin.erbs.show', $workOrder->erb) }}"
                            title="Abrir a ERB"
                            aria-label="Abrir a ERB"
                            class="inline-flex h-6 items-center justify-center rounded-full bg-white/10 px-2 text-white/90 backdrop-blur transition hover:bg-white/20"
                        >
                            <x-icon name="arrow-top-right-on-square" class="h-3.5 w-3.5" />
                        </a>
                    @endif
                    @if ($workOrder->radioLink)
                        <a
                            href="{{ route('admin.radio-links.show', $workOrder->radioLink) }}"
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur transition hover:bg-white/20"
                        >
                            <x-icon name="arrows-right-left" class="h-3.5 w-3.5" />
                            {{ $workOrder->radioLink->code }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('update', $workOrder)
                    <button
                        type="button"
                        data-modal-open="work-order-edit-modal"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50"
                    >
                        <x-icon name="pencil-square" class="h-4 w-4" />
                        Editar
                    </button>
                @endcan
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="chart-bar" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $money((float) $workOrder->total) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Valor total</p>
        </div>

        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="cube" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $workOrder->items->count() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $workOrder->items->count() === 1 ? 'Item na OS' : 'Itens na OS' }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="calendar-days" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $workOrder->opened_at->format('d/m/Y') }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Abertura</p>
        </div>

        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <x-icon name="clock" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $workOrder->due_at?->format('d/m/Y') ?? '—' }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Previsão de entrega</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @can('update', $workOrder)
                <script type="application/json" id="work-order-items-editor-data">@json(['catalog' => $catalogData, 'items' => $editorItems], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>

                <section
                    class="rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    x-data="{
                        catalog: [],
                        items: [],
                        initial: [],
                        search: '',
                        openResults: false,
                        editing: @js(! empty($itemsErrors)),
                        init() {
                            const data = JSON.parse(document.getElementById('work-order-items-editor-data').textContent);
                            this.catalog = data.catalog;
                            this.items = data.items;
                            this.initial = JSON.parse(JSON.stringify(data.items));
                        },
                        startEditing() {
                            this.items = JSON.parse(JSON.stringify(this.initial));
                            this.search = '';
                            this.openResults = false;
                            this.editing = true;
                        },
                        cancelEditing() {
                            this.editing = false;
                            this.search = '';
                            this.openResults = false;
                            this.items = JSON.parse(JSON.stringify(this.initial));
                        },
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
                            return found ? found.name : 'Escolha um item do catálogo';
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
                        addFromCatalog(option) {
                            const existing = this.items.find((item) => String(item.catalog_item_id) === String(option.id));
                            if (existing) {
                                existing.quantity = (Number(existing.quantity) || 0) + 1;
                            } else {
                                this.items.push({ catalog_item_id: String(option.id), quantity: 1, status: 'pending' });
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
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="cube" class="h-4 w-4" />
                            </span>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Itens da OS</h2>
                        </div>
                        <button
                            type="button"
                            x-show="! editing"
                            @click="startEditing()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200/70 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-indigo-500/40"
                        >
                            <x-icon name="pencil-square" class="h-4 w-4" />
                            Editar itens
                        </button>
                        <p x-show="editing" x-cloak class="text-xs text-gray-400 dark:text-gray-500">Adicione itens do catálogo, ajuste quantidades e informe o status de cada um.</p>
                    </div>

                    <div x-show="! editing" class="border-b border-gray-100 px-6 py-3 dark:border-gray-800">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Progresso dos itens</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $itemsProgressLabel }}
                                <span class="ml-1 font-medium text-emerald-600 dark:text-emerald-400">{{ $itemsProgress }}%</span>
                            </p>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $itemsProgress }}%"></div>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.work-orders.items.update', $workOrder) }}"
                        x-show="editing"
                        x-cloak
                    >
                        @csrf
                        @method('PUT')

                        <div class="space-y-4 p-6">
                            @if (! empty($itemsErrors))
                                <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                                    <ul class="list-disc space-y-0.5 pl-4">
                                        @foreach ($itemsErrors as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div>
                                <label for="work-order-items-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adicionar item do catálogo</label>

                                <div class="relative" @click.away="openResults = false">
                                    <div class="relative mt-1">
                                        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                                        <input
                                            type="text"
                                            id="work-order-items-search"
                                            x-model="search"
                                            @focus="openResults = true"
                                            @click="openResults = true"
                                            placeholder="Busque pelo nome do item..."
                                            autocomplete="off"
                                            class="block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 pl-9 pr-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                        >
                                    </div>

                                    <div x-show="openResults" x-cloak class="mt-1 max-h-64 w-full overflow-auto rounded-xl border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-900 dark:ring-white/10">
                                        <template x-for="option in results" :key="option.id">
                                            <button type="button" @click="addFromCatalog(option)" class="flex w-full items-center gap-3 px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800">
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
                            </div>

                            <div class="space-y-2">
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
                                            class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15 dark:hover:text-red-400"
                                            aria-label="Remover item"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </template>

                                <template x-if="items.length === 0">
                                    <p class="rounded-xl border border-dashed border-gray-200 px-3 py-4 text-center text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        Nenhum item adicionado. Busque um item do catálogo acima.
                                    </p>
                                </template>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="money(total)"></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="cancelEditing()"
                                    class="rounded-xl border border-gray-200/70 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-200 dark:hover:border-indigo-500/40"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                                >
                                    <x-icon name="check" class="h-4 w-4" />
                                    Salvar itens
                                </button>
                            </div>
                        </div>
                    </form>

                    <div x-show="! editing" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 font-medium">Descrição</th>
                                    <th class="px-6 py-3 font-medium">Status</th>
                                    <th class="px-6 py-3 text-right font-medium">Quantidade</th>
                                    <th class="px-6 py-3 text-right font-medium">Valor unitário</th>
                                    <th class="px-6 py-3 text-right font-medium">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($workOrder->items as $item)
                                    <tr>
                                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">
                                            <button
                                                type="button"
                                                data-modal-open="work-order-item-modal-{{ $item->getKey() }}"
                                                class="block w-full text-left transition hover:text-indigo-600 dark:hover:text-indigo-400"
                                            >
                                                {{ $item->description }}
                                                @if ($item->catalogItem)
                                                    <span class="mt-0.5 block text-xs text-gray-400 dark:text-gray-500">{{ $item->catalogItem->name }}</span>
                                                @endif
                                                @if ($item->client_os)
                                                    <span class="mt-0.5 block text-xs text-indigo-600 dark:text-indigo-400">OS cliente: {{ $item->client_os }}</span>
                                                @endif
                                            </button>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3">
                                            <x-work-order-item-status :item="$item" />
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right text-gray-500 dark:text-gray-400">{{ $quantity($item->quantity) }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right text-gray-500 dark:text-gray-400">{{ $money((float) $item->unit_price) }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right font-medium text-gray-900 dark:text-gray-100">{{ $money((float) $item->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-6 py-6 text-gray-500 dark:text-gray-400" colspan="5">Nenhum item registrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-200" colspan="4">Total</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $workOrder->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            @else
                <section class="rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon name="cube" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Itens da OS</h2>
                    </div>

                    <div class="border-b border-gray-100 px-6 py-3 dark:border-gray-800">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Progresso dos itens</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $itemsProgressLabel }}
                                <span class="ml-1 font-medium text-emerald-600 dark:text-emerald-400">{{ $itemsProgress }}%</span>
                            </p>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $itemsProgress }}%"></div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 font-medium">Descrição</th>
                                    <th class="px-6 py-3 font-medium">Status</th>
                                    <th class="px-6 py-3 text-right font-medium">Quantidade</th>
                                    <th class="px-6 py-3 text-right font-medium">Valor unitário</th>
                                    <th class="px-6 py-3 text-right font-medium">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($workOrder->items as $item)
                                    <tr>
                                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">
                                            <button
                                                type="button"
                                                data-modal-open="work-order-item-modal-{{ $item->getKey() }}"
                                                class="block w-full text-left transition hover:text-indigo-600 dark:hover:text-indigo-400"
                                            >
                                                {{ $item->description }}
                                                @if ($item->catalogItem)
                                                    <span class="mt-0.5 block text-xs text-gray-400 dark:text-gray-500">{{ $item->catalogItem->name }}</span>
                                                @endif
                                                @if ($item->client_os)
                                                    <span class="mt-0.5 block text-xs text-indigo-600 dark:text-indigo-400">OS cliente: {{ $item->client_os }}</span>
                                                @endif
                                            </button>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3">
                                            <x-work-order-item-status :item="$item" />
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right text-gray-500 dark:text-gray-400">{{ $quantity($item->quantity) }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right text-gray-500 dark:text-gray-400">{{ $money((float) $item->unit_price) }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right font-medium text-gray-900 dark:text-gray-100">{{ $money((float) $item->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-6 py-6 text-gray-500 dark:text-gray-400" colspan="5">Nenhum item registrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-200" colspan="4">Total</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $workOrder->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            @endcan

            @can('update', $workOrder)
                <section
                    class="rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    x-data="{ editing: @js($errors->has('description')) }"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-500/10 dark:text-slate-300">
                                <x-icon name="document-text" class="h-4 w-4" />
                            </span>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Descrição</h2>
                        </div>
                        <button
                            type="button"
                            x-show="! editing"
                            @click="editing = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200/70 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-indigo-500/40"
                        >
                            <x-icon name="pencil-square" class="h-4 w-4" />
                            Editar
                        </button>
                    </div>

                    <div x-show="! editing" class="p-6">
                        <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                            {{ $workOrder->description ?? 'Nenhuma descrição registrada.' }}
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.work-orders.description.update', $workOrder) }}"
                        x-show="editing"
                        x-cloak
                        class="p-6"
                    >
                        @csrf
                        @method('PUT')

                        <label for="work-order-description" class="sr-only">Descrição</label>
                        <textarea
                            name="description"
                            id="work-order-description"
                            rows="4"
                            class="block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        >{{ old('description', $workOrder->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                @click="editing = false"
                                class="rounded-xl border border-gray-200/70 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-200 dark:hover:border-indigo-500/40"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                            >
                                <x-icon name="check" class="h-4 w-4" />
                                Salvar
                            </button>
                        </div>
                    </form>
                </section>
            @else
                <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-500/10 dark:text-slate-300">
                            <x-icon name="document-text" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Descrição</h2>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                        {{ $workOrder->description ?? 'Nenhuma descrição registrada.' }}
                    </p>
                </section>
            @endcan

            @can('update', $workOrder)
                <section
                    class="rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    x-data="{ editing: @js($errors->has('notes')) }"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                <x-icon name="chat-bubble-oval-left-ellipsis" class="h-4 w-4" />
                            </span>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observações</h2>
                        </div>
                        <button
                            type="button"
                            x-show="! editing"
                            @click="editing = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200/70 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-indigo-500/40"
                        >
                            <x-icon name="pencil-square" class="h-4 w-4" />
                            Editar
                        </button>
                    </div>

                    <div x-show="! editing" class="p-6">
                        <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                            {{ $workOrder->notes ?? 'Nenhuma observação registrada.' }}
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.work-orders.notes.update', $workOrder) }}"
                        x-show="editing"
                        x-cloak
                        class="p-6"
                    >
                        @csrf
                        @method('PUT')

                        <label for="work-order-notes" class="sr-only">Observações</label>
                        <textarea
                            name="notes"
                            id="work-order-notes"
                            rows="4"
                            class="block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        >{{ old('notes', $workOrder->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                @click="editing = false"
                                class="rounded-xl border border-gray-200/70 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-200 dark:hover:border-indigo-500/40"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                            >
                                <x-icon name="check" class="h-4 w-4" />
                                Salvar
                            </button>
                        </div>
                    </form>
                </section>
            @else
                <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            <x-icon name="chat-bubble-oval-left-ellipsis" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observações</h2>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                        {{ $workOrder->notes ?? 'Nenhuma observação registrada.' }}
                    </p>
                </section>
            @endcan

            @php
                $workOrderDocuments = $workOrder->items
                    ->flatMap(fn ($item) => $item->documents->map(fn ($document) => ['item' => $item, 'document' => $document]))
                    ->concat($workOrder->documents->map(fn ($document) => ['item' => null, 'document' => $document]))
                    ->sortByDesc(fn (array $row) => $row['document']->created_at)
                    ->values();
            @endphp

            <section class="rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            <x-icon name="paper-clip" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Anexos</h2>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ $workOrderDocuments->count() }}
                        </span>
                    </div>

                    @can('update', $workOrder)
                        <button
                            type="button"
                            data-modal-open="work-order-attach-modal"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                        >
                            <x-icon name="arrow-up-tray" class="h-4 w-4" />
                            Anexar
                        </button>
                    @endcan
                </div>

                @if ($workOrderDocuments->isEmpty())
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum anexo cadastrado.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($workOrderDocuments as $row)
                            @php
                                $document = $row['document'];
                                $documentItem = $row['item'];
                            @endphp
                            <li class="flex items-center gap-3 px-6 py-3.5 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <x-file-type-icon :name="$document->original_name" size="xs" />

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $document->original_name }}">
                                        {{ $document->original_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        @if ($documentItem !== null)
                                            Item: {{ \Illuminate\Support\Str::limit($documentItem->description, 40) }}
                                        @else
                                            Ordem de serviço
                                        @endif
                                        ·
                                        {{ Number::fileSize($document->size) }}
                                        ·
                                        <x-relative-time :value="$document->created_at" />
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <a
                                        href="{{ route('admin.files.preview', $document) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                        aria-label="Visualizar anexo"
                                    >
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>

                                    <a
                                        href="{{ route('admin.files.download', $document) }}"
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                        aria-label="Baixar anexo"
                                    >
                                        <x-icon name="arrow-down-tray" class="h-4 w-4" />
                                    </a>

                                    @if ($documentItem === null)
                                        @can('update', $workOrder)
                                            <form method="POST" action="{{ route('admin.work-orders.documents.destroy', [$workOrder, $document]) }}" data-confirm-delete="Tem certeza que deseja remover este anexo?">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-950 dark:hover:text-red-400"
                                                    aria-label="Remover anexo"
                                                >
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <div class="space-y-6">
            @if ($workOrder->client_os || $workOrder->oc || $workOrder->clientOrders->isNotEmpty())
                <section class="rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                            <x-icon name="clipboard-document" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">OS do cliente</h2>
                    </div>

                    <div class="space-y-4 p-6 text-sm">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">OS Cliente Principal</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->client_os ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">OC (ordem complexa)</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->oc ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">CHAVE</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->chave ?: '—' }}</p>
                            </div>
                        </div>

                        @if ($workOrder->clientOrders->isNotEmpty())
                            <ul class="space-y-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                                @foreach ($workOrder->clientOrders as $clientOrder)
                                    <li class="rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-800">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $clientOrder->number }}</p>
                                        @if ($clientOrder->description)
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $clientOrder->description }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>
            @endif

            @can('update', $workOrder)
                <section
                    class="rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    x-data="{ editing: @js($errors->has('opened_at') || $errors->has('due_at')) }"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                                <x-icon name="calendar-days" class="h-4 w-4" />
                            </span>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Datas</h2>
                        </div>
                        <button
                            type="button"
                            x-show="! editing"
                            @click="editing = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200/70 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-indigo-500/40"
                        >
                            <x-icon name="pencil-square" class="h-4 w-4" />
                            Editar
                        </button>
                    </div>

                    <dl x-show="! editing" class="space-y-3 p-6 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Abertura</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->opened_at->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Previsão de entrega</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->due_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Conclusão</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->completed_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>

                    <form
                        method="POST"
                        action="{{ route('admin.work-orders.dates.update', $workOrder) }}"
                        x-show="editing"
                        x-cloak
                        class="p-6"
                    >
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <label for="work-order-opened-at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Abertura</label>
                                <input
                                    type="date"
                                    name="opened_at"
                                    id="work-order-opened-at"
                                    required
                                    value="{{ old('opened_at', $workOrder->opened_at?->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                >
                                @error('opened_at')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="work-order-due-at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Previsão de entrega</label>
                                <input
                                    type="date"
                                    name="due_at"
                                    id="work-order-due-at"
                                    value="{{ old('due_at', $workOrder->due_at?->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                >
                                @error('due_at')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <button
                                type="button"
                                @click="editing = false"
                                class="rounded-xl border border-gray-200/70 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-200 dark:hover:border-indigo-500/40"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                            >
                                <x-icon name="check" class="h-4 w-4" />
                                Salvar
                            </button>
                        </div>
                    </form>
                </section>
            @else
                <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                            <x-icon name="calendar-days" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Datas</h2>
                    </div>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Abertura</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->opened_at->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Previsão de entrega</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->due_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Conclusão</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->completed_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>
            @endcan

            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                            <x-icon name="briefcase" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Projeto</h2>
                    </div>

                    @if ($workOrder->project === null)
                        <form method="POST" action="{{ route('admin.work-orders.project.store', $workOrder) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                            >
                                <x-icon name="plus" class="h-4 w-4" />
                                Criar projeto
                            </button>
                        </form>
                    @endif
                </div>

                @if ($workOrder->project !== null)
                    <a
                        href="{{ route('admin.projects.show', $workOrder->project) }}"
                        class="mt-4 flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:border-violet-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-violet-500/40 dark:hover:bg-gray-800/50"
                    >
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                            <x-icon name="briefcase" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->project->name }}</span>
                            <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $workOrder->project->status->label() }}</span>
                        </span>
                        <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                    </a>
                @else
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Nenhum projeto vinculado. Crie um projeto a partir desta OS para iniciar o planejamento.
                    </p>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="clock" class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Histórico</h2>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Criado em</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Atualizado em</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>

    @include('admin.work-orders.partials.comments', ['workOrder' => $workOrder])

    @foreach ($workOrder->items as $item)
        @include('admin.work-orders.partials.item-details-modal', ['workOrder' => $workOrder, 'item' => $item, 'money' => $money, 'quantity' => $quantity])
    @endforeach

    @can('update', $workOrder)
        @include('admin.work-orders.partials.edit-modal', ['workOrder' => $workOrder])
        @include('admin.work-orders.partials.attach-modal', ['workOrder' => $workOrder])
    @endcan

    <script>
        const openModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeModal = (modal) => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-modal-open]');

            if (opener) {
                event.preventDefault();

                const modal = document.getElementById(opener.dataset.modalOpen);
                if (modal) {
                    openModal(modal);
                }

                return;
            }

            const closer = event.target.closest('[data-modal-close]');
            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
            }
        });

        document.querySelectorAll('[role="dialog"][data-open]').forEach(openModal);

        const wizard = document.querySelector('[data-wizard]');

        if (wizard) {
            const panels = Array.from(wizard.querySelectorAll('[data-wizard-panel]'));
            const stepsList = wizard.querySelector('[data-wizard-steps]');
            const prevButton = wizard.querySelector('[data-wizard-prev]');
            const nextButton = wizard.querySelector('[data-wizard-next]');
            const submitButton = wizard.querySelector('[data-wizard-submit]');
            const scroller = wizard.closest('.overflow-y-auto');
            const baseIndicator = 'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition';
            const inactiveIndicator = baseIndicator + ' border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800';
            const activeIndicator = baseIndicator + ' border-indigo-600 bg-indigo-600 text-white shadow-sm';
            const doneIndicator = baseIndicator + ' border-emerald-500 text-emerald-600 dark:text-emerald-400';
            const baseBadge = 'inline-flex h-5 w-5 items-center justify-center rounded-full text-[0.65rem] font-semibold';
            const inactiveBadge = baseBadge + ' bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200';
            const activeBadge = baseBadge + ' bg-white/25 text-white';
            const doneBadge = baseBadge + ' bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300';
            let current = 0;

            if (stepsList) {
                stepsList.innerHTML = panels.map((panel, index) => `
                    <li>
                        <button type="button" data-wizard-indicator="${index}" class="${inactiveIndicator}">
                            <span data-wizard-badge class="${inactiveBadge}">${index + 1}</span>
                            <span>${panel.dataset.wizardTitle ?? ''}</span>
                        </button>
                    </li>
                `).join('');
            }

            const indicators = stepsList ? Array.from(stepsList.querySelectorAll('[data-wizard-indicator]')) : [];

            const firstInvalid = (panel) => Array.from(panel.querySelectorAll('input, select, textarea'))
                .find((field) => !field.checkValidity());

            const render = () => {
                panels.forEach((panel, index) => panel.classList.toggle('hidden', index !== current));

                indicators.forEach((indicator, index) => {
                    indicator.className = index === current
                        ? activeIndicator
                        : (index < current ? doneIndicator : inactiveIndicator);

                    const badge = indicator.querySelector('[data-wizard-badge]');

                    if (badge) {
                        badge.className = index === current
                            ? activeBadge
                            : (index < current ? doneBadge : inactiveBadge);
                    }
                });

                prevButton.classList.toggle('hidden', current === 0);
                nextButton.classList.toggle('hidden', current === panels.length - 1);
                submitButton.classList.toggle('hidden', current !== panels.length - 1);
                submitButton.classList.toggle('inline-flex', current === panels.length - 1);
            };

            const goTo = (index) => {
                current = Math.max(0, Math.min(index, panels.length - 1));
                render();

                if (scroller) {
                    scroller.scrollTop = 0;
                }
            };

            nextButton.addEventListener('click', () => {
                const invalid = firstInvalid(panels[current]);

                if (invalid) {
                    invalid.reportValidity();
                    return;
                }

                goTo(current + 1);
            });

            prevButton.addEventListener('click', () => goTo(current - 1));

            indicators.forEach((indicator) => {
                indicator.addEventListener('click', () => goTo(Number(indicator.dataset.wizardIndicator)));
            });

            wizard.addEventListener('submit', (event) => {
                for (let index = 0; index < panels.length; index++) {
                    const invalid = firstInvalid(panels[index]);

                    if (invalid) {
                        event.preventDefault();
                        goTo(index);
                        invalid.reportValidity();
                        return;
                    }
                }
            });

            document.querySelectorAll('[data-modal-open="work-order-edit-modal"]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            const errorPanel = panels.findIndex((panel) => panel.querySelector('.text-red-600'));

            goTo(errorPanel > -1 ? errorPanel : 0);
        }
    </script>
</x-layouts.admin>
