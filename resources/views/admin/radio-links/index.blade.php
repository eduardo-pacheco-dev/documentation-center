<x-layouts.admin title="Radio links">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
        $ghz = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 1, ',', '.').' GHz';
        $mhz = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 0, ',', '.').' MHz';
        $mbps = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 0, ',', '.').' Mbps';
        $km = fn (?float $value): string => $value === null
            ? '—'
            : number_format($value, 2, ',', '.').' km';

        $linksOnPage = $radioLinks->getCollection();
        $activeOnPage = $linksOnPage->filter(fn ($link) => $link->status?->value === 'active')->count();
        $maintenanceOnPage = $linksOnPage->filter(fn ($link) => $link->status?->value === 'maintenance')->count();
        $plannedOnPage = $linksOnPage->filter(fn ($link) => $link->status?->value === 'planned')->count();
        $hasFilters = $search !== '' || $status !== null;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Rede
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="arrows-right-left" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Radio links
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie os enlaces ponto a ponto entre duas ERBs.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                data-modal-open="radio-link-create-modal"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Novo radio link
            </button>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="arrows-right-left" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $radioLinks->total() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Radio links encontrados</p>
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
                <x-icon name="wrench-screwdriver" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $maintenanceOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em manutenção nesta página</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-lg hover:shadow-sky-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-sky-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <x-icon name="clock" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $plannedOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Planejados nesta página</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            method="GET"
            action="{{ route('admin.radio-links.index') }}"
            class="flex flex-wrap items-center gap-2"
            data-search-form
        >
            @foreach (['view', 'sort', 'direction', 'per_page', 'status'] as $param)
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
                    placeholder="Buscar por código, ERB, frequência ou equipamento..."
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
                    href="{{ route('admin.radio-links.index', array_filter(['status' => $status, 'view' => $view])) }}"
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
                @foreach (['' => 'Todos os status', 'active' => 'Ativos', 'maintenance' => 'Em manutenção', 'planned' => 'Planejados', 'inactive' => 'Desativados'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $radioLinks->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $radioLinks->total() }}</span>
            {{ $radioLinks->total() === 1 ? 'radio link' : 'radio links' }}
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.radio-links.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
                            <x-sort-header column="code" label="Radio link" icon="arrows-right-left" default="updated_at" class="px-5 py-3.5" />
                            <x-sort-header column="frequency" label="Frequência" class="px-5 py-3.5" />
                            <x-sort-header column="capacity" label="Capacidade" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 font-medium">Distância</th>
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3.5" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($radioLinks as $radioLink)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                            <x-icon name="arrows-right-left" class="h-4 w-4" />
                                        </span>
                                        <div class="min-w-0">
                                            <a
                                                href="{{ route('admin.radio-links.show', $radioLink) }}"
                                                class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                            >
                                                {{ $radioLink->code }}
                                            </a>
                                            <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                @if ($radioLink->erbA)
                                                    <a href="{{ route('admin.erbs.show', $radioLink->erbA) }}" class="truncate hover:text-indigo-600 dark:hover:text-indigo-400">{{ $radioLink->erbA->site_id ?: $radioLink->erbA->code }}</a>
                                                @else
                                                    <span class="truncate">—</span>
                                                @endif
                                                <x-icon name="arrows-right-left" class="h-3 w-3 shrink-0 text-gray-300 dark:text-gray-600" />
                                                @if ($radioLink->erbB)
                                                    <a href="{{ route('admin.erbs.show', $radioLink->erbB) }}" class="truncate hover:text-indigo-600 dark:hover:text-indigo-400">{{ $radioLink->erbB->site_id ?: $radioLink->erbB->code }}</a>
                                                @else
                                                    <span class="truncate">—</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $ghz($radioLink->frequency) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $mbps($radioLink->capacity) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $km($radioLink->distanceKm()) }}</td>
                                <td class="px-5 py-4">
                                    <x-radio-link-status :radio-link="$radioLink" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-relative-time :value="$radioLink->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-radio-link-actions-dropdown :radio-link="$radioLink" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-16 text-center" colspan="7">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <x-icon name="arrows-right-left" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if ($hasFilters)
                                            Nenhum radio link encontrado
                                        @else
                                            Nenhum radio link cadastrado
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if ($hasFilters)
                                            Ajuste a busca ou os filtros para ver mais resultados.
                                        @else
                                            Cadastre o primeiro enlace ponto a ponto para começar.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                <form method="GET" action="{{ route('admin.radio-links.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
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

                <x-pagination :paginator="$radioLinks" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($radioLinks as $radioLink)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="arrows-right-left" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.radio-links.show', $radioLink) }}" class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $radioLink->code }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $radioLink->erbA?->code ?? '—' }} a {{ $radioLink->erbB?->code ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <x-radio-link-actions-dropdown :radio-link="$radioLink" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2 py-1 font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <x-icon name="signal" class="h-3.5 w-3.5" />
                            {{ $ghz($radioLink->frequency) }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ $mbps($radioLink->capacity) }}
                        </span>
                        @if ($radioLink->bandwidth !== null)
                            <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $mhz($radioLink->bandwidth) }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0" />
                        Distância de {{ $km($radioLink->distanceKm()) }} entre as pontas
                    </p>

                    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3.5 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <x-radio-link-status :radio-link="$radioLink" />
                        @if ($radioLink->polarization)
                            <span>{{ $radioLink->polarization->label() }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center dark:border-gray-700 dark:bg-gray-900">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="arrows-right-left" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhum radio link encontrado
                        @else
                            Nenhum radio link cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Cadastre o primeiro enlace ponto a ponto para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.radio-links.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
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

            <x-pagination :paginator="$radioLinks" />
        </div>
    @else
        <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @forelse ($radioLinks as $radioLink)
                <div class="flex items-center gap-4 border-b border-gray-100 px-5 py-3.5 transition last:border-b-0 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="arrows-right-left" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.radio-links.show', $radioLink) }}" class="truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $radioLink->code }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $radioLink->erbA?->code ?? '—' }} a {{ $radioLink->erbB?->code ?? '—' }} · {{ $ghz($radioLink->frequency) }}
                        </p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 md:inline dark:text-gray-400">
                        {{ $km($radioLink->distanceKm()) }}
                    </span>

                    <div class="hidden sm:block">
                        <x-radio-link-status :radio-link="$radioLink" />
                    </div>

                    <x-radio-link-actions-dropdown :radio-link="$radioLink" />
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="arrows-right-left" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhum radio link encontrado
                        @else
                            Nenhum radio link cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Cadastre o primeiro enlace ponto a ponto para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.radio-links.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
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

            <x-pagination :paginator="$radioLinks" />
        </div>
    @endif

    @include('admin.radio-links.partials.create-modal')

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('radio-links-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('radio-links-view', link.dataset.viewToggle);
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

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }

            const search = modal.querySelector('[data-modal-search]');
            if (search) {
                search.focus();
            }
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-modal-open]');

            if (opener) {
                event.preventDefault();
                closeDropdowns();
                openModal(document.getElementById(opener.dataset.modalOpen));
                return;
            }

            const closer = event.target.closest('[data-modal-close]');
            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdowns();
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
            }
        });

        document.querySelectorAll('[role="dialog"][data-open]').forEach(openModal);

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
            const inactiveIndicator = baseIndicator + ' border-gray-200 bg-white text-gray-600 hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-indigo-500/40';
            const activeIndicator = baseIndicator + ' border-indigo-600 bg-indigo-600 text-white shadow-sm';
            const doneIndicator = baseIndicator + ' border-emerald-500 text-emerald-600 dark:text-emerald-400';
            let current = 0;

            if (stepsList) {
                stepsList.innerHTML = panels.map((panel, index) => `
                    <li>
                        <button type="button" data-wizard-indicator="${index}" class="${inactiveIndicator}">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 text-[0.65rem] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">${index + 1}</span>
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

            document.querySelectorAll('[data-modal-open="radio-link-create-modal"]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            const errorPanel = panels.findIndex((panel) => panel.querySelector('.text-red-600'));

            goTo(errorPanel > -1 ? errorPanel : 0);
        }
    </script>
</x-layouts.admin>
