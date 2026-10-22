@props([
    'icon',
    'title',
    'description' => null,
    'tone' => 'gray',
    'shape' => 'circle',
])

@php
    $toneClasses = match ($tone) {
        'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        default => 'bg-gray-50 text-gray-400 dark:bg-white/[0.04] dark:text-gray-500',
    };

    $shapeClasses = $shape === 'square' ? 'rounded-2xl' : 'rounded-full';
@endphp

<div {{ $attributes->class(['px-6 py-16 text-center sm:py-20']) }}>
    <span class="relative mx-auto inline-flex h-16 w-16 items-center justify-center {{ $shapeClasses }} {{ $toneClasses }}">
        @if ($shape === 'circle')
            <span class="absolute inset-0 rounded-full border border-dashed border-gray-200 dark:border-white/10"></span>
        @endif

        <x-icon :name="$icon" class="h-7 w-7" />
    </span>

    <h3 class="mt-5 text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-1.5 max-w-sm text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    @endif

    {{ $slot }}
</div>
