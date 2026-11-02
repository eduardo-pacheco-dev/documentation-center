@props([
    'title',
    'description' => null,
    'caption' => null,
])

@php
    $labelClass = 'text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500';
@endphp

<x-analytics::card>
    <div class="flex flex-wrap items-center justify-between gap-2 p-5">
        <div>
            <h2 class="{{ $labelClass }}">{{ $title }}</h2>

            @if ($description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>

        @if ($caption)
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $caption }}</span>
        @endif
    </div>

    {{ $slot }}
</x-analytics::card>
