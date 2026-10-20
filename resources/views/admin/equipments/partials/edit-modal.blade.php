@php
    $forceOpen = $forceOpen ?? false;
    $hasErrors = $errors->any()
        && old('modal') === 'edit'
        && (string) old('editing_equipment') === (string) $equipment->getKey();
    $editModalOpen = $forceOpen || $hasErrors;
@endphp

<div
    id="equipment-edit-modal-{{ $equipment->getKey() }}"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="equipment-edit-modal-title-{{ $equipment->getKey() }}"
    @if ($editModalOpen) data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="equipment-edit-modal-title-{{ $equipment->getKey() }}" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Editar equipamento
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $equipment->name }}</p>
            </div>

            <button
                type="button"
                class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                data-modal-close
            >
                <span class="sr-only">Fechar</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        @if ($hasErrors)
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                <p class="font-medium">Não foi possível salvar o equipamento. Corrija os campos destacados e tente novamente.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.equipments.update', $equipment) }}" class="mt-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="modal" value="edit">
            <input type="hidden" name="editing_equipment" value="{{ $equipment->getKey() }}">

            @include('admin.equipments.partials.form', ['equipment' => $equipment, 'statuses' => $statuses])

            <div class="mt-6 flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    class="rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    data-modal-close
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <x-icon name="check" class="h-4 w-4" />
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>
