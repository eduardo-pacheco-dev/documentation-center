@props(['cards' => []])

<div {{ $attributes->class('grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4') }}>
    @foreach ($cards as $card)
        <x-analytics::metric-card
            :label="$card['label']"
            :value="$card['value']"
            :hint="$card['hint'] ?? null"
            :icon="$card['icon'] ?? 'chart-bar'"
            :tone="$card['tone'] ?? 'indigo'"
        />
    @endforeach
</div>
