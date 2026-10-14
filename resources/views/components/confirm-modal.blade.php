<div
    id="confirm-modal"
    class="fixed inset-0 z-[110] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-modal-title"
>
    <div class="absolute inset-0 bg-gray-900/50" data-confirm-cancel></div>

    <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                <x-icon name="exclamation-triangle" class="h-5 w-5" />
            </span>
            <div class="min-w-0 flex-1">
                <h2 id="confirm-modal-title" data-confirm-title class="text-base font-semibold text-gray-900 dark:text-gray-100">Tem certeza?</h2>
                <p data-confirm-message class="mt-1 text-sm text-gray-500 dark:text-gray-400"></p>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button
                type="button"
                data-confirm-cancel
                class="rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                Cancelar
            </button>
            <button
                type="button"
                data-confirm-accept
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500"
            >
                Confirmar
            </button>
        </div>
    </div>
</div>
