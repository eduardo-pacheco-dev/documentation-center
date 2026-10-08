@props(['document'])

<div {{ $attributes->merge(['class' => 'relative flex items-center justify-end']) }}>
    <button
        type="button"
        data-dropdown-toggle
        aria-expanded="false"
        aria-label="Ações de {{ $document->original_name }}"
        title="Ações"
        class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
    >
        <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
    </button>

    <div
        data-dropdown-menu
        role="menu"
        class="absolute right-0 top-full z-20 hidden w-52 origin-top-right rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900"
    >
        <button
            type="button"
            role="menuitem"
            data-preview-open
            data-preview-url="{{ route('admin.files.preview', $document) }}"
            data-preview-name="{{ $document->original_name }}"
            data-preview-mime="{{ $document->mime_type ?? '' }}"
            data-preview-size="{{ Number::fileSize($document->size) }}"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            <x-icon name="eye" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Visualizar
        </button>

        <button
            type="button"
            role="menuitem"
            data-rename-open
            data-document-id="{{ $document->getKey() }}"
            data-document-name="{{ $document->original_name }}"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Renomear
        </button>

        <button
            type="button"
            role="menuitem"
            data-modal-open="links-modal-{{ $document->getKey() }}"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Gerenciar links
        </button>

        <button
            type="submit"
            form="delete-document-{{ $document->getKey() }}"
            role="menuitem"
            aria-label="Excluir {{ $document->original_name }}"
            data-confirm="Excluir {{ $document->original_name }}?"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
        >
            <x-icon name="trash" class="h-4 w-4" />
            Excluir
        </button>
    </div>
</div>
