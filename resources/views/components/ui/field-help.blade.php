@props(['id' => null])

<p
    @if ($id) id="{{ $id }}" @endif
    {{ $attributes->class(['text-xs leading-relaxed text-gray-500 dark:text-gray-400']) }}
>
    {{ $slot }}
</p>
