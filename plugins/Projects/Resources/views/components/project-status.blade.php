@props(['project'])

@php
    $status = $project->status;
    $tone = match ($status?->value) {
        'completed' => 'green',
        'archived' => 'gray',
        default => 'indigo',
    };
@endphp

<x-ui.badge :tone="$tone">
    {{ $status?->label() ?? '—' }}
</x-ui.badge>
