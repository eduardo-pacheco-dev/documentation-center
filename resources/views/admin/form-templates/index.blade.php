<x-layouts.admin title="Templates de formulário">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $hasFilters = $search !== '' || $type !== null;

        $questionCount = fn ($template) => collect($template->steps)->sum(fn ($step) => count($step['fields'] ?? []));

        $secondaryButton = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
        $selectClass = 'rounded-xl border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Biblioteca</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="document-duplicate" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Templates de formulário
                <a
                    href="{{ route('admin.forms.settings.edit') }}"
                    title="Configurações do módulo"
                    aria-label="Configurações do módulo de formulários"
                    class="ml-1 inline-flex h-8 w-8 items-center justify-center rounded-xl text-gray-400 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-indigo-400"
                >
                    <x-icon name="cog-6-tooth" class="h-5 w-5" />
                </a>
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Estruturas reutilizáveis compartilhadas com todos os usuários.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.form-templates.create') }}"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Novo template
            </a>

            <a href="{{ route('admin.forms.index') }}" class="{{ $secondaryButton }}">
                <x-icon name="document-text" class="h-4 w-4" />
                Ir para formulários
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="document-duplicate" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Total de templates</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-lg hover:shadow-sky-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-sky-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <x-icon name="rectangle-stack" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['types'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tipos distintos</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="clipboard-document-list" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['questions'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perguntas no total</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="users" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['contributors'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Criadores</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            method="GET"
            action="{{ route('admin.form-templates.index') }}"
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
                    placeholder="Buscar por nome ou tipo..."
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
                    href="{{ route('admin.form-templates.index', array_filter(['view' => $view])) }}"
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="type-filter" class="sr-only">Filtrar por tipo</label>
            <select id="type-filter" name="type" onchange="this.form.submit()" class="{{ $selectClass }}">
                <option value="" @selected($type === null)>Todos os tipos</option>
                @foreach ($types as $typeOption)
                    <option value="{{ $typeOption }}" @selected($type === $typeOption)>{{ $typeOption }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $templates->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $templates->total() }}</span>
            templates
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.form-templates.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-sm font-medium transition {{ $view === $mode ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-100' }}"
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
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <x-sort-header column="name" label="Nome" icon="document-duplicate" default="name" class="px-5 py-3.5" />
                            <x-sort-header column="type" label="Tipo" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 font-medium">Perguntas</th>
                            <th class="px-5 py-3.5 font-medium">Criado por</th>
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($templates as $template)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    @can('update', $template)
                                        <a
                                            href="{{ route('admin.form-templates.edit', $template) }}"
                                            class="font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                        >
                                            {{ $template->name }}
                                        </a>
                                    @else
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</span>
                                    @endcan
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($template->type)
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                            {{ $template->type }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @php $questions = $questionCount($template); @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <x-icon name="clipboard-document-list" class="h-3.5 w-3.5" />
                                        {{ $questions }} {{ $questions === 1 ? 'pergunta' : 'perguntas' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $template->user?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-relative-time :value="$template->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-form-template-actions-dropdown :template="$template" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-16 text-center" colspan="6">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <x-icon name="document-duplicate" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if ($hasFilters)
                                            Nenhum template encontrado
                                        @else
                                            Nenhum template cadastrado
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if ($hasFilters)
                                            Ajuste a busca ou os filtros para ver mais resultados.
                                        @else
                                            Abra um formulário e use "Salvar como template" para começar a montar sua biblioteca.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                @include('admin.form-templates.partials.pagination')
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($templates as $template)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="document-duplicate" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                @can('update', $template)
                                    <a href="{{ route('admin.form-templates.edit', $template) }}" class="block truncate font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                        {{ $template->name }}
                                    </a>
                                @else
                                    <p class="truncate font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
                                @endcan
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $template->type ?: 'Sem tipo' }}</p>
                            </div>
                        </div>

                        <x-form-template-actions-dropdown :template="$template" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @php $questions = $questionCount($template); @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <x-icon name="clipboard-document-list" class="h-3.5 w-3.5" />
                            {{ $questions }} {{ $questions === 1 ? 'pergunta' : 'perguntas' }}
                        </span>
                        @if ($template->type)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                {{ $template->type }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <span class="flex min-w-0 items-center gap-1.5">
                            <x-icon name="user" class="h-3.5 w-3.5 shrink-0" />
                            <span class="truncate">{{ $template->user?->name ?? '—' }}</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-1.5">
                            <x-icon name="clock" class="h-3.5 w-3.5 shrink-0" />
                            <x-relative-time :value="$template->updated_at" />
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-gray-200/70 bg-white px-5 py-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="document-duplicate" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhum template encontrado
                        @else
                            Nenhum template cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Abra um formulário e use "Salvar como template" para começar a montar sua biblioteca.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            @include('admin.form-templates.partials.pagination')
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($templates as $template)
                <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="document-duplicate" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0 flex-1">
                        @can('update', $template)
                            <a href="{{ route('admin.form-templates.edit', $template) }}" class="block truncate font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                {{ $template->name }}
                            </a>
                        @else
                            <p class="truncate font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
                        @endcan
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $template->type ?: 'Sem tipo' }}</p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 sm:inline dark:text-gray-400">
                        {{ $questionCount($template) }} perguntas
                    </span>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 md:inline dark:text-gray-400">{{ $template->user?->name ?? '—' }}</span>

                    <span class="hidden lg:inline">
                        <x-relative-time :value="$template->updated_at" class="text-xs text-gray-500 dark:text-gray-400" />
                    </span>

                    <x-form-template-actions-dropdown :template="$template" />
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="document-duplicate" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhum template encontrado
                        @else
                            Nenhum template cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Abra um formulário e use "Salvar como template" para começar a montar sua biblioteca.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            @include('admin.form-templates.partials.pagination')
        </div>
    @endif

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('form-templates-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
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
