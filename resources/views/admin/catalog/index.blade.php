<x-layouts.admin title="Catálogo">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
        $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');
        $stock = function ($value): string {
            if ($value === null) {
                return '—';
            }

            return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
        };

        $itemsOnPage = $catalogItems->getCollection();
        $productsOnPage = $itemsOnPage->filter(fn ($item) => $item->type->value === 'product')->count();
        $servicesOnPage = $itemsOnPage->filter(fn ($item) => $item->type->value === 'service')->count();
        $activeOnPage = $itemsOnPage->filter(fn ($item) => $item->status->value === 'active')->count();
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Comercial
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="cube" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Catálogo
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre e organize os produtos e serviços da sua empresa.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                data-modal-open="catalog-item-modal"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Novo item
            </button>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="cube" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $catalogItems->total() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Itens no catálogo</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="squares-2x2" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $productsOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Produtos nesta página</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="wrench-screwdriver" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $servicesOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Serviços nesta página</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $activeOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ativos nesta página</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            method="GET"
            action="{{ route('admin.catalog.index') }}"
            class="flex flex-wrap items-center gap-2"
            data-search-form
        >
            @foreach (['view', 'sort', 'direction', 'per_page'] as $param)
                @if (request($param))
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach

            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por nome, código ou descrição..."
                    class="block w-full rounded-xl border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    data-search-input
                >
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="magnifying-glass" class="h-4 w-4" />
                Buscar
            </button>

            @if ($search !== '')
                <a
                    href="{{ route('admin.catalog.index', array_filter(['type' => $type, 'view' => $view])) }}"
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="type-filter" class="sr-only">Filtrar por tipo</label>
            <select
                id="type-filter"
                name="type"
                onchange="this.form.submit()"
                class="rounded-xl border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
            >
                @foreach (['' => 'Todos os tipos', 'product' => 'Produtos', 'service' => 'Serviços'] as $value => $label)
                    <option value="{{ $value }}" @selected($type === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $catalogItems->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $catalogItems->total() }}</span>
            itens
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.catalog.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-sm font-medium transition {{ $view === $mode ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($view === 'table')
        <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto lg:overflow-x-visible">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50/80 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <x-sort-header column="name" label="Item" icon="cube" class="px-5 py-3.5" />
                            <x-sort-header column="type" label="Tipo" class="px-5 py-3.5" />
                            <x-sort-header column="unit" label="Unidade" class="px-5 py-3.5" />
                            <x-sort-header column="price" label="Preço" class="px-5 py-3.5" />
                            <x-sort-header column="cost" label="Custo" class="px-5 py-3.5" />
                            <x-sort-header column="stock_quantity" label="Estoque" class="px-5 py-3.5" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3.5" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($catalogItems as $catalogItem)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $catalogItem->type->value === 'product' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' }}">
                                            <x-icon :name="$catalogItem->type->value === 'product' ? 'cube' : 'wrench-screwdriver'" class="h-4 w-4" />
                                        </span>
                                        <div class="min-w-0">
                                            <a
                                                href="{{ route('admin.catalog.show', $catalogItem) }}"
                                                class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                            >
                                                {{ $catalogItem->name }}
                                            </a>
                                            <p class="mt-0.5 max-w-[20rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $catalogItem->code ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-catalog-item-type :catalog-item="$catalogItem" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $catalogItem->unit ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-700 dark:text-gray-200">{{ $money((float) $catalogItem->price) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $money((float) $catalogItem->cost) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">
                                    {{ $catalogItem->type->value === 'product' ? $stock($catalogItem->stock_quantity) : '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-catalog-item-status :catalog-item="$catalogItem" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-relative-time :value="$catalogItem->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-catalog-item-actions-dropdown :catalog-item="$catalogItem" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-16 text-center" colspan="9">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <x-icon name="cube" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if ($search !== '' || $type !== null)
                                            Nenhum item encontrado
                                        @else
                                            Nenhum item cadastrado
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if ($search !== '' || $type !== null)
                                            Ajuste a busca ou os filtros para ver mais resultados.
                                        @else
                                            Cadastre o primeiro produto ou serviço para começar.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                <form method="GET" action="{{ route('admin.catalog.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction', 'type'] as $param)
                        @if (request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach
                    <label for="per-page" class="sr-only">Itens por página</label>
                    <select
                        id="per-page"
                        name="per_page"
                        onchange="this.form.submit()"
                        class="rounded-xl border border-gray-300 bg-white py-1.5 pl-2 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                    >
                        @foreach ([12, 24, 36] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                        @endforeach
                    </select>
                </form>

                <x-pagination :paginator="$catalogItems" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($catalogItems as $catalogItem)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $catalogItem->type->value === 'product' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' }}">
                                <x-icon :name="$catalogItem->type->value === 'product' ? 'cube' : 'wrench-screwdriver'" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.catalog.show', $catalogItem) }}" class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $catalogItem->name }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $catalogItem->code ?? $catalogItem->type->label() }}</p>
                            </div>
                        </div>

                        <x-catalog-item-actions-dropdown :catalog-item="$catalogItem" />
                    </div>

                    <p class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $money((float) $catalogItem->price) }}</p>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <x-catalog-item-type :catalog-item="$catalogItem" />
                        @if ($catalogItem->unit)
                            <span>por {{ $catalogItem->unit }}</span>
                        @endif
                        @if ($catalogItem->type->value === 'product')
                            <span>&middot; Estoque: {{ $stock($catalogItem->stock_quantity) }}</span>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <x-catalog-item-status :catalog-item="$catalogItem" />
                        <x-relative-time :value="$catalogItem->updated_at" />
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-gray-200/70 bg-white px-5 py-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="cube" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($search !== '' || $type !== null)
                            Nenhum item encontrado
                        @else
                            Nenhum item cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($search !== '' || $type !== null)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Cadastre o primeiro produto ou serviço para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.catalog.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'type'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">Itens por página</label>
                <select
                    id="per-page"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-gray-300 bg-white py-1.5 pl-2 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$catalogItems" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($catalogItems as $catalogItem)
                <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $catalogItem->type->value === 'product' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' }}">
                        <x-icon :name="$catalogItem->type->value === 'product' ? 'cube' : 'wrench-screwdriver'" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.catalog.show', $catalogItem) }}" class="truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $catalogItem->name }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $catalogItem->code ?? $catalogItem->description ?? $catalogItem->type->label() }}
                        </p>
                    </div>

                    <div class="hidden sm:block">
                        <x-catalog-item-type :catalog-item="$catalogItem" />
                    </div>

                    <span class="hidden whitespace-nowrap text-sm font-semibold text-gray-700 md:inline dark:text-gray-200">
                        {{ $money((float) $catalogItem->price) }}
                    </span>

                    <div class="hidden sm:block">
                        <x-catalog-item-status :catalog-item="$catalogItem" />
                    </div>

                    <x-catalog-item-actions-dropdown :catalog-item="$catalogItem" />
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="cube" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($search !== '' || $type !== null)
                            Nenhum item encontrado
                        @else
                            Nenhum item cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($search !== '' || $type !== null)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Cadastre o primeiro produto ou serviço para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.catalog.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'type'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">Itens por página</label>
                <select
                    id="per-page"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-gray-300 bg-white py-1.5 pl-2 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$catalogItems" />
        </div>
    @endif

    @foreach ($catalogItems as $catalogItem)
        @include('admin.catalog.partials.edit-modal', ['catalogItem' => $catalogItem])
    @endforeach

    <div
        id="catalog-item-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="catalog-item-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="catalog-item-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="cube" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Novo item
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre um produto ou serviço no catálogo.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            @if ($errors->any() && old('modal') === 'create')
                <div
                    data-modal-error
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">Não foi possível criar o item. Corrija os campos destacados e tente novamente.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.catalog.store') }}" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="create">

                <div class="mb-5">
                    <div class="flex items-center justify-between text-xs font-medium text-gray-500 dark:text-gray-400">
                        <span data-wizard-label>Identificação</span>
                        <span data-wizard-counter>Passo 1 de 3</span>
                    </div>
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                        <div data-wizard-progress class="h-full w-1/3 rounded-full bg-indigo-500 transition-all duration-200"></div>
                    </div>
                </div>

                @include('admin.catalog.partials.form', ['catalogItem' => null, 'types' => \App\Enums\CatalogItemType::cases(), 'wizard' => true])

                <div class="mt-6 flex items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        data-wizard-prev
                        class="hidden rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    >
                        Voltar
                    </button>

                    <div class="ml-auto flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                            data-modal-close
                        >
                            Cancelar
                        </button>

                        <button
                            type="button"
                            data-wizard-next
                            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        >
                            Próximo
                        </button>

                        <button
                            type="submit"
                            data-wizard-submit
                            class="hidden items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        >
                            <x-icon name="check" class="h-4 w-4" />
                            Criar item
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('catalog-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('catalog-view', link.dataset.viewToggle);
            });
        });

        const closeDropdowns = (except) => {
            document.querySelectorAll('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
                if (menu !== except) {
                    menu.classList.add('hidden');
                    menu.previousElementSibling.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-dropdown-toggle]');

            if (toggle) {
                const menu = toggle.nextElementSibling;
                const willOpen = menu.classList.contains('hidden');
                closeDropdowns(willOpen ? menu : null);
                menu.classList.toggle('hidden', !willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));
                return;
            }

            if (!event.target.closest('[data-dropdown-menu]')) {
                closeDropdowns();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdowns();
            }
        });

        const searchForm = document.querySelector('[data-search-form]');
        const searchInput = document.querySelector('[data-search-input]');

        if (searchForm && searchInput) {
            let searchTimer;

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => searchForm.submit(), 400);
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    clearTimeout(searchTimer);
                }
            });

            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        }

    </script>

    @include('admin.catalog.partials.modal-script')
</x-layouts.admin>
