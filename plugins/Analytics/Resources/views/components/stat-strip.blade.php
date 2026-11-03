@props(['items' => []])

<div {{ $attributes->class('flex flex-wrap items-center gap-x-6 gap-y-2 border-y border-gray-200 py-3 dark:border-gray-800') }}>
    @foreach ($items as $item)
        <div class="flex items-center gap-2">
            <x-icon :name="$item['icon'] ?? 'chart-bar'" class="h-4 w-4 {{ $item['color'] ?? 'text-gray-400 dark:text-gray-500' }}" />
            <span class="text-lg font-semibold leading-none text-gray-900 dark:text-gray-100">{{ $item['value'] }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item['label'] }}</span>
        </div>
    @endforeach
</div>
