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
        <form method="POST" action="{{ route('admin.files.generate-link') }}" class="mt-4">
            @csrf

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="w-10 px-4 py-3">
                                <input
                                    type="checkbox"
                                    onclick="document.querySelectorAll('.select-file').forEach((el) => { el.checked = this.checked; })"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                >
                            </th>
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
                                    <input
                                        type="checkbox"
                                        name="document_ids[]"
                                        value="{{ $document->getKey() }}"
                                        class="select-file rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </td>
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
                                    <button
                                        type="button"
                                        form="delete-document-{{ $document->getKey() }}"
                                        class="text-sm font-medium text-red-600 hover:underline"
                                        onclick="if (! confirm('Excluir {{ $document->original_name }}?')) event.preventDefault()"
                                    >
                                        Excluir
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 px-4 py-4">
                    <input
                        type="text"
                        name="title"
                        placeholder="Título do link (opcional)"
                        class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                    >
                        Gerar link de download
                    </button>
                </div>
            </div>
        </form>

        @foreach ($documents as $document)
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

    <script>
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
