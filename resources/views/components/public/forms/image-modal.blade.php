<div
    data-image-modal
    role="dialog"
    aria-modal="true"
    aria-labelledby="image-preview-title"
    {{ $attributes->class(['fixed inset-0 z-[100] hidden']) }}
>
    <div class="absolute inset-0 bg-gray-950/90 backdrop-blur-sm" data-image-modal-close></div>

    <div class="pointer-events-none absolute inset-0 flex flex-col">
        <div class="pointer-events-auto flex items-start justify-between gap-4 p-4 pt-[calc(env(safe-area-inset-top)+1rem)] sm:p-6">
            <div class="min-w-0 text-white">
                <h2 id="image-preview-title" data-image-modal-title class="truncate text-sm font-semibold"></h2>
                <p data-image-modal-counter class="mt-0.5 text-xs text-gray-300"></p>
            </div>

            <button
                type="button"
                data-image-modal-close
                aria-label="Fechar pré-visualização"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        <div class="relative flex min-h-0 flex-1 items-center justify-center px-4 pb-4 sm:px-16">
            <button
                type="button"
                data-image-modal-prev
                aria-label="Imagem anterior"
                hidden
                class="pointer-events-auto absolute left-2 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60 sm:left-4"
            >
                <x-icon name="chevron-left" class="h-6 w-6" />
            </button>

            <img
                data-image-modal-image
                alt=""
                class="pointer-events-auto max-h-full max-w-full rounded-lg object-contain shadow-2xl"
            >

            <button
                type="button"
                data-image-modal-next
                aria-label="Próxima imagem"
                hidden
                class="pointer-events-auto absolute right-2 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60 sm:right-4"
            >
                <x-icon name="chevron-right" class="h-6 w-6" />
            </button>
        </div>

        <div class="pointer-events-auto flex justify-center p-4 pb-[calc(env(safe-area-inset-bottom)+1rem)] sm:p-6">
            <a
                data-image-modal-original
                href="#"
                target="_blank"
                rel="noopener"
                class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white/10 px-4 text-sm font-medium text-white transition hover:bg-white/20"
            >
                Abrir original
            </a>
        </div>
    </div>
</div>
