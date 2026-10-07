<x-layouts.admin title="Arquivos">
    @php
        $uploadFailed = collect($errors->getMessages())->keys()
            ->contains(fn (string $key): bool => str_starts_with($key, 'documents'));
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Arquivos</h1>
            <p class="mt-1 text-sm text-gray-500">Envie arquivos e gere links de download a partir deles.</p>
        </div>

        <button
            type="button"
            data-modal-open="upload-modal"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
        >
            <x-icon name="arrow-up-tray" class="h-4 w-4" />
            Enviar arquivos
        </button>
    </div>

    <form
        method="GET"
        action="{{ route('admin.files.index') }}"
        class="mt-6 flex flex-wrap items-center gap-2"
        data-search-form
    >
        @if (request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if (request('direction'))
            <input type="hidden" name="direction" value="{{ request('direction') }}">
        @endif

        <div class="relative w-full max-w-sm">
            <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Buscar por nome do arquivo..."
                class="block w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                data-search-input
            >
        </div>

        <button
            type="submit"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
        >
            Buscar
        </button>

        @if ($search !== '')
            <a href="{{ route('admin.files.index') }}" class="text-sm text-gray-500 hover:text-gray-900">
                Limpar
            </a>
        @endif
    </form>

    @if ($documents->isNotEmpty())
        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <x-sort-header column="original_name" label="Arquivo" icon="document-text" class="px-4 py-3" />
                        <x-sort-header column="size" label="Tamanho" icon="document" class="px-4 py-3" />
                        <x-sort-header column="short_links_count" label="Em links" icon="link" class="px-4 py-3" />
                        <x-sort-header column="created_at" label="Enviado em" icon="clock" class="px-4 py-3" />
                        <th class="px-4 py-3 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($documents as $document)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="max-w-[18rem] truncate text-gray-900">{{ $document->original_name }}</p>
                                @if ($document->uploaded_via_short_link_id !== null)
                                    <p class="text-xs text-gray-400">Recebido via link de upload</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ Number::fileSize($document->size) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $document->short_links_count }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $document->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="relative inline-block text-left">
                                    <button
                                        type="button"
                                        class="rounded-full p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                        data-dropdown-toggle
                                    >
                                        <span class="sr-only">Abrir menu de ações</span>
                                        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
                                    </button>

                                    <div
                                        class="absolute right-0 z-10 mt-1 hidden w-44 origin-top-right rounded-md bg-white py-1 text-left shadow-lg ring-1 ring-gray-900/5"
                                            role="menu"
                                            data-dropdown-menu
                                        >
                                            <button
                                                type="button"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                role="menuitem"
                                                data-rename-open
                                                data-document-id="{{ $document->getKey() }}"
                                                data-document-name="{{ $document->original_name }}"
                                            >
                                                <x-icon name="pencil-square" class="h-4 w-4 text-gray-400" />
                                                Renomear
                                            </button>

                                            <a
                                                href="{{ route('admin.links.index', ['document' => $document->getKey()]) }}"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                role="menuitem"
                                            >
                                                <x-icon name="link" class="h-4 w-4 text-gray-400" />
                                                Gerenciar links
                                            </a>

                                            <button
                                                type="submit"
                                                form="generate-link-{{ $document->getKey() }}"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                role="menuitem"
                                            >
                                                <x-icon name="plus" class="h-4 w-4 text-gray-400" />
                                                Criar link
                                            </button>

                                        <button
                                            type="submit"
                                            form="delete-document-{{ $document->getKey() }}"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50"
                                            role="menuitem"
                                            onclick="return confirm('Excluir {{ $document->original_name }}?')"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

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
        @endforeach

        <div class="pt-4">
            {{ $documents->links() }}
        </div>
    @else
        <p class="mt-6 text-sm text-gray-500">
            @if ($search !== '')
                Nenhum arquivo encontrado para "{{ $search }}".
                <a href="{{ route('admin.files.index') }}" class="font-medium text-gray-900 hover:underline">Limpar busca</a>
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

        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-5 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="upload-modal-title" class="text-base font-semibold text-gray-900">Enviar arquivos</h2>
                    <p class="mt-1 text-sm text-gray-500">Selecione até 10 arquivos de até 20 MB cada.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
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
                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200"
                >

                @foreach ($errors->getMessages() as $key => $messages)
                    @if (str_starts_with($key, 'documents'))
                        @foreach ($messages as $message)
                            <p class="text-sm text-red-600" data-modal-error>{{ $message }}</p>
                        @endforeach
                    @endif
                @endforeach

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
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

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-5 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="rename-modal-title" class="text-base font-semibold text-gray-900">Renomear arquivo</h2>
                    <p class="mt-1 text-sm text-gray-500">Defina um novo nome para o arquivo.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
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
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >

                @error('original_name')
                    <p class="text-sm text-red-600" data-modal-error>{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                    >
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
