@props(['title', 'url' => null, 'linkLabel' => 'Ver todos'])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900']) }}>
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h2>
        @if ($url)
            <a href="{{ $url }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ $linkLabel }}</a>
        @endif
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        {{ $slot }}
    </div>
</section>
