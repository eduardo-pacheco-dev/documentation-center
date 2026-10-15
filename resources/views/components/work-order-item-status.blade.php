@props(['item'])

@switch ($item->status->value)
    @case('pending')
        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-500/15 px-2 py-0.5 text-xs font-medium text-slate-600 dark:text-slate-300">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            {{ $item->status->label() }}
        </span>
    @break

    @case('in_progress')
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-500/15 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
            <x-icon name="arrow-path" class="h-3.5 w-3.5" />
            {{ $item->status->label() }}
        </span>
    @break

    @case('completed')
        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/15 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">
            <x-icon name="check-circle" class="h-3.5 w-3.5" />
            {{ $item->status->label() }}
        </span>
    @break

    @default
        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-500/15 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">
            <x-icon name="x-circle" class="h-3.5 w-3.5" />
            {{ $item->status->label() }}
        </span>
    @endswitch
