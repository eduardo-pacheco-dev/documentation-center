<x-layouts.admin title="Clientes">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="building-office" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Clientes
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre e organize os dados de contato dos seus clientes.</p>
        </div>

        <button
            type="button"
            data-modal-open="client-modal"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo cliente
        </button>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form
            method="GET"
            action="{{ route('admin.clients.index') }}"
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
                    placeholder="Buscar cliente pelo nome, e-mail, documento ou cidade..."
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
                    href="{{ route('admin.clients.index') }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.clients.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="rounded-t-xl bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <x-sort-header column="name" label="Cliente" icon="building-office" class="px-5 py-3" />
                            <x-sort-header column="document" label="CPF/CNPJ" class="px-5 py-3" />
                            <x-sort-header column="city" label="Cidade" icon="map-pin" class="px-5 py-3" />
                            <x-sort-header column="state" label="UF" class="px-5 py-3" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($clients as $client)
                            <tr>
                                <td class="px-5 py-3">
                                    <a
                                        href="{{ route('admin.clients.show', $client) }}"
                                        class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        <x-icon name="building-office" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $client->name }}
                                    </a>
                                    @if ($client->email)
                                        <p class="mt-0.5 max-w-[20rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $client->email }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $client->document ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $client->city ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $client->state ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-client-status :client="$client" />
                                </td>
                                <td class="px-5 py-3">
                                    <x-relative-time :value="$client->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-client-actions-dropdown :client="$client" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="7">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="building-office" class="h-4 w-4" />
                                        @if ($search !== '')
                                            Nenhum cliente encontrado para "{{ $search }}".
                                        @else
                                            Nenhum cliente cadastrado até o momento.
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                <form method="GET" action="{{ route('admin.clients.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction'] as $param)
                        @if (request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach
                    <label for="per-page" class="sr-only">Clientes por página</label>
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

                <x-pagination :paginator="$clients" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($clients as $client)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="building-office" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.clients.show', $client) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $client->name }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $client->document ?? $client->email ?? 'Sem documento' }}</p>
                            </div>
                        </div>

                        <x-client-actions-dropdown :client="$client" />
                    </div>

                    @if ($client->notes)
                        <p class="mt-3 line-clamp-2 text-xs text-gray-400 dark:text-gray-500">{{ $client->notes }}</p>
                    @endif

                    <div class="mt-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        @if ($client->phone)
                            <p>{{ $client->phone }}</p>
                        @endif
                        <p>
                            @if ($client->city)
                                {{ $client->city }}@if ($client->state) / {{ $client->state }} @endif
                            @else
                                Endereço não informado
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <x-client-status :client="$client" />
                        <x-relative-time :value="$client->updated_at" />
                    </div>
                </div>
            @empty
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="building-office" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '')
                        Nenhum cliente encontrado para "{{ $search }}".
                    @else
                        Nenhum cliente cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.clients.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">Clientes por página</label>
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

            <x-pagination :paginator="$clients" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($clients as $client)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="building-office" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.clients.show', $client) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $client->name }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $client->email ?? $client->phone ?? $client->document ?? 'Sem contato informado' }}
                        </p>
                    </div>

                    <span class="hidden text-xs text-gray-500 md:inline dark:text-gray-400">
                        @if ($client->city)
                            {{ $client->city }}@if ($client->state) / {{ $client->state }} @endif
                        @else
                            —
                        @endif
                    </span>

                    <div class="hidden sm:block">
                        <x-client-status :client="$client" />
                    </div>

                    <x-client-actions-dropdown :client="$client" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="building-office" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '')
                        Nenhum cliente encontrado para "{{ $search }}".
                    @else
                        Nenhum cliente cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.clients.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">Clientes por página</label>
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

            <x-pagination :paginator="$clients" />
        </div>
    @endif

    <div
        id="client-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="client-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="client-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="building-office" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Novo cliente
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registre os dados de contato e o endereço do cliente.</p>
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

            <form method="POST" action="{{ route('admin.clients.store') }}" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="create">

                @include('admin.clients.partials.form', ['client' => null])

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Criar cliente
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

            const savedView = localStorage.getItem('clients-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('clients-view', link.dataset.viewToggle);
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

        const clientModal = document.getElementById('client-modal');
        if (clientModal?.hasAttribute('data-open')) {
            openModal(clientModal);
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
    </script>
</x-layouts.admin>
