@props([
    'icon',
    'label',
    'href' => null,
])

@php
    $classes = 'relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-200/70 hover:text-gray-900 focus:outline-none dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white';
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" {{ $attributes->class($classes) }}>
        <x-icon :name="$icon" class="h-5 w-5" />
        {{ $slot }}
    </a>
@else
    <button type="button" aria-label="{{ $label }}" {{ $attributes->class($classes) }}>
        <x-icon :name="$icon" class="h-5 w-5" />
        {{ $slot }}
    </button>
@endif
