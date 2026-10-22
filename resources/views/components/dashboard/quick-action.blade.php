@if (auth()->user()->hasModule(\App\Enums\AdminModule::WorkOrders->value))
    <a
        href="{{ route('admin.work-orders.create') }}"
        class="group relative mb-3 inline-flex h-10 items-center rounded-xl border border-gray-300 bg-white text-gray-700 shadow-sm transition hover:border-gray-900 hover:text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-white dark:hover:text-white"
        :class="sidebarExpanded ? 'w-full justify-start gap-2.5 px-3' : 'w-10 justify-center self-center'"
    >
        <x-icon name="plus" class="h-5 w-5 shrink-0" />
        <span x-cloak x-show="sidebarExpanded" class="truncate text-xs font-semibold">Nova OS</span>
        <x-ui.tooltip x-cloak x-show="!sidebarExpanded">Nova OS</x-ui.tooltip>
    </a>
@endif
