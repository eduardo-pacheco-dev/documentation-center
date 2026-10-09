@props(['workOrder'])

@php
    $classes = match ($workOrder->priority->value) {
        'urgent' => 'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-300',
        'high' => 'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300',
        'low' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300',
        default => 'bg-sky-100 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300',
    };
@endphp

<span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $classes }}">
    @if ($workOrder->priority->value === 'urgent')
        <x-icon name="exclamation-circle" class="h-3.5 w-3.5" />
    @else
        <x-icon name="flag" class="h-3.5 w-3.5" />
    @endif
    {{ $workOrder->priority->label() }}
</span>
