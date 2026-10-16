@props(['backup'])

@switch ($backup->status->value)
    @case('pending')
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            {{ $backup->status->label() }}
        </span>
    @break

    @case('running')
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
            <x-icon name="arrow-path" class="h-3.5 w-3.5 animate-spin" />
            {{ $backup->status->label() }}
        </span>
    @break

    @case('completed')
        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-300">
            <x-icon name="check-circle" class="h-3.5 w-3.5" />
            {{ $backup->status->label() }}
        </span>
    @break

    @default
        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/15 dark:text-red-300">
            <x-icon name="exclamation-circle" class="h-3.5 w-3.5" />
            {{ $backup->status->label() }}
        </span>
    @endswitch
