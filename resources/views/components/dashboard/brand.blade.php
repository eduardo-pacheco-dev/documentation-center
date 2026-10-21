<div class="mb-2 flex w-full items-center gap-2" :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
    <a
        href="{{ route('admin.dashboard') }}"
        class="inline-flex h-11 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 dark:border-white/10 dark:bg-white dark:hover:border-white/20"
        aria-label="AFL Engenharia"
    >
        <img
            src="{{ asset('images/auth/logo.jpg') }}"
            alt="AFL Engenharia"
            class="h-full w-full object-contain p-1"
        >
    </a>

    <span x-cloak x-show="sidebarExpanded" class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900 dark:text-white">
        AFL Engenharia
    </span>
</div>
