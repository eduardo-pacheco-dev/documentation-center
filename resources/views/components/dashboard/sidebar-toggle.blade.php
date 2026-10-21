<button
    type="button"
    @click="sidebarExpanded = !sidebarExpanded; modulesOpen = false; userOpen = false"
    class="group absolute -right-3.5 bottom-6 z-50 inline-flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-md transition hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:text-white dark:focus:ring-white/20"
    :aria-label="sidebarExpanded ? 'Recolher menu' : 'Expandir menu'"
    :title="sidebarExpanded ? 'Recolher menu' : 'Expandir menu'"
>
    <span x-cloak x-show="!sidebarExpanded">
        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
    </span>
    <span x-cloak x-show="sidebarExpanded">
        <x-icon name="chevron-left" class="h-3.5 w-3.5" />
    </span>
</button>
