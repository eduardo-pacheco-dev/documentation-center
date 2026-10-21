<div class="mb-2 flex w-full items-center gap-2" :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
    <a
        href="{{ route('admin.dashboard') }}"
        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gray-900 text-white shadow-sm transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
        aria-label="{{ config('app.name') }}"
    >
        <x-icon name="rectangle-stack" class="h-5 w-5" />
    </a>

    <span x-cloak x-show="sidebarExpanded" class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900 dark:text-white">
        {{ config('app.name') }}
    </span>
</div>
