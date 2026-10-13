@props(['template'])

<div class="relative inline-block text-left">
    <button
        type="button"
        data-dropdown-toggle
        aria-haspopup="true"
        aria-expanded="false"
        class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
    >
        <span class="sr-only">Ações</span>
        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
    </button>

    <div
        data-dropdown-menu
        class="absolute right-0 z-10 mt-1 hidden w-40 rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:shadow-gray-950"
    >
        @can('update', $template)
            <a href="{{ route('admin.form-templates.edit', $template) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>
        @endcan
        @can('delete', $template)
            <form method="POST" action="{{ route('admin.form-templates.destroy', $template) }}" data-confirm="Excluir template {{ $template->name }}?">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                    <x-icon name="trash" class="h-4 w-4" />
                    Excluir
                </button>
            </form>
        @endcan
    </div>
</div>
