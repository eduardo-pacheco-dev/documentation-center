@props([
    'href',
    'name',
])

<a
    href="{{ $href }}"
    target="_blank"
    rel="noopener"
    {{ $attributes->class(['flex min-h-11 items-center gap-2.5 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-indigo-600 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-gray-800 dark:bg-gray-900 dark:text-indigo-400 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10']) }}
>
    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300">
        <x-icon name="paper-clip" class="h-4 w-4" />
    </span>
    <span class="min-w-0 flex-1 truncate">{{ $name }}</span>
    <x-icon name="arrow-down-tray" class="h-4 w-4 shrink-0 text-gray-400" />
</a>
