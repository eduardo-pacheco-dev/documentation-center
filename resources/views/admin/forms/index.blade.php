<x-layouts.admin title="Formulários">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Formulários
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preencha e acompanhe os formulários de projeto (AFL).</p>
        </div>

        <a
            href="{{ route('admin.forms.create') }}"
            data-modal-open="form-create-modal"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo formulário
        </a>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        <form method="GET" action="{{ route('admin.forms.index') }}" class="flex flex-wrap items-center gap-2" data-search-form>
            @foreach (['sort', 'direction', 'per_page', 'project_id'] as $param)
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
                    placeholder="Buscar por código ou nome..."
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

            @if ($search !== '' || $status !== '' || $projectId !== null)
                <a
                    href="{{ route('admin.forms.index') }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="project-filter" class="sr-only">Filtrar por projeto</label>
            <select
                id="project-filter"
                name="project_id"
                onchange="this.form.submit()"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-3 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="">Todos os projetos</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected($projectId === $project->id)>[{{ $project->code }}] {{ $project->name }}</option>
                @endforeach
            </select>

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select
                id="status-filter"
                name="status"
                onchange="this.form.submit()"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-3 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                @foreach (['' => 'Todos os status', 'Pendente' => 'Pendente', 'Em preenchimento' => 'Em preenchimento', 'Preenchido' => 'Preenchido', 'Aprovado' => 'Aprovado', 'Reprovado' => 'Reprovado'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="rounded-t-xl bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <x-sort-header column="code" label="Código" icon="clipboard-document-list" class="px-5 py-3" />
                        <x-sort-header column="name" label="Nome" class="px-5 py-3" />
                        <th class="px-5 py-3 font-medium">Projeto</th>
                        <x-sort-header column="type" label="Tipo" class="px-5 py-3" />
                        <x-sort-header column="status" label="Status" class="px-5 py-3" />
                        <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                        <th class="px-5 py-3 text-right font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($forms as $form)
                        <tr>
                            <td class="px-5 py-3">
                                <a
                                    href="{{ route('admin.forms.show', $form) }}"
                                    class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                >
                                    <x-icon name="clipboard-document-list" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    {{ $form->code }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $form->name }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">
                                @if ($form->project)
                                    <a href="{{ route('admin.projects.show', $form->project) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        [{{ $form->project->code }}] {{ $form->project->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $form->type }}</td>
                            <td class="whitespace-nowrap px-5 py-3"><x-form-status :status="$form->status" /></td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-relative-time :value="$form->updated_at" class="text-gray-500 dark:text-gray-400" />
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="relative inline-block text-left">
                                    <button
                                        type="button"
                                        data-dropdown-toggle
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                        class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                    >
                                        <span class="sr-only">Ações</span>
                                        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
                                    </button>

                                    <div
                                        data-dropdown-menu
                                        class="absolute right-0 z-10 mt-1 hidden w-40 rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:shadow-gray-950"
                                    >
                                        <a href="{{ route('admin.forms.show', $form) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                            <x-icon name="eye" class="h-4 w-4" />
                                            Visualizar
                                        </a>
                                        <a href="{{ route('admin.forms.edit', $form) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                            <x-icon name="pencil-square" class="h-4 w-4" />
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('admin.forms.destroy', $form) }}" data-confirm="Excluir formulário {{ $form->code }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                                                <x-icon name="trash" class="h-4 w-4" />
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="7">
                                <span class="inline-flex items-center gap-2">
                                    <x-icon name="clipboard-document-list" class="h-4 w-4" />
                                    @if ($search !== '' || $status !== '' || $projectId !== null)
                                        Nenhum formulário encontrado para os filtros informados.
                                    @else
                                        Nenhum formulário cadastrado até o momento.
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
            <form method="GET" action="{{ route('admin.forms.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'sort', 'direction', 'status', 'project_id'] as $param)
                    @if (request($param) !== null && request($param) !== '')
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

            <x-pagination :paginator="$forms" />
        </div>
    </div>

    <div
        id="form-create-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="form-create-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="form-create-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
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
                    class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
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
                        Criar formulário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
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