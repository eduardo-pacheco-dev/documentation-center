@props([
    'initialStep' => 0,
    'sectionCount' => 0,
    'hasResponse' => false,
])

<div {{ $attributes->class(['fixed inset-x-0 bottom-0 z-30 border-t border-gray-200/80 bg-white/95 pt-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] shadow-[0_-4px_16px_-8px_rgba(0,0,0,0.15)] backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/95']) }}>
    <div class="mx-auto flex w-full max-w-2xl items-center gap-2 px-4 sm:px-6">
        <button
            type="button"
            data-form-step-prev
            @if ($initialStep === 0) hidden @endif
            class="inline-flex min-h-12 flex-1 items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 dark:active:bg-gray-700 dark:focus-visible:ring-offset-gray-900"
        >
            <x-icon name="chevron-left" class="h-4 w-4" />
            Anterior
        </button>

        <button
            type="button"
            data-form-step-next
            @if ($initialStep >= $sectionCount - 1) hidden @endif
            class="inline-flex min-h-12 flex-1 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:active:bg-indigo-600 dark:focus-visible:ring-offset-gray-900"
        >
            Próximo
            <x-icon name="chevron-right" class="h-4 w-4" />
        </button>

        <button
            type="submit"
            data-form-step-submit
            @if ($initialStep < $sectionCount - 1) hidden @endif
            class="inline-flex min-h-12 flex-1 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:active:bg-indigo-600 dark:focus-visible:ring-offset-gray-900"
        >
            <x-icon name="paper-airplane" class="h-4 w-4" />
            {{ $hasResponse ? 'Salvar' : 'Enviar respostas' }}
        </button>
    </div>
</div>
