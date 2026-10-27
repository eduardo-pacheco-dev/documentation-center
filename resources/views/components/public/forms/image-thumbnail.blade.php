@props([
    'href',
    'src',
    'alt' => '',
])

<a
    href="{{ $href }}"
    target="_blank"
    rel="noopener"
    data-image-existing
    {{ $attributes->class(['group relative block h-20 w-20 overflow-hidden rounded-xl border border-gray-200 transition active:scale-[0.98] dark:border-gray-800']) }}
>
    <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" class="h-full w-full object-cover">
    <span class="pointer-events-none absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-950/60 text-white">
        <x-icon name="eye" class="h-3.5 w-3.5" />
    </span>
</a>
