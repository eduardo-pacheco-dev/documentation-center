@props(['id' => null])

<p
    @if ($id) id="{{ $id }}" @endif
    role="alert"
    {{ $attributes->class(['flex items-start gap-1.5 text-sm font-medium text-red-600 dark:text-red-400']) }}
>
    <x-icon name="exclamation-triangle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
    <span>{{ $slot }}</span>
</p>
