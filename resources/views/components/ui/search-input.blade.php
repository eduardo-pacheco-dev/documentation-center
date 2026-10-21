@props([
    'placeholder' => 'Pesquisar no sistema…',
    'shortcut' => '/',
])

<label {{ $attributes->class(['relative']) }}>
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
        <x-icon name="magnifying-glass" class="h-4 w-4" />
    </span>

    <input
        x-ref="search"
        type="search"
        placeholder="{{ $placeholder }}"
        @keydown.window.slash.prevent="$refs.search.focus()"
        @keydown.window.meta.k.prevent="$refs.search.focus()"
        class="h-9 w-full rounded-xl border border-gray-200 bg-white pl-9 pr-12 text-sm text-gray-700 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-gray-400 focus:ring-2 focus:ring-gray-900/5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:border-gray-500 dark:focus:ring-white/10"
        aria-label="Pesquisar"
    >

    @if ($shortcut)
        <x-ui.kbd class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 hidden lg:inline">{{ $shortcut }}</x-ui.kbd>
    @endif
</label>
