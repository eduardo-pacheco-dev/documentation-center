<div
    id="link-edit-modal-{{ $shortLink->getKey() }}"
    data-link-edit-modal
    class="fixed inset-0 z-50 hidden flex overflow-y-auto p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="link-edit-modal-title-{{ $shortLink->getKey() }}"
    @if (($errors->any() && old('modal') === 'edit' && (int) old('link_id') === $shortLink->getKey()) || (string) session('open_link_edit') === (string) $shortLink->getKey()) data-open @endif
>
    <div class="fixed inset-0 bg-gray-950/50" data-modal-close></div>

    <div class="relative m-auto w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-gray-900">
        <header class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 id="link-edit-modal-title-{{ $shortLink->getKey() }}" class="flex items-center gap-2 text-base font-semibold">
                    <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Editar link
                </h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Ajuste as configurações de acesso e os documentos do link.</p>
            </div>

            <button
                type="button"
                data-modal-close
                class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </header>

        <div x-data="{ type: '{{ old('type', $shortLink->type->value) }}' }">
            <form
                id="link-edit-form-{{ $shortLink->getKey() }}"
                method="POST"
                action="{{ route('admin.links.update', $shortLink) }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="modal" value="edit">
                <input type="hidden" name="link_id" value="{{ $shortLink->getKey() }}">

                <div class="px-5 py-4">
                    @include('admin.links.partials.form-fields', ['shortLink' => $shortLink])
                </div>
            </form>

            <section
                x-show="type === 'download'"
                @if (old('type', $shortLink->type->value) !== 'download') style="display: none" @endif
                class="space-y-3 border-t border-gray-200 px-5 py-4 dark:border-gray-800"
            >
                <div>
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="document" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        Arquivos para download
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Anexe os arquivos que devem ficar disponíveis para download neste link.</p>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.links.documents.store', $shortLink) }}"
                    enctype="multipart/form-data"
                    class="space-y-3"
                >
                    @csrf
                    <input type="hidden" name="modal" value="edit">
                    <input type="hidden" name="link_id" value="{{ $shortLink->getKey() }}">
                    <input
                        type="file"
                        name="documents[]"
                        multiple
                        class="block w-full text-sm text-gray-600 dark:text-gray-300 dark:border dark:border-gray-700 dark:bg-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 dark:file:bg-gray-800 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 dark:file:text-gray-200 hover:file:bg-gray-200 dark:hover:file:bg-gray-700"
                    >
                    @error('documents')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('documents.*')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <x-icon name="arrow-up-tray" class="h-4 w-4" />
                        Adicionar arquivos
                    </button>
                </form>

                @if ($shortLink->documents->isNotEmpty())
                    <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                        @foreach ($shortLink->documents as $document)
                            <li class="flex items-center justify-between gap-3 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm text-gray-700 dark:text-gray-200">{{ $document->original_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ Number::fileSize($document->size) }}</p>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('admin.links.documents.destroy', [$shortLink, $document]) }}"
                                    data-confirm="Remover {{ $document->original_name }} deste link?"
                                    data-confirm-title="Remover arquivo"
                                    data-confirm-label="Remover"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="modal" value="edit">
                                    <input type="hidden" name="link_id" value="{{ $shortLink->getKey() }}">
                                    <button
                                        type="submit"
                                        class="shrink-0 rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
                                    >
                                        Remover
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum arquivo anexado a este link ainda.</p>
                @endif
            </section>

            <footer class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                <button
                    type="button"
                    data-modal-close
                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    form="link-edit-form-{{ $shortLink->getKey() }}"
                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                >
                    <x-icon name="check" class="h-4 w-4" />
                    Salvar alterações
                </button>
            </footer>
        </div>
    </div>
</div>
