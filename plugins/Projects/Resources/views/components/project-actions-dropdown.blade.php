@props(['project'])

<div
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative inline-block text-left"
>
    <button
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
        x-on:click="open = ! open"
        x-bind:aria-expanded="open ? 'true' : 'false'"
        aria-haspopup="menu"
    >
        <span class="sr-only">Abrir menu de ações do projeto {{ $project->name }}</span>
        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.100ms
        class="absolute right-0 z-20 mt-1 w-44 rounded-xl border border-gray-200/80 bg-white p-1 shadow-lg dark:border-white/10 dark:bg-[#1c1c1b]"
        role="menu"
    >
        <a
            href="{{ route('admin.projects.show', $project) }}"
            role="menuitem"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
        >
            <x-icon name="eye" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Ver detalhes
        </a>

        @can('update', $project)
            <a
                href="{{ route('admin.projects.edit', $project) }}"
                role="menuitem"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
            >
                <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                Editar
            </a>
        @endcan

        @can('delete', $project)
            <form
                method="POST"
                action="{{ route('admin.projects.destroy', $project) }}"
                data-confirm-delete="Excluir o projeto {{ $project->name }}? Essa ação não pode ser desfeita."
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    role="menuitem"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
                >
                    <x-icon name="trash" class="h-4 w-4 text-red-500 dark:text-red-400" />
                    Excluir
                </button>
            </form>
        @endcan
    </div>
</div>
