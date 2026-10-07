@props(['user'])

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
        <button
            type="button"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
            data-modal-open="edit-user-modal"
            data-user-edit
            data-user-id="{{ $user->getKey() }}"
            data-user-name="{{ $user->name }}"
            data-user-email="{{ $user->email }}"
            data-user-admin="{{ $user->is_admin ? '1' : '0' }}"
        >
            <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Editar
        </button>

        <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
            @csrf
            @method('PATCH')
            <button
                type="submit"
                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                role="menuitem"
                @if ($user->is_active)
                    onclick="return confirm('Desativar o usuário {{ $user->name }}?')"
                @endif
            >
                @if ($user->is_active)
                    <x-icon name="x-circle" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    Desativar
                @else
                    <x-icon name="check-circle" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    Ativar
                @endif
            </button>
        </form>

        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15"
                role="menuitem"
                onclick="return confirm('Excluir o usuário {{ $user->name }}?')"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir
            </button>
        </form>
    </div>
</div>