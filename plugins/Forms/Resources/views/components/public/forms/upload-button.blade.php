@props([
    'icon',
    'label',
    'tag' => 'label',
])

<{{ $tag }} {{ $attributes->class(['group flex min-h-12 cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-dashed border-gray-300 bg-white px-2 text-[13px] font-semibold text-gray-700 transition hover:border-indigo-300 hover:text-indigo-600 active:bg-gray-50 sm:gap-2 sm:px-3 sm:text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/60 dark:hover:text-indigo-300 dark:active:bg-gray-800']) }}>
    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-aria-pressed:bg-gray-200 group-aria-pressed:text-gray-700 sm:h-8 sm:w-8 dark:bg-indigo-500/10 dark:text-indigo-300 dark:group-aria-pressed:bg-gray-700 dark:group-aria-pressed:text-gray-100">
        <x-icon :name="$icon" class="h-4 w-4" />
    </span>
    {{ $label }}
    {{ $slot }}
</{{ $tag }}>
