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
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="cube" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Catálogo
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre os produtos e serviços da sua empresa.</p>
        </div>

        <button
            type="button"
            data-modal-open="catalog-item-modal"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo item
        </button>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
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
                    href="{{ route('admin.catalog.index', array_filter(['type' => $type, 'view' => $view])) }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="type-filter" class="sr-only">Filtrar por tipo</label>
            <select
                id="type-filter"
                name="type"
                onchange="this.form.submit()"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-3 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                @foreach (['' => 'Todos os tipos', 'product' => 'Produtos', 'service' => 'Serviços'] as $value => $label)
                    <option value="{{ $value }}" @selected($type === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.catalog.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
                            <x-sort-header column="name" label="Item" icon="cube" class="px-5 py-3" />
                            <x-sort-header column="type" label="Tipo" class="px-5 py-3" />
                            <x-sort-header column="unit" label="Unidade" class="px-5 py-3" />
                            <x-sort-header column="price" label="Preço" class="px-5 py-3" />
                            <x-sort-header column="cost" label="Custo" class="px-5 py-3" />
                            <x-sort-header column="stock_quantity" label="Estoque" class="px-5 py-3" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($catalogItems as $catalogItem)
                            <tr>
                                <td class="px-5 py-3">
                                    <a
                                        href="{{ route('admin.catalog.show', $catalogItem) }}"
                                        class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        <x-icon :name="$catalogItem->type->value === 'product' ? 'cube' : 'wrench-screwdriver'" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $catalogItem->name }}
                                    </a>
                                    @if ($catalogItem->code)
                                        <p class="mt-0.5 max-w-[20rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $catalogItem->code }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <x-catalog-item-type :catalog-item="$catalogItem" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $catalogItem->unit ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $money((float) $catalogItem->price) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $money((float) $catalogItem->cost) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $catalogItem->type->value === 'product' ? $stock($catalogItem->stock_quantity) : '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <x-catalog-item-status :catalog-item="$catalogItem" />
                                </td>
                                <td class="px-5 py-3">
                                    <x-relative-time :value="$catalogItem->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-catalog-item-actions-dropdown :catalog-item="$catalogItem" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="9">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="cube" class="h-4 w-4" />
                                        @if ($search !== '')
                                            Nenhum item encontrado para "{{ $search }}".
                                        @else
                                            Nenhum item cadastrado até o momento.
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
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
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
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
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon :name="$catalogItem->type->value === 'product' ? 'cube' : 'wrench-screwdriver'" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.catalog.show', $catalogItem) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $catalogItem->name }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $catalogItem->code ?? $catalogItem->type->label() }}</p>
                            </div>
                        </div>

                        <x-catalog-item-actions-dropdown :catalog-item="$catalogItem" />
                    </div>

                    <p class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $catalogItem->price) }}</p>

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
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="cube" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '')
                        Nenhum item encontrado para "{{ $search }}".
                    @else
                        Nenhum item cadastrado até o momento.
                    @endif
                </p>
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
                    class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$catalogItems" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($catalogItems as $catalogItem)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon :name="$catalogItem->type->value === 'product' ? 'cube' : 'wrench-screwdriver'" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.catalog.show', $catalogItem) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $catalogItem->name }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $catalogItem->code ?? $catalogItem->description ?? $catalogItem->type->label() }}
                        </p>
                    </div>

                    <div class="hidden sm:block">
                        <x-catalog-item-type :catalog-item="$catalogItem" />
                    </div>

                    <span class="hidden whitespace-nowrap text-sm font-medium text-gray-700 md:inline dark:text-gray-200">
                        {{ $money((float) $catalogItem->price) }}
                    </span>

                    <div class="hidden sm:block">
                        <x-catalog-item-status :catalog-item="$catalogItem" />
                    </div>

                    <x-catalog-item-actions-dropdown :catalog-item="$catalogItem" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="cube" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '')
                        Nenhum item encontrado para "{{ $search }}".
                    @else
                        Nenhum item cadastrado até o momento.
                    @endif
                </p>
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
                    class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$catalogItems" />
        </div>
    @endif

    <div
        id="catalog-item-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="catalog-item-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="catalog-item-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
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

                <div class="mt-6 flex items-center justify-between gap-3">
                    <button
                        type="button"
                        class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        data-modal-close
                    >
                        Cancelar
                    </button>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            data-wizard-prev
                            class="hidden rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            Voltar
                        </button>

                        <button
                            type="button"
                            data-wizard-next
                            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                        >
                            Próximo
                        </button>

                        <button
                            type="submit"
                            data-wizard-submit
                            class="hidden items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
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

        const openModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
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

        const catalogModal = document.getElementById('catalog-item-modal');
        if (catalogModal?.hasAttribute('data-open')) {
            openModal(catalogModal);
        }

        const wizard = catalogModal?.querySelector('[data-wizard]');

        if (wizard) {
            const steps = Array.from(wizard.querySelectorAll('[data-wizard-step]'));
            const label = catalogModal.querySelector('[data-wizard-label]');
            const counter = catalogModal.querySelector('[data-wizard-counter]');
            const progressBar = catalogModal.querySelector('[data-wizard-progress]');
            const prevButton = catalogModal.querySelector('[data-wizard-prev]');
            const nextButton = catalogModal.querySelector('[data-wizard-next]');
            const submitButton = catalogModal.querySelector('[data-wizard-submit]');

            const stepLabels = ['Identificação', 'Valores', 'Detalhes'];
            const progressWidths = ['w-1/3', 'w-2/3', 'w-full'];

            let current = 0;

            const render = () => {
                steps.forEach((step, index) => {
                    step.classList.toggle('hidden', index !== current);
                });

                if (label) {
                    label.textContent = stepLabels[current] || '';
                }

                if (counter) {
                    counter.textContent = 'Passo ' + (current + 1) + ' de ' + steps.length;
                }

                if (progressBar) {
                    progressBar.classList.remove('w-1/3', 'w-2/3', 'w-full');
                    progressBar.classList.add(progressWidths[current] || 'w-full');
                }

                prevButton?.classList.toggle('hidden', current === 0);
                nextButton?.classList.toggle('hidden', current === steps.length - 1);
                submitButton?.classList.toggle('hidden', current !== steps.length - 1);
                submitButton?.classList.toggle('inline-flex', current === steps.length - 1);
            };

            const goTo = (index) => {
                current = Math.max(0, Math.min(index, steps.length - 1));
                render();
            };

            nextButton?.addEventListener('click', () => {
                const step = steps[current];
                const invalid = Array.from(step.querySelectorAll('input, select, textarea')).find((field) => !field.checkValidity());

                if (invalid) {
                    invalid.reportValidity();

                    return;
                }

                goTo(current + 1);
            });

            prevButton?.addEventListener('click', () => goTo(current - 1));

            document.querySelectorAll('[data-modal-open="catalog-item-modal"]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            const errorStep = steps.findIndex((step) => step.querySelector('.text-red-600'));

            goTo(errorStep > -1 ? errorStep : 0);
        }
    </script>
</x-layouts.admin>
