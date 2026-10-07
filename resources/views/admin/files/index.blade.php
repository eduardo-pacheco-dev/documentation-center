<x-layouts.admin title="Arquivos">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
    @endphp

    @php
        $uploadFailed = collect($errors->getMessages())->keys()
            ->contains(fn (string $key): bool => str_starts_with($key, 'documents'));
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="document-text" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Arquivos
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Envie arquivos e gere links de download a partir deles.</p>
        </div>

        <button
            type="button"
            data-modal-open="upload-modal"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="arrow-up-tray" class="h-4 w-4" />
            Enviar arquivos
        </button>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form
            method="GET"
            action="{{ route('admin.files.index') }}"
            class="flex flex-wrap items-center gap-2"
            data-search-form
        >
            @if (request('view'))
                <input type="hidden" name="view" value="{{ request('view') }}">
            @endif
            @if (request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if (request('direction'))
                <input type="hidden" name="direction" value="{{ request('direction') }}">
            @endif

            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por nome do arquivo..."
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
                <a href="{{ route('admin.files.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    Limpar
                </a>
            @endif
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.files.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm {{ $view === $mode ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($documents->isNotEmpty())
        @if ($view === 'table')
            <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <x-sort-header column="original_name" label="Arquivo" icon="document-text" class="px-4 py-3" />
                        <x-sort-header column="size" label="Tamanho" icon="document" class="px-4 py-3" />
                        <x-sort-header column="short_links_count" label="Em links" icon="link" class="px-4 py-3" />
                        <x-sort-header column="created_at" label="Enviado em" icon="clock" class="px-4 py-3" />
                        <th class="px-4 py-3 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($documents as $document)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="max-w-[18rem] truncate text-gray-900 dark:text-gray-100">{{ $document->original_name }}</p>
                                @if ($document->uploaded_via_short_link_id !== null)
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Recebido via link de upload</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ Number::fileSize($document->size) }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $document->short_links_count }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $document->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-document-actions-dropdown :document="$document" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($documents as $document)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="document-text" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $document->original_name }}</p>
                                @if ($document->uploaded_via_short_link_id !== null)
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Recebido via link de upload</p>
                                @endif
                            </div>
                        </div>

                        <x-document-actions-dropdown :document="$document" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="document" class="h-3.5 w-3.5" />
                            {{ Number::fileSize($document->size) }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="link" class="h-3.5 w-3.5" />
                            {{ $document->short_links_count }}
                        </span>
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <x-icon name="clock" class="h-3.5 w-3.5" />
                        Enviado em {{ $document->created_at?->format('d/m/Y H:i') ?? '—' }}
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @foreach ($documents as $document)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="document-text" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $document->original_name }}</p>
                        @if ($document->uploaded_via_short_link_id !== null)
                            <p class="text-xs text-gray-400 dark:text-gray-500">Recebido via link de upload</p>
                        @endif
                    </div>

                    <div class="hidden items-center gap-2 text-xs text-gray-500 sm:flex dark:text-gray-400">
                        <span>{{ Number::fileSize($document->size) }}</span>
                        <span>{{ $document->short_links_count }}</span>
                    </div>

                    <span class="hidden text-xs text-gray-500 md:inline dark:text-gray-400">{{ $document->created_at?->format('d/m/Y H:i') ?? '—' }}</span>

                    <x-document-actions-dropdown :document="$document" />
                </div>
            @endforeach
        </div>
    @endif

        @foreach ($documents as $document)
            <form
                id="generate-link-{{ $document->getKey() }}"
                method="POST"
                action="{{ route('admin.files.generate-link') }}"
                class="hidden"
            >
                @csrf
                <input type="hidden" name="document_ids[]" value="{{ $document->getKey() }}">
            </form>

            <form
                id="delete-document-{{ $document->getKey() }}"
                method="POST"
                action="{{ route('admin.files.destroy', $document) }}"
                class="hidden"
            >
                @csrf
                @method('DELETE')
            </form>

            <div
                id="links-modal-{{ $document->getKey() }}"
                class="fixed inset-0 z-50 hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="links-modal-title-{{ $document->getKey() }}"
            >
                <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

                <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white dark:bg-gray-900 p-5 shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 id="links-modal-title-{{ $document->getKey() }}" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Gerenciar links
                            </h2>
                            <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">{{ $document->original_name }}</p>
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

                    <ul class="mt-4 max-h-72 space-y-2 overflow-y-auto">
                        @forelse ($document->shortLinks as $shortLink)
                            <li class="flex items-start justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-800 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1.5 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <span class="truncate">{{ $shortLink->title }}</span>
                                        @if ($shortLink->type->value === 'upload')
                                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                                Upload
                                            </span>
                                        @else
                                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                Download
                                            </span>
                                        @endif
                                    </p>

                                    <p class="mt-0.5 truncate font-mono text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->url }}</p>

                                    @if ($shortLink->is_active === false)
                                        <span class="mt-1 inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                            Desativado
                                        </span>
                                    @elseif ($shortLink->isExpired())
                                        <span class="mt-1 inline-flex items-center rounded-full bg-red-100 dark:bg-red-500/15 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">
                                            Expirado
                                        </span>
                                    @elseif ($shortLink->hasReachedAccessLimit())
                                        <span class="mt-1 inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/15 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
                                            Limite atingido
                                        </span>
                                    @else
                                        <span class="mt-1 inline-flex items-center rounded-full bg-green-100 dark:bg-green-500/15 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">
                                            Ativo
                                        </span>
                                    @endif
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <a
                                        href="{{ route('admin.links.edit', $shortLink) }}"
                                        class="rounded-md px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                    >
                                        Abrir
                                    </a>

                                    @if ($shortLink->type->value !== 'upload')
                                        <form method="POST" action="{{ route('admin.links.documents.destroy', [$shortLink, $document]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-md px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15"
                                                onclick="return confirm('Remover este arquivo do link?')"
                                            >
                                                Remover
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                Este arquivo ainda não está em nenhum link.
                                <div class="mt-3">
                                    <button
                                        type="submit"
                                        form="generate-link-{{ $document->getKey() }}"
                                        class="rounded-md bg-gray-900 dark:bg-white px-3 py-1.5 text-xs font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                                    >
                                        Criar link com este arquivo
                                    </button>
                                </div>
                            </li>
                        @endforelse
                    </ul>

                    <div class="mt-4 flex justify-end">
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            data-modal-close
                        >
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="pt-4">
            {{ $documents->links() }}
        </div>
    @else
        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
            @if ($search !== '')
                Nenhum arquivo encontrado para "{{ $search }}".
                <a href="{{ route('admin.files.index') }}" class="font-medium text-gray-900 dark:text-gray-100 hover:underline">Limpar busca</a>
            @else
                Nenhum arquivo enviado ainda. Use o botão "Enviar arquivos" para começar.
            @endif
        </p>
    @endif

    <div
        id="upload-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="upload-modal-title"
        @if ($uploadFailed) data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white dark:bg-gray-900 p-5 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="upload-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Enviar arquivos</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selecione até 10 arquivos de até 20 MB cada.</p>
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

            <form
                method="POST"
                action="{{ route('admin.files.store') }}"
                enctype="multipart/form-data"
                class="mt-4 space-y-3"
            >
                @csrf

                <input
                    id="documents"
                    type="file"
                    name="documents[]"
                    multiple
                    required
                    class="block w-full text-sm text-gray-600 dark:text-gray-300 dark:border dark:border-gray-700 dark:bg-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 dark:file:bg-gray-800 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 dark:file:text-gray-200 hover:file:bg-gray-200 dark:hover:file:bg-gray-700"
                >

                @foreach ($errors->getMessages() as $key => $messages)
                    @if (str_starts_with($key, 'documents'))
                        @foreach ($messages as $message)
                            <p class="text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                        @endforeach
                    @endif
                @endforeach

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        Enviar arquivos
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="rename-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="rename-modal-title"
        data-action-template="{{ route('admin.files.update', '__ID__') }}"
        @if ($errors->has('original_name') && old('rename_document')) data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white dark:bg-gray-900 p-5 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="rename-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Renomear arquivo</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Defina um novo nome para o arquivo.</p>
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

            <form id="rename-form" method="POST" action="" class="mt-4 space-y-3">
                @csrf
                @method('PUT')

                <input type="hidden" name="rename_document" value="{{ old('rename_document') }}">

                <input
                    type="text"
                    name="original_name"
                    value="{{ old('original_name') }}"
                    required
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >

                @error('original_name')
                    <p class="text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        Salvar
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

            const savedView = localStorage.getItem('files-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('files-view', link.dataset.viewToggle);
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

        document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                closeDropdowns();
                openModal(document.getElementById(trigger.dataset.modalOpen));
            });
        });

        document.addEventListener('click', (event) => {
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

        const uploadModal = document.getElementById('upload-modal');
        if (uploadModal?.hasAttribute('data-open')) {
            openModal(uploadModal);
        }

        const renameModal = document.getElementById('rename-modal');
        const renameForm = document.getElementById('rename-form');

        const openRenameModal = (documentId) => {
            renameForm.action = renameModal.dataset.actionTemplate.replace('__ID__', documentId);
            openModal(renameModal);
        };

        document.querySelectorAll('[data-rename-open]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                renameForm.elements.original_name.value = trigger.dataset.documentName;
                renameForm.elements.rename_document.value = trigger.dataset.documentId;
                closeDropdowns();
                openRenameModal(trigger.dataset.documentId);
            });
        });

        if (renameModal?.hasAttribute('data-open') && renameForm.elements.rename_document.value) {
            openRenameModal(renameForm.elements.rename_document.value);
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
