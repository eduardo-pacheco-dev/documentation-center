@props(['workOrder'])

@switch ($workOrder->status->value)
    @case('open')
        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-500/15 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-300">
            <x-icon name="clipboard-document-list" class="h-3.5 w-3.5" />
            {{ $workOrder->status->label() }}
        </span>
    @break

    @case('in_progress')
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-500/15 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
            <x-icon name="arrow-path" class="h-3.5 w-3.5" />
            {{ $workOrder->status->label() }}
        </span>
    @break

    @case('completed')
        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/15 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">
            <x-icon name="check-circle" class="h-3.5 w-3.5" />
            {{ $workOrder->status->label() }}
        </span>
    @break

    @default
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
            <x-icon name="x-circle" class="h-3.5 w-3.5" />
            {{ $workOrder->status->label() }}
        </span>
    @endswitch
