<x-layouts.admin title="Formulários">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $statusOptions = [
            '' => 'Todos',
            'Pendente' => 'Pendente',
            'Em preenchimento' => 'Em preenchimento',
            'Preenchido' => 'Preenchido',
            'Aprovado' => 'Aprovado',
            'Reprovado' => 'Reprovado',
        ];

        $statusUrl = fn (string $value): string => route('admin.forms.index', array_merge(request()->query(), ['status' => $value]));
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/20">
                <x-icon name="document-check" class="h-6 w-6" />
            </span>
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Formulários</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Preencha e acompanhe os formulários de projeto (AFL).</p>
            </div>
        </div>

        <a
            href="{{ route('admin.forms.create') }}"
            data-modal-open="form-create-modal"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 dark:focus:ring-offset-gray-950"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo formulário
        </a>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form method="GET" action="{{ route('admin.forms.index') }}" class="flex flex-wrap items-center gap-2" data-search-form>
            @foreach (['view', 'sort', 'direction', 'per_page'] as $param)
                @if (request($param) !== null && request($param) !== '')
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="relative w-full sm:max-w-sm">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por código ou nome..."
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-12 text-sm shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-950 dark:placeholder:text-gray-500"
                    data-search-input
                >
                <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 select-none items-center gap-0.5 rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-[10px] font-medium text-gray-400 sm:inline-flex dark:border-gray-700 dark:bg-gray-900 dark:text-gray-500">/</kbd>
            </div>

            <button
                type="submit"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-800"
            >
                Buscar
            </button>

            @if ($search !== '' || $status !== '')
                <a
                    href="{{ route('admin.forms.index', array_filter(['view' => $view])) }}"
                    class="rounded-lg px-2 py-2 text-sm text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Limpar filtros
                </a>
            @endif
        </form>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-3 dark:border-gray-800">
            <nav class="-mx-1 flex items-center gap-1 overflow-x-auto px-1" aria-label="Filtrar por status">
                @foreach ($statusOptions as $value => $label)
                    <a
                        href="{{ $statusUrl($value) }}"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition {{ $status === $value ? 'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="inline-flex shrink-0 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-950" role="group" aria-label="Modo de visualização">
                @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                    <a
                        href="{{ route('admin.forms.index', array_merge($viewQuery, ['view' => $mode])) }}"
                        data-view-toggle="{{ $mode }}"
                        aria-pressed="{{ $view === $mode ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-medium transition {{ $view === $mode ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }}"
                    >
                        <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-3.5 w-3.5" />
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if ($view === 'table')
    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <x-sort-header column="code" label="Código" icon="clipboard-document-list" class="px-5 py-3" />
                        <x-sort-header column="name" label="Nome" class="px-5 py-3" />
                        <th class="hidden px-5 py-3 font-medium md:table-cell">Projeto</th>
                        <x-sort-header column="type" label="Tipo" class="hidden px-5 py-3 lg:table-cell" />
                        <x-sort-header column="status" label="Status" class="px-5 py-3" />
                        <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="hidden px-5 py-3 sm:table-cell" />
                        <th class="px-5 py-3 text-right font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($forms as $form)
                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <a
                                    href="{{ route('admin.forms.show', $form) }}"
                                    class="inline-flex items-center gap-2 font-mono text-xs font-medium text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                >
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                        <x-icon name="clipboard-document-list" class="h-4 w-4" />
                                    </span>
                                    {{ $form->code }}
                                </a>
                            </td>
                            <td class="max-w-xs px-5 py-3.5">
                                <span class="block truncate font-medium text-gray-900 dark:text-gray-100">{{ $form->name }}</span>
                                <span class="mt-0.5 block truncate text-xs text-gray-500 dark:text-gray-400 md:hidden">
                                    {{ $form->project ? '['.$form->project->code.'] '.$form->project->name : $form->type }}
                                </span>
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-3.5 text-gray-500 dark:text-gray-400 md:table-cell">
                                @if ($form->project)
                                    <a href="{{ route('admin.projects.show', $form->project) }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">
                                        [{{ $form->project->code }}] {{ $form->project->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-3.5 text-gray-500 lg:table-cell dark:text-gray-400">{{ $form->type }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5"><x-form-status :status="$form->status" /></td>
                            <td class="hidden whitespace-nowrap px-5 py-3.5 sm:table-cell">
                                <x-relative-time :value="$form->updated_at" class="text-gray-500 dark:text-gray-400" />
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <x-form-actions-dropdown :form="$form" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-12" colspan="7">
                                <div class="flex flex-col items-center text-center">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if ($search !== '' || $status !== '')
                                            Nenhum formulário encontrado
                                        @else
                                            Nenhum formulário cadastrado
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @if ($search !== '' || $status !== '')
                                            Ajuste os filtros ou limpe a busca para ver todos os registros.
                                        @else
                                            Crie o primeiro formulário para começar a acompanhar as avaliações.
                                        @endif
                                    </p>
                                    @if ($search !== '' || $status !== '')
                                        <a href="{{ route('admin.forms.index', array_filter(['view' => $view])) }}" class="mt-4 text-sm font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">
                                            Limpar filtros
                                        </a>
                                    @else
                                        <a href="{{ route('admin.forms.create') }}" data-modal-open="form-create-modal" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                                            <x-icon name="plus" class="h-4 w-4" />
                                            Novo formulário
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.forms.partials.pagination-bar')
    </div>
    @elseif ($view === 'cards')
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($forms as $form)
            <div class="group flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:shadow-gray-950">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500/10 to-violet-500/10 text-indigo-600 transition group-hover:from-indigo-500 group-hover:to-violet-600 group-hover:text-white dark:from-indigo-500/20 dark:to-violet-500/20 dark:text-indigo-400">
                            <x-icon name="clipboard-document-list" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <a href="{{ route('admin.forms.show', $form) }}" class="block truncate font-mono text-xs font-medium text-gray-500 transition hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                                {{ $form->code }}
                            </a>
                            <p class="mt-0.5 truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $form->name }}</p>
                        </div>
                    </div>

                    <x-form-actions-dropdown :form="$form" />
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <x-form-status :status="$form->status" />
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $form->type }}</span>
                </div>

                @if ($form->project)
                    <a href="{{ route('admin.projects.show', $form->project) }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">
                        <x-icon name="briefcase" class="h-3.5 w-3.5" />
                        <span class="truncate">[{{ $form->project->code }}] {{ $form->project->name }}</span>
                    </a>
                @endif

                <div class="mt-4 flex items-center gap-1.5 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <x-icon name="clock" class="h-3.5 w-3.5" />
                    <x-relative-time :value="$form->updated_at" />
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <x-icon name="clipboard-document-list" class="h-6 w-6" />
                </span>
                <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                    @if ($search !== '' || $status !== '')
                        Nenhum formulário encontrado
                    @else
                        Nenhum formulário cadastrado
                    @endif
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($search !== '' || $status !== '')
                        Ajuste os filtros ou limpe a busca para ver todos os registros.
                    @else
                        Crie o primeiro formulário para começar a acompanhar as avaliações.
                    @endif
                </p>
                @if ($search !== '' || $status !== '')
                    <a href="{{ route('admin.forms.index', array_filter(['view' => $view])) }}" class="mt-4 text-sm font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">
                        Limpar filtros
                    </a>
                @else
                    <a href="{{ route('admin.forms.create') }}" data-modal-open="form-create-modal" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                        <x-icon name="plus" class="h-4 w-4" />
                        Novo formulário
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @include('admin.forms.partials.pagination-bar')
    @else
    <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
        @forelse ($forms as $form)
            <div class="flex items-center gap-4 px-5 py-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="clipboard-document-list" class="h-4 w-4" />
                </span>

                <div class="min-w-0 flex-1">
                    <a href="{{ route('admin.forms.show', $form) }}" class="block truncate text-sm font-medium text-gray-900 transition hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $form->code }}</span>
                        {{ $form->name }}
                    </a>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $form->project ? '['.$form->project->code.'] '.$form->project->name : $form->type }}
                    </p>
                </div>

                <div class="hidden shrink-0 sm:block">
                    <x-form-status :status="$form->status" />
                </div>

                <span class="hidden whitespace-nowrap text-sm text-gray-500 md:inline dark:text-gray-400">{{ $form->type }}</span>

                <span class="hidden shrink-0 lg:inline">
                    <x-relative-time :value="$form->updated_at" class="text-xs text-gray-500 dark:text-gray-400" />
                </span>

                <div class="shrink-0">
                    <x-form-actions-dropdown :form="$form" />
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center px-5 py-12 text-center">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <x-icon name="clipboard-document-list" class="h-6 w-6" />
                </span>
                <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                    @if ($search !== '' || $status !== '')
                        Nenhum formulário encontrado
                    @else
                        Nenhum formulário cadastrado
                    @endif
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($search !== '' || $status !== '')
                        Ajuste os filtros ou limpe a busca para ver todos os registros.
                    @else
                        Crie o primeiro formulário para começar a acompanhar as avaliações.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @include('admin.forms.partials.pagination-bar')
    @endif

    <div
        id="form-create-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="form-create-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/20">
                        <x-icon name="clipboard-document-list" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 id="form-create-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Novo formulário
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Informe os dados do formulário.</p>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            @if ($errors->any() && old('modal') === 'create')
                <div
                    data-modal-error
                    class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">Não foi possível criar o formulário. Corrija os itens destacados e tente novamente.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.forms.store') }}" class="mt-5 space-y-6">
                @csrf
                <input type="hidden" name="modal" value="create">

                @include('admin.forms.partials.form', [
                    'form' => null,
                    'projects' => $projects,
                    'types' => $types,
                    'statuses' => $statuses,
                ])

                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 dark:focus:ring-offset-gray-950"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Criar formulário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const viewParams = new URLSearchParams(window.location.search);

            if (!viewParams.has('view')) {
                const savedView = localStorage.getItem('forms-view');

                if (['table', 'cards', 'compact'].includes(savedView)) {
                    viewParams.set('view', savedView);
                    window.location.replace(window.location.pathname + '?' + viewParams.toString());
                    return;
                }
            }

            document.querySelectorAll('[data-view-toggle]').forEach((link) => {
                link.addEventListener('click', () => {
                    localStorage.setItem('forms-view', link.dataset.viewToggle);
                });
            });

            const searchInput = document.querySelector('[data-search-input]');
            const searchForm = document.querySelector('[data-search-form]');

            if (searchInput && searchForm) {
                let debounceTimer;

                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);

                    debounceTimer = setTimeout(() => {
                        sessionStorage.setItem('forms-search-focus', '1');
                        searchForm.submit();
                    }, 400);
                });

                if (sessionStorage.getItem('forms-search-focus') === '1') {
                    sessionStorage.removeItem('forms-search-focus');
                    searchInput.focus();
                    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                }
            }

            document.addEventListener('keydown', (event) => {
                const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

                if (event.key === '/' && !isTyping && searchInput) {
                    event.preventDefault();
                    searchInput.focus();
                }
            });

            const resetDropdown = (menu) => {
                menu.classList.add('hidden');
                menu.style.position = '';
                menu.style.top = '';
                menu.style.right = '';
                menu.style.left = '';
                menu.style.marginTop = '';
                menu.previousElementSibling.setAttribute('aria-expanded', 'false');
            };

            const closeDropdowns = (except) => {
                document.querySelectorAll('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
                    if (menu !== except) {
                        resetDropdown(menu);
                    }
                });
            };

            const positionDropdown = (menu, toggle) => {
                const rect = toggle.getBoundingClientRect();
                const gap = 4;

                menu.style.position = 'fixed';
                menu.style.marginTop = '0';
                menu.style.left = 'auto';

                const height = menu.offsetHeight;

                let top = rect.bottom + gap;

                if (top + height > window.innerHeight - 8) {
                    top = Math.max(8, rect.top - height - gap);
                }

                menu.style.right = Math.max(8, window.innerWidth - rect.right) + 'px';
                menu.style.top = top + 'px';
            };

            document.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-dropdown-toggle]');

                if (toggle) {
                    const menu = toggle.nextElementSibling;
                    const willOpen = menu.classList.contains('hidden');
                    closeDropdowns(willOpen ? menu : null);

                    if (willOpen) {
                        menu.classList.remove('hidden');
                        positionDropdown(menu, toggle);
                    } else {
                        resetDropdown(menu);
                    }

                    toggle.setAttribute('aria-expanded', String(willOpen));
                    return;
                }

                if (!event.target.closest('[data-dropdown-menu]')) {
                    closeDropdowns();
                }
            });

            window.addEventListener('scroll', () => closeDropdowns(), true);
            window.addEventListener('resize', () => closeDropdowns());

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
                    closeDropdowns();
                    document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
                }
            });

            document.querySelectorAll('[role="dialog"][data-open]').forEach(openModal);
        })();
    </script>
</x-layouts.admin>
