@php
    $openEditModal = $openEditModal ?? false;
    $editModalOpen = $openEditModal || ($errors->any() && old('modal') === 'edit');
@endphp

<div
    id="work-order-edit-modal"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="work-order-edit-modal-title"
    @if ($editModalOpen) data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-3xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="work-order-edit-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Editar ordem de serviço
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $workOrder->number }}</p>
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

        @if ($errors->any() && old('modal') === 'edit')
            <div
                data-modal-error
                class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                <p class="font-medium">Não foi possível salvar a ordem de serviço. Corrija os itens destacados e tente novamente.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.work-orders.update', $workOrder) }}" class="mt-5" data-wizard novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="modal" value="edit">

            <ol data-wizard-steps class="flex flex-wrap items-center gap-2" role="list"></ol>

            <div class="mt-5">
                @include('admin.work-orders.partials.form', ['workOrder' => $workOrder, 'wizard' => true])
            </div>

            <div class="mt-6 flex items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    data-wizard-prev
                    class="hidden rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    Voltar
                </button>

                <div class="ml-auto flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
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

        <form
            method="POST"
            action="{{ route('admin.work-orders.destroy', $workOrder) }}"
            class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800"
        >
            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
                onclick="return confirm('Excluir a ordem de serviço {{ $workOrder->number }}?')"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir ordem de serviço
            </button>
        </form>
    </div>
</div>
