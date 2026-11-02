@props([
    'items' => [],
    'labels' => [],
    'values' => [],
    'empty' => null,
    'columns' => 1,
    'dot' => false,
    'truncate' => false,
])

@php
    $rows = $items;

    if ($rows === [] && $labels !== []) {
        $rows = collect($labels)
            ->map(fn (string $label, int $index): array => [
                'label' => $label,
                'value' => $values[$index] ?? 0,
            ])
            ->all();
    }
@endphp

@if ($rows === [])
    @if ($empty)
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $empty }}</p>
    @endif
@else
    <ul {{ $attributes->class($columns === 2 ? 'grid grid-cols-2 gap-x-4 gap-y-1.5' : 'space-y-2') }}>
        @foreach ($rows as $row)
            <li class="flex items-center justify-between gap-2 text-sm">
                <span class="flex min-w-0 items-center gap-2 text-gray-600 dark:text-gray-300">
                    @if ($dot)
                        <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $row['color'] ?? '#9ca3af' }}"></span>
                    @endif

                    <span @class(['truncate' => $truncate])>{{ $row['label'] }}</span>
                </span>

                <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $row['value'] }}</span>
            </li>
        @endforeach
    </ul>
@endif
