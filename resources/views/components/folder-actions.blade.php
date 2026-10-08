@props(['folder'])

@php
    $folderQuery = request()->query();
    unset($folderQuery['page'], $folderQuery['search'], $folderQuery['folder']);
@endphp

<div {{ $attributes->merge(['class' => 'relative flex items-center justify-end']) }}>
    <button
        type="button"
        data-dropdown-toggle
        aria-expanded="false"
        aria-label="Ações da pasta {{ $folder->name }}"
        title="Mais ações"
        class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100 sm:opacity-0 sm:group-focus-within:opacity-100 sm:group-hover:opacity-100"
    >
        <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
    </button>

    <div
        data-dropdown-menu
        role="menu"
        class="absolute right-0 top-full z-20 hidden w-52 origin-top-right rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900"
    >
        <a
            href="{{ route('admin.files.index', array_merge($folderQuery, ['folder' => $folder->getKey()])) }}"
            role="menuitem"
            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            <x-icon name="folder" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Abrir
        </a>

        <button
            type="button"
            role="menuitem"
            data-folder-rename-open
            data-folder-id="{{ $folder->getKey() }}"
            data-folder-name="{{ $folder->name }}"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Renomear
        </button>

        <form method="POST" action="{{ route('admin.folders.destroy', $folder) }}">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                role="menuitem"
                data-confirm="Mover a pasta {{ $folder->name }} para a lixeira? Subpastas e arquivos vão junto."
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Mover para lixeira
            </button>
        </form>
    </div>
</div>
