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
        class="absolute right-[-10.5px] top-[-10.5px] z-10 inline-flex h-[21px] w-[21px] items-center justify-center rounded-full border border-red-600 bg-red-600 text-white transition hover:border-red-500 hover:bg-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:border-red-500 dark:bg-red-500 dark:hover:border-red-400 dark:hover:bg-red-400"
    >
        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
