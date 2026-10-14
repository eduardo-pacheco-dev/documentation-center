@php
    $forceOpen = $forceOpen ?? false;
    $hasErrors = $errors->any()
        && old('modal') === 'edit'
        && (string) old('editing_item') === (string) $catalogItem->getKey();
    $editModalOpen = $forceOpen || $hasErrors;
@endphp

<div
    id="catalog-item-edit-modal-{{ $catalogItem->getKey() }}"
    data-catalog-edit-modal
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="catalog-item-edit-modal-title-{{ $catalogItem->getKey() }}"
    @if ($editModalOpen) data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="catalog-item-edit-modal-title-{{ $catalogItem->getKey() }}" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Editar item
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $catalogItem->name }}</p>
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

        @if ($hasErrors)
            <div
                data-modal-error
                class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                <p class="font-medium">Não foi possível salvar o item. Corrija os campos destacados e tente novamente.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.catalog.update', $catalogItem) }}" class="mt-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="modal" value="edit">
            <input type="hidden" name="editing_item" value="{{ $catalogItem->getKey() }}">

            <div class="mb-5">
                <div class="flex items-center justify-between text-xs font-medium text-gray-500 dark:text-gray-400">
                    <span data-wizard-label>Identificação</span>
                    <span data-wizard-counter>Passo 1 de 3</span>
                </div>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                    <div data-wizard-progress class="h-full w-1/3 rounded-full bg-indigo-500 transition-all duration-200"></div>
                </div>
            </div>

            @include('admin.catalog.partials.form', ['catalogItem' => $catalogItem, 'types' => \App\Enums\CatalogItemType::cases(), 'wizard' => true])

            <div class="mt-6 flex items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    data-wizard-prev
                    class="hidden rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                >
                    Voltar
                </button>

                <div class="ml-auto flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                        data-modal-close
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        data-wizard-next
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        Próximo
                    </button>

                    <button
                        type="submit"
                        data-wizard-submit
                        class="hidden items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
