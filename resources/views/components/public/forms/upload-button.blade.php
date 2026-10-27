@props([
    'icon',
    'label',
])

<label {{ $attributes->class(['flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 active:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/60 dark:hover:text-indigo-300 dark:active:bg-gray-800']) }}>
    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
        <x-icon :name="$icon" class="h-4 w-4" />
    </span>
    {{ $label }}
    {{ $slot }}
</label>
