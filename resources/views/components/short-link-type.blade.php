@props(['type'])

@if ($type->value === 'upload')
    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
        <x-icon name="arrow-up-tray" class="h-3.5 w-3.5" />
        Upload
    </span>
@else
    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">
        <x-icon name="arrow-down-tray" class="h-3.5 w-3.5" />
        Download
    </span>
@endif