@props([
    'show',
    'close' => null,
    'origin' => null,
])

<div
    x-cloak
    x-show="{{ $show }}"
    @if ($close)
        @click.away="{{ $close }}"
    @endif
    x-transition{{ $origin ? '.origin.'.$origin : '' }}
    {{ $attributes->class(['absolute z-50 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900']) }}
>
    {{ $slot }}
</div>
