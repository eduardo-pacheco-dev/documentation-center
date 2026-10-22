@props([
    'href' => null,
    'variant' => 'primary',
    'icon' => null,
    'type' => 'button',
    'size' => 'md',
])

@php
    $tag = $href ? 'a' : 'button';

    $variantClasses = match ($variant) {
        'secondary' => 'border border-gray-200 bg-white text-gray-600 hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300',
        'ghost' => 'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white',
        'danger' => 'bg-red-600 text-white hover:bg-red-500',
        default => 'bg-indigo-600 text-white hover:bg-indigo-500',
    };

    $sizeClasses = match ($size) {
        'sm' => 'h-9 px-3 text-xs',
        'lg' => 'h-11 px-5 text-sm',
        default => 'h-10 px-4 text-sm',
    };
@endphp

<{{ $tag }}
    @if ($href)
        href="{{ $href }}"
    @else
        type="{{ $type }}"
    @endif
    {{ $attributes->class(['inline-flex items-center justify-center gap-1.5 rounded-xl font-semibold transition', $variantClasses, $sizeClasses]) }}
>
    @if ($icon)
        <x-icon :name="$icon" class="h-4 w-4" />
    @endif

    {{ $slot }}
</{{ $tag }}>
