@props([
    'labels' => [],
    'values' => [],
    'empty' => null,
])

@php
    $max = max(1, ...($values ?: [1]));
@endphp

@if ($labels === [])
    @if ($empty)
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $empty }}</p>
    @endif
@else
    <ul {{ $attributes->class('grid gap-x-6 gap-y-3 sm:grid-cols-2') }}>
        @foreach ($labels as $index => $label)
            <li>
                <div class="flex items-center justify-between gap-2 text-sm">
                    <span class="min-w-0 truncate text-gray-600 dark:text-gray-300">{{ $label }}</span>
                    <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $values[$index] }}</span>
                </div>

                <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ max(4, round(($values[$index] / $max) * 100)) }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
