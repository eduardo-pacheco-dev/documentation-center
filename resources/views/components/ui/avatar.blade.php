@props([
    'name',
    'size' => 'md',
    'status' => false,
    'statusBorder' => 'border-[#f4f4f2] dark:border-[#171716]',
])

@php
    $dimensions = match ($size) {
        'sm' => 'h-8 w-8',
        'lg' => 'h-10 w-10',
        default => 'h-9 w-9',
    };
@endphp

<span {{ $attributes->class(['relative inline-flex shrink-0']) }}>
    <img
        src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=1f2937&color=fff&size=64"
        alt="{{ $name }}"
        class="{{ $dimensions }} rounded-full"
    >

    @if ($status)
        <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 bg-emerald-500 {{ $statusBorder }}"></span>
    @endif
</span>
