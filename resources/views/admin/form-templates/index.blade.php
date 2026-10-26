<x-layouts.admin title="Templates de formulário">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $hasFilters = $search !== '' || $type !== null;

        $questionCount = fn ($template) => collect($template->steps)->sum(fn ($step) => count($step['fields'] ?? []));

        $primaryButton = 'inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white transition hover:bg-indigo-500';
        $secondaryButton = 'inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800';
        $selectClass = 'rounded-md border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
        $panelClass = 'rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';

        $pageStats = [
            ['label' => 'Templates', 'value' => $stats['total'], 'icon' => 'document-duplicate', 'color' => 'text-indigo-600 dark:text-indigo-400'],
            ['label' => 'Tipos', 'value' => $stats['types'], 'icon' => 'rectangle-stack', 'color' => 'text-sky-600 dark:text-sky-400'],
            ['label' => 'Perguntas', 'value' => $stats['questions'], 'icon' => 'clipboard-document-list', 'color' => 'text-amber-600 dark:text-amber-400'],
            ['label' => 'Criadores', 'value' => $stats['contributors'], 'icon' => 'users', 'color' => 'text-emerald-600 dark:text-emerald-400'],
        ];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="document-duplicate" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Templates de formulário
                <a
                    href="{{ route('admin.forms.settings.edit') }}"
                    title="Configurações do módulo"
                    aria-label="Configurações do módulo de formulários"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-indigo-400"
                >
                    <x-icon name="cog-6-tooth" class="h-4 w-4" />
                </a>
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Estruturas reutilizáveis compartilhadas com todos os usuários.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.form-templates.create') }}" class="{{ $primaryButton }}">
                <x-icon name="plus" class="h-4 w-4" />
                Novo template
            </a>

            <a href="{{ route('admin.forms.index') }}" class="{{ $secondaryButton }}">
                <x-icon name="document-text" class="h-4 w-4" />
                Ir para formulários
            </a>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 border-y border-gray-200 py-3 dark:border-gray-800">
        @foreach ($pageStats as $stat)
            <div class="flex items-center gap-2">
                <x-icon :name="$stat['icon']" class="h-4 w-4 {{ $stat['color'] }}" />
                <span class="text-lg font-semibold leading-none text-gray-900 dark:text-gray-100">{{ $stat['value'] }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</span>
            </div>
        @endforeach
    </div>

    <section class="mt-4 {{ $panelClass }} p-3">
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
                    class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
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

        <div class="inline-flex rounded-md border border-gray-300 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.form-templates.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 text-sm font-medium transition {{ $view === $mode ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-100' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($view === 'table')
        <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <x-sort-header column="name" label="Nome" icon="document-duplicate" default="name" class="whitespace-nowrap px-4 py-2.5" />
                            <x-sort-header column="type" label="Tipo" class="whitespace-nowrap px-4 py-2.5" />
                            <th class="whitespace-nowrap px-4 py-2.5 font-medium">Perguntas</th>
                            <th class="whitespace-nowrap px-4 py-2.5 font-medium">Criado por</th>
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="whitespace-nowrap px-4 py-2.5" />
                            <th class="whitespace-nowrap px-4 py-2.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($templates as $template)
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="max-w-[20rem] px-4 py-3">
                                    @can('update', $template)
                                        <a
                                            href="{{ route('admin.form-templates.edit', $template) }}"
                                            class="block truncate font-medium text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                        >
                                            {{ $template->name }}
                                        </a>
                                    @else
                                        <span class="block truncate font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</span>
                                    @endcan
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($template->type)
                                        <span class="inline-flex items-center rounded bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                            {{ $template->type }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @php $questions = $questionCount($template); @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <x-icon name="clipboard-document-list" class="h-3.5 w-3.5" />
                                        {{ $questions }} {{ $questions === 1 ? 'pergunta' : 'perguntas' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500 dark:text-gray-400">{{ $template->user?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <x-relative-time :value="$template->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <x-form-template-actions-dropdown :template="$template" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-14 text-center" colspan="6">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
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

            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                @include('admin.form-templates.partials.pagination')
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($templates as $template)
                <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-4 transition hover:border-indigo-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="document-duplicate" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                @can('update', $template)
                                    <a href="{{ route('admin.form-templates.edit', $template) }}" class="block truncate font-medium text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                        {{ $template->name }}
                                    </a>
                                @else
                                    <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
                                @endcan
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $template->type ?: 'Sem tipo' }}</p>
                            </div>
                        </div>

                        <x-form-template-actions-dropdown :template="$template" />
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @php $questions = $questionCount($template); @endphp
                        <span class="inline-flex items-center gap-1.5 rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <x-icon name="clipboard-document-list" class="h-3.5 w-3.5" />
                            {{ $questions }} {{ $questions === 1 ? 'pergunta' : 'perguntas' }}
                        </span>
                        @if ($template->type)
                            <span class="inline-flex items-center rounded bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                {{ $template->type }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
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
                <div class="col-span-full rounded-lg border border-dashed border-gray-300 bg-white px-4 py-12 text-center dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
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
        <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($templates as $template)
                <div class="flex items-center gap-3 px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="document-duplicate" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        @can('update', $template)
                            <a href="{{ route('admin.form-templates.edit', $template) }}" class="block truncate font-medium text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                {{ $template->name }}
                            </a>
                        @else
                            <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
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
                <div class="px-4 py-14 text-center">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
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
