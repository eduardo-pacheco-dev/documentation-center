<div
    data-link-modal
    class="fixed inset-0 z-50 hidden flex overflow-y-auto p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="link-modal-title"
>
    <div class="fixed inset-0 bg-gray-950/50" data-link-modal-close></div>

    <div class="relative m-auto w-full max-w-lg rounded-xl bg-white shadow-xl dark:bg-gray-900">
        <header class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 id="link-modal-title" class="flex items-center gap-2 text-base font-semibold">
                    <x-icon name="link" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Link
                </h2>
                <p data-link-modal-title class="mt-0.5 text-sm text-gray-500 dark:text-gray-400"></p>
            </div>

            <button
                type="button"
                data-link-modal-close
                class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </header>

        <div class="space-y-3 px-5 py-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Compartilhe este link</label>

            <div class="flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 px-3 py-2">
                <code data-link-modal-url class="min-w-0 flex-1 break-all font-mono text-sm text-gray-700 dark:text-gray-200"></code>

                <button
                    type="button"
                    data-link-modal-copy
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-800"
                >
                    <x-icon name="clipboard-document" class="h-4 w-4" />
                    <span data-link-modal-copy-label>Copiar</span>
                </button>
            </div>
        </div>

        <footer class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            <button
                type="button"
                data-link-modal-close
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Fechar
            </button>

            <a
                data-link-modal-open-link
                href="#"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="arrow-top-right-on-square" class="h-4 w-4" />
                Abrir link
            </a>
        </footer>
    </div>
</div>
