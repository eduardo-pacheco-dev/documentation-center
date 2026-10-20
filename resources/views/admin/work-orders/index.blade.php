<x-layouts.admin title="Ordens de serviço">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
        $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');

        $hasFilters = $search !== '' || $status !== null;

        $secondaryButton = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
        $selectClass = 'rounded-xl border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Operações</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="clipboard-document-list" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Ordens de serviço
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Abra e acompanhe as ordens de serviço dos seus clientes.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <button
                    type="button"
                    class="{{ $secondaryButton }}"
                    aria-haspopup="true"
                    aria-expanded="false"
                    data-dropdown-toggle
                >
                    <x-icon name="arrow-down-tray" class="h-4 w-4" />
                    Exportar
                    <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                </button>

                <div
                    data-dropdown-menu
                    class="absolute right-0 z-10 mt-1 hidden w-56 origin-top-right rounded-xl bg-white py-1 text-left shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
                    role="menu"
                >
                    <a
                        href="{{ route('admin.work-orders.export', array_filter(['search' => $search, 'status' => $status, 'sort' => $sort, 'direction' => $direction])) }}"
                        data-export-link
                        data-export-filename="ordens-de-servico.xlsx"
                        class="flex items-start gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                        role="menuitem"
                    >
                        <x-icon name="document-text" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <span>
                            <span class="block font-medium">Simplificado</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Uma linha por OS, como na listagem.</span>
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.work-orders.export', array_filter(['search' => $search, 'status' => $status, 'sort' => $sort, 'direction' => $direction, 'mode' => 'completo'])) }}"
                        data-export-link
                        data-export-filename="ordens-de-servico-completo.xlsx"
                        class="flex items-start gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                        role="menuitem"
                    >
                        <x-icon name="rectangle-stack" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <span>
                            <span class="block font-medium">Completo</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Todos os dados da OS e uma linha por item.</span>
                        </span>
                    </a>
                </div>
            </div>

            <div class="relative">
                <button
                    type="button"
                    class="{{ $secondaryButton }}"
                    aria-haspopup="true"
                    aria-expanded="false"
                    data-dropdown-toggle
                >
                    <x-icon name="arrow-up-tray" class="h-4 w-4" />
                    Importar
                    <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                </button>

                <div
                    data-dropdown-menu
                    class="absolute right-0 z-10 mt-1 hidden w-56 origin-top-right rounded-xl bg-white py-1 text-left shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
                    role="menu"
                >
                    <button
                        type="button"
                        data-modal-open="work-order-import-modal"
                        class="flex w-full items-start gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                        role="menuitem"
                    >
                        <x-icon name="document-text" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <span>
                            <span class="block font-medium">Simplificado</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Uma OS por linha.</span>
                        </span>
                    </button>

                    <button
                        type="button"
                        data-modal-open="work-order-import-complete-modal"
                        class="flex w-full items-start gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                        role="menuitem"
                    >
                        <x-icon name="rectangle-stack" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <span>
                            <span class="block font-medium">Completo</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Uma OS com seus itens.</span>
                        </span>
                    </button>
                </div>
            </div>

            <button
                type="button"
                data-modal-open="work-order-modal"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Nova OS
            </button>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="clipboard-document-list" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Total de ordens</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-lg hover:shadow-sky-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-sky-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <x-icon name="clipboard-document" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['open'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em aberto</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="arrow-path" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['inProgress'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em andamento</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="clock" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['onHold'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em espera</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Concluídas</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            method="GET"
            action="{{ route('admin.work-orders.index') }}"
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
                    placeholder="Buscar por número, cliente, Site ID, OC ou CHAVE..."
                    class="block w-full rounded-xl border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    data-search-input
                >
            </div>

            <button type="submit" class="{{ $secondaryButton }}">
                <x-icon name="magnifying-glass" class="h-4 w-4" />
                Buscar
            </button>

            @if ($hasFilters)
                <a
                    href="{{ route('admin.work-orders.index', array_filter(['view' => $view])) }}"
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select id="status-filter" name="status" onchange="this.form.submit()" class="{{ $selectClass }}">
                <option value="" @selected($status === null)>Todos os status</option>
                @foreach (\App\Enums\WorkOrderStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrders->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrders->total() }}</span>
            ordens de serviço
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.work-orders.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50/80 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <x-sort-header column="number" label="Número" icon="clipboard-document-list" class="w-32 px-5 py-3.5" />
                            <th class="px-5 py-3.5 font-medium">Site ID</th>
                            <x-sort-header column="priority" label="Prioridade" class="px-5 py-3.5" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3.5" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="w-40 px-5 py-3.5" />
                            <th class="w-16 px-5 py-3.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($workOrders as $workOrder)
                            <tr class="align-middle transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <a
                                        href="{{ route('admin.work-orders.show', $workOrder) }}"
                                        class="font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        {{ $workOrder->number }}
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="flex min-w-0 items-center gap-1.5 text-gray-500 dark:text-gray-400">
                                        @if ($workOrder->erb)
                                            <x-icon name="signal" class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" />
                                            <span class="truncate">{{ $workOrder->erb->site_id ?: $workOrder->erb->code }}</span>
                                        @elseif ($workOrder->radioLink)
                                            <x-icon name="arrows-right-left" class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" />
                                            <span class="truncate">{{ $workOrder->radioLink->erbA?->site_id ?: ($workOrder->radioLink->erbA?->code ?? '—') }}</span>
                                            <x-icon name="arrow-right" class="h-3 w-3 shrink-0 text-gray-400 dark:text-gray-500" />
                                            <span class="truncate">{{ $workOrder->radioLink->erbB?->site_id ?: ($workOrder->radioLink->erbB?->code ?? '—') }}</span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-work-order-priority :work-order="$workOrder" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-work-order-status :work-order="$workOrder" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-relative-time :value="$workOrder->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-work-order-actions-dropdown :work-order="$workOrder" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-16 text-center" colspan="6">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if ($hasFilters)
                                            Nenhuma ordem de serviço encontrada
                                        @else
                                            Nenhuma ordem de serviço cadastrada
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if ($hasFilters)
                                            Ajuste a busca ou os filtros para ver mais resultados.
                                        @else
                                            Abra a primeira OS para começar a acompanhar seu trabalho.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                <form method="GET" action="{{ route('admin.work-orders.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                        @if (request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach
                    <label for="per-page" class="sr-only">OS por página</label>
                    <select
                        id="per-page"
                        name="per_page"
                        onchange="this.form.submit()"
                        class="{{ $selectClass }} py-1.5"
                    >
                        @foreach ([12, 24, 36] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                        @endforeach
                    </select>
                </form>

                <x-pagination :paginator="$workOrders" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($workOrders as $workOrder)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="clipboard-document-list" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="block truncate font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $workOrder->number }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $workOrder->client->name }}</p>
                            </div>
                        </div>

                        <x-work-order-actions-dropdown :work-order="$workOrder" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <x-work-order-status :work-order="$workOrder" />
                        <x-work-order-priority :work-order="$workOrder" />
                    </div>

                    <div class="mt-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <p class="flex items-center gap-1.5">
                            <x-icon name="calendar-days" class="h-3.5 w-3.5" />
                            {{ $workOrder->opened_at->format('d/m/Y') }}@if ($workOrder->due_at) &nbsp;&rarr;&nbsp;{{ $workOrder->due_at->format('d/m/Y') }}@endif
                        </p>
                        <p class="flex items-center gap-1.5">
                            <x-icon name="rectangle-stack" class="h-3.5 w-3.5" />
                            {{ $workOrder->items_count }} {{ $workOrder->items_count === 1 ? 'item' : 'itens' }}
                        </p>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total</span>
                        <span class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $workOrder->total) }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-gray-200/70 bg-white px-5 py-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhuma ordem de serviço encontrada
                        @else
                            Nenhuma ordem de serviço cadastrada
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Abra a primeira OS para começar a acompanhar seu trabalho.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.work-orders.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">OS por página</label>
                <select
                    id="per-page"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="{{ $selectClass }} py-1.5"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$workOrders" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($workOrders as $workOrder)
                <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="clipboard-document-list" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="truncate font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $workOrder->number }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $workOrder->client->name }}</p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm font-medium text-gray-700 md:inline dark:text-gray-200">
                        {{ $money((float) $workOrder->total) }}
                    </span>

                    <div class="hidden sm:block">
                        <x-work-order-status :work-order="$workOrder" />
                    </div>

                    <x-work-order-actions-dropdown :work-order="$workOrder" />
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhuma ordem de serviço encontrada
                        @else
                            Nenhuma ordem de serviço cadastrada
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Abra a primeira OS para começar a acompanhar seu trabalho.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.work-orders.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">OS por página</label>
                <select
                    id="per-page"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="{{ $selectClass }} py-1.5"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$workOrders" />
        </div>
    @endif

    <div
        id="work-order-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="work-order-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-3xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="work-order-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Nova ordem de serviço
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Abra uma OS para um cliente e monte a lista de itens.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('admin.work-orders.store') }}" class="mt-5" data-wizard novalidate>
                @csrf
                <input type="hidden" name="modal" value="create">

                <ol data-wizard-steps class="flex flex-wrap items-center gap-2" role="list"></ol>

                <div class="mt-5">
                    @include('admin.work-orders.partials.form', ['workOrder' => null, 'wizard' => true])
                </div>

                <div class="mt-6 flex items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        data-wizard-prev
                        class="hidden rounded-xl border border-gray-200/70 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    >
                        Voltar
                    </button>

                    <div class="ml-auto flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-xl border border-gray-200/70 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-200 dark:hover:border-indigo-500/40"
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
                            Criar ordem de serviço
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div
        id="work-order-import-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="work-order-import-modal-title"
        @if ($errors->any() && old('modal') === 'import') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="work-order-import-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="arrow-up-tray" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Importar ordens de serviço
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Envie uma planilha Excel (.xlsx) com uma OS por linha.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            @if ($errors->any() && old('modal') === 'import')
                <div
                    data-modal-error
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">Nenhuma OS foi criada. Corrija os itens abaixo e tente novamente:</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @forelse ($errors->get('import') as $message)
                            <li>{{ $message }}</li>
                        @empty
                            <li>Verifique o arquivo enviado.</li>
                        @endforelse
                    </ul>
                </div>
            @endif

            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">
                <p>Colunas: <span class="font-medium">Cliente, ERB, Radio link, Descrição, Prioridade, Status, Abertura, Previsão, Observações</span>.</p>
                <p class="mt-1">Informe a ERB (Site ID ou código) ou o Radio link (código), nunca ambos.</p>
                <p class="mt-1">O cliente precisa já existir. Prioridade: baixa, normal, alta ou urgente. Status: aberta, em andamento, em espera, concluída ou cancelada. Datas em dd/mm/aaaa.</p>
                <a
                    href="{{ route('admin.work-orders.import.template') }}"
                    class="mt-2 inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    <x-icon name="arrow-down-tray" class="h-4 w-4" />
                    Baixar modelo
                </a>
            </div>

            <form method="POST" action="{{ route('admin.work-orders.import') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="import">

                <label for="import-file" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Planilha (.xlsx)</label>
                <input
                    type="file"
                    name="file"
                    id="import-file"
                    accept=".xlsx,.xls"
                    required
                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                @error('file')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        class="{{ $secondaryButton }}"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <x-icon name="arrow-up-tray" class="h-4 w-4" />
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="work-order-import-complete-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="work-order-import-complete-modal-title"
        @if ($errors->any() && old('modal') === 'import-complete') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="work-order-import-complete-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="rectangle-stack" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Importar OS com itens
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Envie uma planilha com uma linha por item da OS.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            @if ($errors->any() && old('modal') === 'import-complete')
                <div
                    data-modal-error
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">Nenhuma OS foi importada. Corrija os itens abaixo e tente novamente:</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @forelse ($errors->get('import') as $message)
                            <li>{{ $message }}</li>
                        @empty
                            <li>Verifique o arquivo enviado.</li>
                        @endforelse
                    </ul>
                </div>
            @endif

            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">
                <p>Colunas: <span class="font-medium">Cliente, ERB, Radio link, Descrição, Prioridade, Status, Abertura, Previsão, Observações, OC, CHAVE, Item, Quantidade, Status do item, OS cliente 1, OS cliente 2</span>.</p>
                <p class="mt-1">Cada linha representa um item. Repita a <span class="font-medium">OC</span> ou a <span class="font-medium">CHAVE</span> nas linhas da mesma OS para agrupá-las.</p>
                <p class="mt-1">Objetos ausentes (cliente, ERB e radio link) são criados automaticamente. Info: prioridade baixa, normal, alta ou urgente; status da OS aberta, em andamento, em espera, concluída ou cancelada; status do item pendente, em andamento, concluído, rejeitado ou cancelado.</p>
                <a
                    href="{{ route('admin.work-orders.import.template', ['mode' => 'completo']) }}"
                    class="mt-2 inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    <x-icon name="arrow-down-tray" class="h-4 w-4" />
                    Baixar modelo completo
                </a>
            </div>

            <form method="POST" action="{{ route('admin.work-orders.import') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="import-complete">
                <input type="hidden" name="mode" value="completo">

                <label for="import-complete-file" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Planilha (.xlsx)</label>
                <input
                    type="file"
                    name="file"
                    id="import-complete-file"
                    accept=".xlsx,.xls"
                    required
                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                @error('file')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        class="{{ $secondaryButton }}"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <x-icon name="arrow-up-tray" class="h-4 w-4" />
                        Importar com itens
                    </button>
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

            const savedView = localStorage.getItem('work-orders-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('work-orders-view', link.dataset.viewToggle);
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

        const positionDropdown = (toggle, menu) => {
            const toggleRect = toggle.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            const opensUp = window.innerHeight - toggleRect.bottom < menuRect.height + 8;

            menu.style.position = 'fixed';
            menu.style.right = 'auto';
            menu.style.marginTop = '0';
            menu.style.marginBottom = '0';
            menu.style.left = `${Math.max(8, toggleRect.right - menuRect.width)}px`;
            menu.style.top = opensUp
                ? `${Math.max(8, toggleRect.top - menuRect.height - 4)}px`
                : `${toggleRect.bottom + 4}px`;
        };

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-dropdown-toggle]');

            if (toggle) {
                const menu = toggle.nextElementSibling;
                const willOpen = menu.classList.contains('hidden');
                closeDropdowns(willOpen ? menu : null);
                menu.classList.toggle('hidden', !willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));

                if (willOpen) {
                    positionDropdown(toggle, menu);
                }

                return;
            }

            if (!event.target.closest('[data-dropdown-menu]')) {
                closeDropdowns();
            }
        });

        window.addEventListener('scroll', () => closeDropdowns(), true);
        window.addEventListener('resize', () => closeDropdowns());

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdowns();
            }
        });

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
                closeDropdowns();

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

        const workOrderModal = document.getElementById('work-order-modal');
        if (workOrderModal?.hasAttribute('data-open')) {
            openModal(workOrderModal);
        }

        const workOrderImportModal = document.getElementById('work-order-import-modal');
        if (workOrderImportModal?.hasAttribute('data-open')) {
            openModal(workOrderImportModal);
        }

        const workOrderImportCompleteModal = document.getElementById('work-order-import-complete-modal');
        if (workOrderImportCompleteModal?.hasAttribute('data-open')) {
            openModal(workOrderImportCompleteModal);
        }

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

            document.querySelectorAll('[data-modal-open="work-order-modal"]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            const errorPanel = panels.findIndex((panel) => panel.querySelector('.text-red-600'));

            goTo(errorPanel > -1 ? errorPanel : 0);
        }
    </script>
</x-layouts.admin>
