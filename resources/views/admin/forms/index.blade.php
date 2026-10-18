<x-layouts.admin title="Formulários">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $hasFilters = $search !== '' || $status !== '';

        $secondaryButton = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
        $selectClass = 'rounded-xl border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Operações</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="document-check" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Formulários
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preencha e acompanhe os formulários de projeto (AFL).</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.forms.create') }}"
                data-modal-open="form-create-modal"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Novo formulário
            </a>
        </div>
    </div>

    @php
        $formsOnPage = $forms->getCollection();
        $pendingOnPage = $formsOnPage->where('status', 'Pendente')->count();
        $fillingOnPage = $formsOnPage->where('status', 'Em preenchimento')->count();
        $approvedOnPage = $formsOnPage->whereIn('status', ['Preenchido', 'Aprovado'])->count();
    @endphp

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="clipboard-document-list" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $forms->total() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Formulários encontrados</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="clock" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $pendingOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pendentes nesta página</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-lg hover:shadow-sky-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-sky-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <x-icon name="pencil-square" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $fillingOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em preenchimento nesta página</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $approvedOnPage }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preenchidos nesta página</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            method="GET"
            action="{{ route('admin.forms.index') }}"
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
                    placeholder="Buscar por código ou nome..."
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
                    href="{{ route('admin.forms.index', array_filter(['view' => $view])) }}"
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select id="status-filter" name="status" onchange="this.form.submit()" class="{{ $selectClass }}">
                @foreach (['' => 'Todos os status'] + $statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $forms->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $forms->total() }}</span>
            formulários
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.forms.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
                            <x-sort-header column="code" label="Código" icon="clipboard-document-list" class="px-5 py-3.5" />
                            <x-sort-header column="name" label="Nome" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 font-medium">Projeto</th>
                            <x-sort-header column="type" label="Tipo" class="px-5 py-3.5" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3.5" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($forms as $form)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                            <x-icon name="clipboard-document-list" class="h-4 w-4" />
                                        </span>
                                        <a
                                            href="{{ route('admin.forms.show', $form) }}"
                                            class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                        >
                                            {{ $form->code }}
                                        </a>
                                    </div>
                                </td>
                                <td class="max-w-xs px-5 py-4">
                                    <span class="block truncate font-medium text-gray-900 dark:text-gray-100">{{ $form->name }}</span>
                                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $form->type }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">
                                    @if ($form->project)
                                        <a href="{{ route('admin.projects.show', $form->project) }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">
                                            [{{ $form->project->code }}] {{ $form->project->name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $form->type }}</td>
                                <td class="px-5 py-4"><x-form-status :status="$form->status" /></td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-relative-time :value="$form->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-form-actions-dropdown :form="$form" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-16 text-center" colspan="7">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if ($hasFilters)
                                            Nenhum formulário encontrado
                                        @else
                                            Nenhum formulário cadastrado
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if ($hasFilters)
                                            Ajuste a busca ou os filtros para ver mais resultados.
                                        @else
                                            Crie o primeiro formulário para começar.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('admin.forms.partials.pagination-bar', ['bordered' => true])
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($forms as $form)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="clipboard-document-list" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.forms.show', $form) }}" class="block truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $form->code }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $form->name }}</p>
                            </div>
                        </div>

                        <x-form-actions-dropdown :form="$form" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ $form->type }}
                        </span>
                    </div>

                    @if ($form->project)
                        <a href="{{ route('admin.projects.show', $form->project) }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">
                            <x-icon name="briefcase" class="h-3.5 w-3.5" />
                            <span class="truncate">[{{ $form->project->code }}] {{ $form->project->name }}</span>
                        </a>
                    @endif

                    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3.5 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <x-form-status :status="$form->status" />
                        <x-relative-time :value="$form->updated_at" />
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center dark:border-gray-700 dark:bg-gray-900">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhum formulário encontrado
                        @else
                            Nenhum formulário cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Crie o primeiro formulário para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        @include('admin.forms.partials.pagination-bar', ['bordered' => false])
    @else
        <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @forelse ($forms as $form)
                <div class="flex items-center gap-4 border-b border-gray-100 px-5 py-3.5 transition last:border-b-0 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="clipboard-document-list" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.forms.show', $form) }}" class="truncate font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $form->code }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $form->name }}{{ $form->project ? ' · '.$form->project->name : '' }}
                        </p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 md:inline dark:text-gray-400">{{ $form->type }}</span>

                    <div class="hidden sm:block">
                        <x-form-status :status="$form->status" />
                    </div>

                    <x-form-actions-dropdown :form="$form" />
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if ($hasFilters)
                            Nenhum formulário encontrado
                        @else
                            Nenhum formulário cadastrado
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($hasFilters)
                            Ajuste a busca ou os filtros para ver mais resultados.
                        @else
                            Crie o primeiro formulário para começar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        @include('admin.forms.partials.pagination-bar', ['bordered' => false])
    @endif

    <div
        id="form-create-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="form-create-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="form-create-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Novo formulário
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informe os dados do formulário.</p>
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

            @if ($errors->any() && old('modal') === 'create')
                <div
                    data-modal-error
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
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
                        class="rounded-2xl px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
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
