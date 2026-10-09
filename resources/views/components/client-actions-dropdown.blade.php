@props(['client'])

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
        class="absolute right-0 z-10 mt-1 hidden w-40 origin-top-right rounded-md bg-white dark:bg-gray-900 py-1 text-left shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10"
        role="menu"
        data-dropdown-menu
    >
        <a
            href="{{ route('admin.clients.show', $client) }}"
            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="eye" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Ver detalhes
        </a>

        <a
            href="{{ route('admin.clients.edit', $client) }}"
            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Editar
        </a>

        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15"
                role="menuitem"
                onclick="return confirm('Excluir o cliente {{ $client->name }}?')"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir
            </button>
        </form>
    </div>
</div>
