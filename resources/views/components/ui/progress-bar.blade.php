@props([
    'percent' => 0,
    'height' => 'h-2',
    'indicator' => null,
])

@php
    $percent = max(0, min(100, (int) $percent));
@endphp

<div {{ $attributes->class(['overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800', $height]) }}>
    <div
        @if ($indicator) {{ $indicator }} @endif
        class="h-full rounded-full bg-indigo-600 transition-all duration-300 dark:bg-indigo-500"
        style="width: {{ $percent }}%"
    ></div>
</div>
