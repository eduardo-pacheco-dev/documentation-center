<x-layouts.admin title="Lixeira">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lixeira</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Itens excluídos ficam aqui até serem restaurados ou removidos em definitivo.
            </p>
        </div>

        @if ($items->isNotEmpty())
            <form method="POST" action="{{ route('admin.trash.destroy') }}">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    data-confirm="Esvaziar a lixeira? Todos os itens serão excluídos definitivamente."
                    class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-500/15"
                >
                    Esvaziar lixeira
                </button>
            </form>
        @endif
    </div>

    @if ($items->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-gray-900">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <x-icon name="trash" class="h-7 w-7" />
            </span>
            <h2 class="mt-4 text-base font-semibold text-gray-900 dark:text-gray-100">A lixeira está vazia</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Arquivos e pastas excluídos aparecem aqui.
            </p>
        </div>
    @else
        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($items as $item)
                    @php
                        $isFolder = $item['type'] === 'folder';
                        $trashedModel = $item['model'];
                    @endphp

                    <li class="group flex flex-wrap items-center gap-3 px-4 py-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        @if ($isFolder)
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                <x-icon name="folder" class="h-5 w-5" />
                            </span>
                        @else
                            <x-file-type-icon :name="$trashedModel->original_name" class="shrink-0" />
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $isFolder ? $trashedModel->name : $trashedModel->original_name }}">
                                {{ $isFolder ? $trashedModel->name : $trashedModel->original_name }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ $isFolder ? 'Pasta' : 'Arquivo' }}
                                · excluído <x-relative-time :value="$trashedModel->deleted_at" />
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1.5">
                            <form
                                method="POST"
                                action="{{ route($isFolder ? 'admin.trash.folders.restore' : 'admin.trash.documents.restore', $trashedModel) }}"
                            >
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                                >
                                    Restaurar
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route($isFolder ? 'admin.trash.folders.force-destroy' : 'admin.trash.documents.force-destroy', $trashedModel) }}"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    data-confirm="Excluir {{ $isFolder ? 'a pasta' : 'o arquivo' }} {{ $isFolder ? $trashedModel->name : $trashedModel->original_name }} definitivamente? Não será possível recuperar."
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-500/15"
                                >
                                    Excluir definitivamente
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <script>
        document.addEventListener('click', (event) => {
            const confirmation = event.target.closest('[data-confirm]');

            if (confirmation && !window.confirm(confirmation.dataset.confirm)) {
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);
    </script>
</x-layouts.admin>
