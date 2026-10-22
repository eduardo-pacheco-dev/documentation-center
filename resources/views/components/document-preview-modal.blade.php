<div
    id="preview-modal"
    class="fixed inset-0 z-[100] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="preview-modal-title"
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 flex max-h-[90vh] w-full max-w-4xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-2xl bg-white dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div class="min-w-0">
                <h2 id="preview-modal-title" data-preview-title class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100"></h2>
                <p data-preview-meta class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"></p>
            </div>

            <button
                type="button"
                data-modal-close
                aria-label="Fechar pré-visualização"
                class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
            >
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-auto p-5">
            <img data-preview-image alt="" class="mx-auto hidden max-h-[70vh] rounded-lg" />

            <iframe data-preview-frame title="Pré-visualização" class="hidden h-[75vh] w-full rounded-lg border-0"></iframe>

            <pre data-preview-text class="hidden max-h-[75vh] overflow-auto whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-200"></pre>

            <div data-preview-fallback class="hidden flex-col items-center justify-center gap-4 py-8 text-center">
                <x-icon name="document" class="h-14 w-14 text-gray-300 dark:text-gray-600" />
                <p class="text-sm text-gray-500 dark:text-gray-400">Este tipo de arquivo não pode ser pré-visualizado no navegador.</p>
                <a
                    data-preview-download
                    href="#"
                    download
                    class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    <x-icon name="arrow-down-tray" class="h-4 w-4" />
                    Baixar arquivo
                </a>
            </div>
        </div>
    </div>
</div>
