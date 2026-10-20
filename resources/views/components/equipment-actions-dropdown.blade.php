@props(['equipment'])

<div class="relative inline-block text-left">
    <button
        type="button"
        class="rounded-full p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
        aria-haspopup="true"
        aria-expanded="false"
        data-dropdown-toggle
    >
        <span class="sr-only">Abrir menu de ações</span>
        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
    </button>

    <div
        class="absolute right-0 z-10 mt-1 hidden w-40 origin-top-right rounded-md bg-white py-1 text-left shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
        role="menu"
        data-dropdown-menu
    >
        <a
            href="{{ route('admin.equipments.show', $equipment) }}"
            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="eye" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Ver detalhes
        </a>

        <button
            type="button"
            data-modal-open="equipment-edit-modal-{{ $equipment->getKey() }}"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Editar
        </button>

        <form
            method="POST"
            action="{{ route('admin.equipments.destroy', $equipment) }}"
            data-confirm="Excluir o equipamento {{ $equipment->name }}?"
            data-confirm-title="Excluir equipamento"
            data-confirm-label="Excluir"
        >
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
                role="menuitem"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir
            </button>
        </form>
    </div>
</div>
