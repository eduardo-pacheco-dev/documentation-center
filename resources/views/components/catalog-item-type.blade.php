@props(['catalogItem'])

@if ($catalogItem->type->value === 'product')
    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
        <x-icon name="cube" class="h-3.5 w-3.5" />
        {{ $catalogItem->type->label() }}
    </span>
@else
    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-500/15 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
        <x-icon name="wrench-screwdriver" class="h-3.5 w-3.5" />
        {{ $catalogItem->type->label() }}
    </span>
@endif
