@props([
    'title',
    'description' => null,
])

@php
    $labelClass = 'text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500';
@endphp

<x-analytics::card {{ $attributes->class('p-5') }}>
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <h2 class="{{ $labelClass }}">{{ $title }}</h2>

            @if ($description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>

        @if (isset($actions) && ! $actions->isEmpty())
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>

    {{ $slot }}
</x-analytics::card>
