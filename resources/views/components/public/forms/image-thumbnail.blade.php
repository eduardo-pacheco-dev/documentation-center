@props([
    'href',
    'src',
    'alt' => '',
])

<div {{ $attributes->class(['group relative h-20 w-20']) }} data-image-item>
    <a
        href="{{ $href }}"
        target="_blank"
        rel="noopener"
        data-image-existing
        class="block h-full w-full overflow-hidden rounded-xl border border-gray-200 transition active:scale-[0.98] dark:border-gray-800"
    >
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" class="h-full w-full object-cover">
        <span class="pointer-events-none absolute left-1 top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-950/60 text-white">
            <x-icon name="eye" class="h-3 w-3" />
        </span>
    </a>

    <button
        type="button"
        data-image-remove
        aria-label="Excluir imagem"
        class="absolute bottom-1 right-1 inline-flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-red-500/40 dark:hover:bg-red-500/10 dark:hover:text-red-400"
    >
        <x-icon name="x-mark" class="h-4 w-4" />
    </button>
</div>
