@props(['side' => 'right'])

@php
    $position = match ($side) {
        'right' => 'left-full top-1/2 ml-3 -translate-y-1/2',
        'top' => 'bottom-full left-1/2 mb-2 -translate-x-1/2',
        'left' => 'right-full top-1/2 mr-3 -translate-y-1/2',
        default => 'left-full top-1/2 ml-3 -translate-y-1/2',
    };
@endphp

<span {{ $attributes->class(['pointer-events-none absolute z-50 whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-white dark:text-gray-900', $position]) }}>
    {{ $slot }}
</span>
