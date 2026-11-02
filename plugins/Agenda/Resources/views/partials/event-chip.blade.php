@php
    $hex = $occurrence->color()->hex();
    $cancelled = $occurrence->isCancelled();
@endphp

<button
    type="button"
    data-agenda-event
    data-event='@json($occurrence->toModalPayload())'
    style="background-color: {{ $hex }}1f; border-color: {{ $hex }};"
    class="block w-full truncate rounded-md border-l-[3px] px-1.5 py-0.5 text-left text-xs font-medium text-gray-700 transition hover:brightness-95 dark:text-gray-200 {{ $cancelled ? 'line-through opacity-60' : '' }}"
    title="{{ $occurrence->title() }}"
>
    @unless ($occurrence->isAllDay())
        <span class="font-semibold">{{ $occurrence->start->format('H:i') }}</span>
    @endunless
    {{ $occurrence->title() }}
</button>
