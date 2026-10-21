<div class="relative">
    <button
        type="button"
        @click="userOpen = !userOpen"
        :aria-expanded="userOpen"
        class="flex items-center gap-2.5 rounded-xl transition hover:bg-white focus:outline-none dark:hover:bg-white/10"
        :class="sidebarExpanded ? 'w-full justify-start px-3 py-1.5' : 'justify-center p-1.5'"
        aria-label="Menu do usuário"
    >
        <x-ui.avatar :name="auth()->user()->name" status />

        <span x-cloak x-show="sidebarExpanded" class="min-w-0 flex-1 text-left">
            <span class="block truncate text-xs font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
            <span class="block truncate text-[10px] text-gray-400 dark:text-gray-500">{{ auth()->user()->email }}</span>
        </span>

        <span x-cloak x-show="sidebarExpanded" class="shrink-0 text-gray-400 dark:text-gray-500">
            <x-icon name="chevron-up-down" class="h-4 w-4" />
        </span>
    </button>

    <x-ui.dropdown
        show="userOpen"
        close="userOpen = false"
        origin="bottom.left"
        x-bind:class="sidebarExpanded ? 'left-0 bottom-full mb-2' : 'left-full bottom-0 ml-3'"
        class="w-56 p-1.5"
    >
        <div class="px-3 py-2.5">
            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
        </div>

        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

        <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Perfil</a>
        <a href="{{ route('settings') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Configurações</a>

        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Sair</button>
        </form>
    </x-ui.dropdown>
</div>
