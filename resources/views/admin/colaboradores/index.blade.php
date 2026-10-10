<x-layouts.admin title="Colaboradores">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="user" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Colaboradores
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie a equipe técnica e demais colaboradores.</p>
        </div>

        <a
            href="{{ route('admin.colaboradores.create') }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo colaborador
        </a>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form
            method="GET"
            action="{{ route('admin.colaboradores.index') }}"
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
                    placeholder="Buscar por nome, cargo, e-mail ou documento..."
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
                    href="{{ route('admin.colaboradores.index', array_filter(['status' => $status, 'view' => $view])) }}"
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
                @foreach (['' => 'Todos os status', 'active' => 'Ativos', 'inactive' => 'Inativos'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.colaboradores.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
                            <x-sort-header column="name" label="Nome" icon="user" class="px-5 py-3" />
                            <x-sort-header column="role" label="Cargo" class="px-5 py-3" />
                            <th class="px-5 py-3 font-medium">Documento</th>
                            <x-sort-header column="email" label="E-mail" class="px-5 py-3" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($colaboradores as $colaborador)
                            <tr>
                                <td class="px-5 py-3">
                                    <a
                                        href="{{ route('admin.colaboradores.show', $colaborador) }}"
                                        class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        <x-icon name="user" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $colaborador->name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $colaborador->role ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $colaborador->document ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $colaborador->email ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-colaborador-status :colaborador="$colaborador" />
                                </td>
                                <td class="px-5 py-3">
                                    <x-relative-time :value="$colaborador->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-colaborador-actions-dropdown :colaborador="$colaborador" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="7">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="user" class="h-4 w-4" />
                                        @if ($search !== '' || $status !== null)
                                            Nenhum colaborador encontrado para os filtros informados.
                                        @else
                                            Nenhum colaborador cadastrado até o momento.
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                <form method="GET" action="{{ route('admin.colaboradores.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
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

                <x-pagination :paginator="$colaboradores" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($colaboradores as $colaborador)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="user" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.colaboradores.show', $colaborador) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $colaborador->name }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $colaborador->role ?? 'Cargo não informado' }}</p>
                            </div>
                        </div>

                        <x-colaborador-actions-dropdown :colaborador="$colaborador" />
                    </div>

                    <div class="mt-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        @if ($colaborador->phone)
                            <p class="flex items-center gap-1">
                                <x-icon name="phone" class="h-3.5 w-3.5" />
                                {{ $colaborador->phone }}
                            </p>
                        @endif
                        @if ($colaborador->email)
                            <p class="flex items-center gap-1">
                                <x-icon name="envelope" class="h-3.5 w-3.5" />
                                {{ $colaborador->email }}
                            </p>
                        @endif
                        @if ($colaborador->document)
                            <p class="font-medium tracking-tight text-gray-600 dark:text-gray-300">{{ $colaborador->document }}</p>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                        <x-colaborador-status :colaborador="$colaborador" />
                        <x-relative-time :value="$colaborador->updated_at" class="text-xs text-gray-500 dark:text-gray-400" />
                    </div>
                </div>
            @empty
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="user" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '' || $status !== null)
                        Nenhum colaborador encontrado para os filtros informados.
                    @else
                        Nenhum colaborador cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.colaboradores.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
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

            <x-pagination :paginator="$colaboradores" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($colaboradores as $colaborador)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="user" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.colaboradores.show', $colaborador) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $colaborador->name }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $colaborador->role ?? 'Cargo não informado' }}{{ $colaborador->email ? ' · '.$colaborador->email : '' }}
                        </p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 md:inline dark:text-gray-400">
                        {{ $colaborador->document ?? '—' }}
                    </span>

                    <div class="hidden sm:block">
                        <x-colaborador-status :colaborador="$colaborador" />
                    </div>

                    <x-colaborador-actions-dropdown :colaborador="$colaborador" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="user" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '' || $status !== null)
                        Nenhum colaborador encontrado para os filtros informados.
                    @else
                        Nenhum colaborador cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.colaboradores.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
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

            <x-pagination :paginator="$colaboradores" />
        </div>
    @endif

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('colaboradores-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('colaboradores-view', link.dataset.viewToggle);
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