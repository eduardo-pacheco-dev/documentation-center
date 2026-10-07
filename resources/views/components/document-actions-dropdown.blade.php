@props(['document'])

<div class="relative inline-block text-left">
    <button
        type="button"
        class="rounded-full p-1.5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100"
        aria-haspopup="true"
        aria-expanded="false"
        data-dropdown-toggle
    >
        <span class="sr-only">Abrir menu de ações</span>
        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
    </button>

    <div
        class="absolute right-0 z-10 mt-1 hidden w-44 origin-top-right rounded-md bg-white dark:bg-gray-900 py-1 text-left shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10"
        role="menu"
        data-dropdown-menu
    >
        <button
            type="button"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
            data-rename-open
            data-document-id="{{ $document->getKey() }}"
            data-document-name="{{ $document->original_name }}"
        >
            <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Renomear
        </button>

        <button
            type="button"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
            data-modal-open="links-modal-{{ $document->getKey() }}"
        >
            <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Gerenciar links
        </button>

        <button
            type="submit"
            form="generate-link-{{ $document->getKey() }}"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="plus" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Criar link
        </button>

        <button
            type="submit"
            form="delete-document-{{ $document->getKey() }}"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15"
            role="menuitem"
            onclick="return confirm('Excluir {{ $document->original_name }}?')"
        >
            <x-icon name="trash" class="h-4 w-4" />
            Excluir
        </button>
    </div>
</div>