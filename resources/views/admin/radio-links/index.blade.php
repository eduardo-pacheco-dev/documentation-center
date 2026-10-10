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
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="arrows-right-left" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Radio links
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enlaces ponto a ponto entre duas ERBs.</p>
        </div>

        <a
            href="{{ route('admin.radio-links.create') }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo radio link
        </a>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
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
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    data-search-input
                >
            </div>

            <button
                type="submit"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                Buscar
            </button>

            @if ($search !== '')
                <a
                    href="{{ route('admin.radio-links.index', array_filter(['status' => $status, 'view' => $view])) }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select
                id="status-filter"
                name="status"
                onchange="this.form.submit()"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-3 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                @foreach (['' => 'Todos os status', 'active' => 'Ativos', 'maintenance' => 'Em manutenção', 'planned' => 'Planejados', 'inactive' => 'Desativados'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.radio-links.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm {{ $view === $mode ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($view === 'table')
        <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="overflow-x-auto lg:overflow-x-visible">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="rounded-t-xl bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <x-sort-header column="code" label="Código" icon="arrows-right-left" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 font-medium">Ponta A</th>
                            <th class="px-5 py-3 font-medium">Ponta B</th>
                            <x-sort-header column="frequency" label="Frequência" class="px-5 py-3" />
                            <x-sort-header column="capacity" label="Capacidade" class="px-5 py-3" />
                            <th class="px-5 py-3 font-medium">Distância</th>
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($radioLinks as $radioLink)
                            <tr>
                                <td class="px-5 py-3">
                                    <a
                                        href="{{ route('admin.radio-links.show', $radioLink) }}"
                                        class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        <x-icon name="arrows-right-left" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $radioLink->code }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">
                                    @if ($radioLink->erbA)
                                        <a href="{{ route('admin.erbs.show', $radioLink->erbA) }}" class="font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                            {{ $radioLink->erbA->code }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">
                                    @if ($radioLink->erbB)
                                        <a href="{{ route('admin.erbs.show', $radioLink->erbB) }}" class="font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                            {{ $radioLink->erbB->code }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $ghz($radioLink->frequency) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $mbps($radioLink->capacity) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $km($radioLink->distanceKm()) }}</td>
                                <td class="px-5 py-3">
                                    <x-radio-link-status :radio-link="$radioLink" />
                                </td>
                                <td class="px-5 py-3">
                                    <x-relative-time :value="$radioLink->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-radio-link-actions-dropdown :radio-link="$radioLink" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="9">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="arrows-right-left" class="h-4 w-4" />
                                        @if ($search !== '' || $status !== null)
                                            Nenhum radio link encontrado para os filtros informados.
                                        @else
                                            Nenhum radio link cadastrado até o momento.
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
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
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
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
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="arrows-right-left" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.radio-links.show', $radioLink) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $radioLink->code }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $radioLink->erbA?->code ?? '—' }} a {{ $radioLink->erbB?->code ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <x-radio-link-actions-dropdown :radio-link="$radioLink" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $ghz($radioLink->frequency) }}</span>
                        @if ($radioLink->bandwidth !== null)
                            <span>&middot; {{ $mhz($radioLink->bandwidth) }}</span>
                        @endif
                        <span>&middot; {{ $km($radioLink->distanceKm()) }}</span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <x-radio-link-status :radio-link="$radioLink" />
                        <span>{{ $radioLink->erbA?->name ?? '—' }} · {{ $radioLink->erbB?->name ?? '—' }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="arrows-right-left" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '' || $status !== null)
                        Nenhum radio link encontrado para os filtros informados.
                    @else
                        Nenhum radio link cadastrado até o momento.
                    @endif
                </p>
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
                    class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$radioLinks" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($radioLinks as $radioLink)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="arrows-right-left" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.radio-links.show', $radioLink) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
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
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="arrows-right-left" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '' || $status !== null)
                        Nenhum radio link encontrado para os filtros informados.
                    @else
                        Nenhum radio link cadastrado até o momento.
                    @endif
                </p>
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
                    class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$radioLinks" />
        </div>
    @endif

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
</x-layouts.admin>