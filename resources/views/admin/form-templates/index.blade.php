<x-layouts.admin title="Templates de formulário">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $hasFilters = $search !== '' || $type !== null;
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="document-duplicate" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Templates de formulário
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Estruturas reutilizáveis compartilhadas com todos os usuários.</p>
        </div>

        <a
            href="{{ route('admin.forms.index') }}"
            class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
        >
            Ir para formulários
        </a>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.form-templates.index') }}" class="flex flex-wrap items-center gap-2" data-search-form>
            @foreach (['view', 'sort', 'direction', 'per_page', 'type'] as $param)
                @if (request($param) !== null && request($param) !== '')
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach

            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por nome ou tipo..."
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

            @if ($hasFilters)
                <a
                    href="{{ route('admin.form-templates.index', array_filter(['view' => $view])) }}"
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
                <option value="">Todos os tipos</option>
                @foreach ($types as $typeOption)
                    <option value="{{ $typeOption }}" @selected($type === $typeOption)>{{ $typeOption }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.form-templates.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
                            <x-sort-header column="name" label="Nome" icon="document-duplicate" default="name" class="px-5 py-3" />
                            <x-sort-header column="type" label="Tipo" class="px-5 py-3" />
                            <th class="px-5 py-3 font-medium">Perguntas</th>
                            <th class="px-5 py-3 font-medium">Criado por</th>
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($templates as $template)
                            <tr>
                                <td class="px-5 py-3">
                                    @can('update', $template)
                                        <a
                                            href="{{ route('admin.form-templates.edit', $template) }}"
                                            class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                        >
                                            <x-icon name="document-duplicate" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                            {{ $template->name }}
                                        </a>
                                    @else
                                        <span class="flex items-center gap-2 font-medium text-gray-900 dark:text-gray-100">
                                            <x-icon name="document-duplicate" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                            {{ $template->name }}
                                        </span>
                                    @endcan
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $template->type ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">
                                    {{ collect($template->steps)->sum(fn ($step) => count($step['fields'] ?? [])) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $template->user?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <x-relative-time :value="$template->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-form-template-actions-dropdown :template="$template" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="6">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="document-duplicate" class="h-4 w-4" />
                                        @if ($hasFilters)
                                            Nenhum template encontrado para os filtros informados.
                                        @else
                                            Nenhum template cadastrado até o momento. Abra um formulário e use "Salvar como template".
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4">
                @include('admin.form-templates.partials.pagination')
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($templates as $template)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="document-duplicate" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                @can('update', $template)
                                    <a href="{{ route('admin.form-templates.edit', $template) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                        {{ $template->name }}
                                    </a>
                                @else
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
                                @endcan
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $template->type ?: 'Sem tipo' }}</p>
                            </div>
                        </div>

                        <x-form-template-actions-dropdown :template="$template" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="clipboard-document-list" class="h-3.5 w-3.5" />
                            {{ collect($template->steps)->sum(fn ($step) => count($step['fields'] ?? [])) }} perguntas
                        </span>
                        <span>&middot;</span>
                        <span class="truncate">{{ $template->user?->name ?? '—' }}</span>
                    </div>

                    <div class="mt-4 flex items-center gap-1.5 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <x-icon name="clock" class="h-3.5 w-3.5" />
                        <x-relative-time :value="$template->updated_at" />
                    </div>
                </div>
            @empty
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="document-duplicate" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($hasFilters)
                        Nenhum template encontrado para os filtros informados.
                    @else
                        Nenhum template cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="pt-4">
            @include('admin.form-templates.partials.pagination')
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($templates as $template)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="document-duplicate" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        @can('update', $template)
                            <a href="{{ route('admin.form-templates.edit', $template) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                {{ $template->name }}
                            </a>
                        @else
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
                        @endcan
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $template->type ?: 'Sem tipo' }}</p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 sm:inline dark:text-gray-400">
                        {{ collect($template->steps)->sum(fn ($step) => count($step['fields'] ?? [])) }} perguntas
                    </span>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 md:inline dark:text-gray-400">{{ $template->user?->name ?? '—' }}</span>

                    <span class="hidden lg:inline">
                        <x-relative-time :value="$template->updated_at" class="text-xs text-gray-500 dark:text-gray-400" />
                    </span>

                    <x-form-template-actions-dropdown :template="$template" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="document-duplicate" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($hasFilters)
                        Nenhum template encontrado para os filtros informados.
                    @else
                        Nenhum template cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="pt-4">
            @include('admin.form-templates.partials.pagination')
        </div>
    @endif

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (!params.has('view')) {
                const savedView = localStorage.getItem('form-templates-view');

                if (['table', 'cards', 'compact'].includes(savedView)) {
                    params.set('view', savedView);
                    window.location.replace(window.location.pathname + '?' + params.toString());
                    return;
                }
            }
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('form-templates-view', link.dataset.viewToggle);
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
