@props(['value'])

@php
    $text = '—';
    $full = null;

    if ($value !== null) {
        $full = $value->format('d/m/Y H:i');
        $today = now();

        $text = match (true) {
            $value->isSameDay($today) => 'Hoje, '.$value->format('H:i'),
            $value->isSameDay($today->copy()->subDay()) => 'Ontem, '.$value->format('H:i'),
            $value->isSameYear($today) => $value->format('d/m'),
            default => $value->format('d/m/Y'),
        };
    }
@endphp

<time
    {{ $attributes->merge(['class' => 'whitespace-nowrap']) }}
    @if ($full !== null) datetime="{{ $value->toIso8601String() }}" title="{{ $full }}" @endif
>{{ $text }}</time>
