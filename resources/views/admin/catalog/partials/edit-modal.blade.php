@php
    $editModalOpen = $errors->any()
        && old('modal') === 'edit'
        && (string) old('editing_item') === (string) $catalogItem->getKey();
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

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="catalog-item-edit-modal-title-{{ $catalogItem->getKey() }}" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
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

            <div class="mt-6 flex items-center justify-between gap-3">
                <button
                    type="button"
                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    data-modal-close
                >
                    Cancelar
                </button>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        data-wizard-prev
                        class="hidden rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        Voltar
                    </button>

                    <button
                        type="button"
                        data-wizard-next
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        Próximo
                    </button>

                    <button
                        type="submit"
                        data-wizard-submit
                        class="hidden items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
