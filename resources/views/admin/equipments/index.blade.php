<x-layouts.admin title="Equipamentos">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $itemsOnPage = $equipments->getCollection();
        $activeOnPage = $itemsOnPage->filter(fn ($equipment) => $equipment->status->value === 'active')->count();
        $maintenanceOnPage = $itemsOnPage->filter(fn ($equipment) => $equipment->status->value === 'maintenance')->count();
        $datasheetsOnPage = $itemsOnPage->sum('documents_count');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Consulta
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="wrench-screwdriver" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Equipamentos
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre os dados dos equipamentos e consulte os datasheets anexados.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.equipments.create') }}"
                class="inline-flex items-center gap-1.5 rounded-2xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                Página completa
            </a>

            <button
                type="button"
                data-modal-open="equipment-modal"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Novo equipamento
            </button>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="wrench-screwdriver" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $equipments->total() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Equipamentos cadastrados</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $activeOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ativos nesta página</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="exclamation-triangle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $maintenanceOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em manutenção nesta página</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="paper-clip" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $datasheetsOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Datasheets nesta página</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            method="GET"
            action="{{ route('admin.equipments.index') }}"
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
                    placeholder="Buscar por nome, código, fabricante, modelo ou categoria..."
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
                    href="{{ route('admin.equipments.index', array_filter(['status' => $status, 'view' => $view])) }}"
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select
                id="status-filter"
                name="status"
                onchange="this.form.submit()"
                class="rounded-xl border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
            >
                <option value="">Todos os status</option>
                @foreach (\App\Enums\EquipmentStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $equipments->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $equipments->total() }}</span>
            equipamentos
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.equipments.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
                            <x-sort-header column="name" label="Equipamento" icon="wrench-screwdriver" class="px-5 py-3.5" />
                            <x-sort-header column="manufacturer" label="Fabricante" class="px-5 py-3.5" />
                            <x-sort-header column="model" label="Modelo" class="px-5 py-3.5" />
                            <x-sort-header column="category" label="Categoria" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 font-medium">Datasheets</th>
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3.5" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($equipments as $equipment)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                            <x-icon name="wrench-screwdriver" class="h-4 w-4" />
                                        </span>
                                        <div class="min-w-0">
                                            <a
                                                href="{{ route('admin.equipments.show', $equipment) }}"
                                                class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                            >
                                                {{ $equipment->name }}
                                            </a>
                                            <p class="mt-0.5 max-w-[20rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $equipment->code ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $equipment->manufacturer ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $equipment->model ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $equipment->category ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                        <x-icon name="paper-clip" class="h-3.5 w-3.5" />
                                        {{ $equipment->documents_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <x-equipment-status :equipment="$equipment" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-relative-time :value="$equipment->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-equipment-actions-dropdown :equipment="$equipment" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-16 text-center" colspan="8">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <x-icon name="wrench-screwdriver" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if ($search !== '' || $status !== null)
                                            Nenhum equipamento encontrado
                                        @else
                                            Nenhum equipamento cadastrado
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if ($search !== '' || $status !== null)
                                            Ajuste a busca ou os filtros para ver mais resultados.
                                        @else
                                            Cadastre o primeiro equipamento para começar.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                <form method="GET" action="{{ route('admin.equipments.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                        @if (request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach
                    <label for="per-page" class="sr-only">Equipamentos por página</label>
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

                <x-pagination :paginator="$equipments" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($equipments as $equipment)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="wrench-screwdriver" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.equipments.show', $equipment) }}" class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $equipment->name }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $equipment->code ?? '—' }}</p>
                            </div>
                        </div>

                        <x-equipment-actions-dropdown :equipment="$equipment" />
                    </div>

                    <p class="mt-4 truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ $equipment->manufacturer ?? 'Fabricante não informado' }}
                        @if ($equipment->model)
                            <span class="text-gray-400 dark:text-gray-500">/ {{ $equipment->model }}</span>
                        @endif
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        @if ($equipment->category)
                            <span>{{ $equipment->category }}</span>
                        @endif
                        <span>&middot; {{ $equipment->documents_count }} datasheet(s)</span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <x-equipment-status :equipment="$equipment" />
                        <x-relative-time :value="$equipment->updated_at" />
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-gray-200/70 bg-white px-5 py-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="wrench-screwdriver" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($search !== '' || $status !== null)
                            Nenhum equipamento encontrado
                        @else
                            Nenhum equipamento cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($search !== '' || $status !== null)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Cadastre o primeiro equipamento para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.equipments.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page-cards" class="sr-only">Equipamentos por página</label>
                <select
                    id="per-page-cards"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-gray-300 bg-white py-1.5 pl-2 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$equipments" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($equipments as $equipment)
                <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="wrench-screwdriver" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.equipments.show', $equipment) }}" class="truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $equipment->name }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $equipment->code ?? '—' }}
                            @if ($equipment->manufacturer)
                                &middot; {{ $equipment->manufacturer }}
                            @endif
                            @if ($equipment->model)
                                / {{ $equipment->model }}
                            @endif
                        </p>
                    </div>

                    <span class="hidden whitespace-nowrap text-xs text-gray-500 sm:inline dark:text-gray-400">
                        {{ $equipment->documents_count }} datasheet(s)
                    </span>

                    <div class="hidden sm:block">
                        <x-equipment-status :equipment="$equipment" />
                    </div>

                    <x-equipment-actions-dropdown :equipment="$equipment" />
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="wrench-screwdriver" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($search !== '' || $status !== null)
                            Nenhum equipamento encontrado
                        @else
                            Nenhum equipamento cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($search !== '' || $status !== null)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Cadastre o primeiro equipamento para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.equipments.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page-compact" class="sr-only">Equipamentos por página</label>
                <select
                    id="per-page-compact"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-gray-300 bg-white py-1.5 pl-2 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$equipments" />
        </div>
    @endif

    @foreach ($equipments as $equipment)
        @include('admin.equipments.partials.edit-modal', [
            'equipment' => $equipment,
            'statuses' => \App\Enums\EquipmentStatus::cases(),
        ])
    @endforeach

    <div
        id="equipment-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="equipment-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="equipment-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="wrench-screwdriver" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Novo equipamento
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre os dados do equipamento para consulta.</p>
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

            @if ($errors->any() && old('modal') === 'create')
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    <p class="font-medium">Não foi possível cadastrar o equipamento. Corrija os campos destacados e tente novamente.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.equipments.store') }}" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="create">

                @include('admin.equipments.partials.form', ['equipment' => null, 'statuses' => \App\Enums\EquipmentStatus::cases()])

                <div class="mt-6 flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                        data-modal-close
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Cadastrar equipamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.equipments.partials.modal-script')
</x-layouts.admin>
