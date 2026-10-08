@props(['document'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-0.5']) }}>
    <button
        type="button"
        class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
        aria-label="Renomear {{ $document->original_name }}"
        title="Renomear"
        data-rename-open
        data-document-id="{{ $document->getKey() }}"
        data-document-name="{{ $document->original_name }}"
    >
        <x-icon name="pencil-square" class="h-4 w-4" />
    </button>

    <button
        type="button"
        class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
        aria-label="Gerenciar links de {{ $document->original_name }}"
        title="Gerenciar links"
        data-modal-open="links-modal-{{ $document->getKey() }}"
    >
        <x-icon name="link" class="h-4 w-4" />
    </button>

    <button
        type="submit"
        form="delete-document-{{ $document->getKey() }}"
        class="rounded-md p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-gray-400 dark:hover:bg-red-500/15 dark:hover:text-red-400"
        aria-label="Excluir {{ $document->original_name }}"
        title="Excluir"
        data-confirm="Excluir {{ $document->original_name }}?"
    >
        <x-icon name="trash" class="h-4 w-4" />
    </button>
</div>
