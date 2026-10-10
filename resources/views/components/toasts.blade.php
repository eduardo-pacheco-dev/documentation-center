@php
    $flashToasts = collect();

    if (session('status')) {
        $flashToasts->push(['type' => 'success', 'message' => session('status')]);
    }

    if (session('error')) {
        $flashToasts->push(['type' => 'error', 'message' => session('error')]);
    }

    if (isset($errors) && $errors->any()) {
        foreach (collect($errors->all())->take(3) as $message) {
            $flashToasts->push(['type' => 'error', 'message' => $message]);
        }
    }
@endphp

<div
    data-toast-container
    class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-3 px-4 sm:inset-x-auto sm:right-4 sm:items-end"
    role="region"
    aria-label="Notificações"
    aria-live="polite"
    aria-atomic="false"
></div>

<template data-toast-template>
    <div
        data-toast
        role="status"
        class="pointer-events-auto flex w-full max-w-sm translate-y-[-0.5rem] items-start gap-3 rounded-lg border border-gray-200 bg-white p-4 opacity-0 shadow-lg transition duration-200 dark:border-gray-800 dark:bg-gray-900"
    >
        <span data-toast-icon="success" class="mt-0.5 shrink-0 text-green-500">
            <x-icon name="check-circle" class="h-5 w-5" />
        </span>
        <span data-toast-icon="error" class="mt-0.5 hidden shrink-0 text-red-500">
            <x-icon name="exclamation-circle" class="h-5 w-5" />
        </span>
        <span data-toast-icon="info" class="mt-0.5 hidden shrink-0 text-blue-500">
            <x-icon name="exclamation-triangle" class="h-5 w-5" />
        </span>

        <div class="flex-1 space-y-2">
            <p data-toast-message class="break-words text-sm text-gray-700 dark:text-gray-200"></p>

            <div data-toast-progress hidden class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div data-toast-progress-bar class="h-full w-0 rounded-full bg-indigo-500 transition-all duration-200" style="width: 0%"></div>
            </div>
        </div>

        <button
            type="button"
            data-toast-close
            class="shrink-0 rounded-md p-0.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            aria-label="Fechar notificação"
        >
            <x-icon name="x-mark" class="h-4 w-4" />
        </button>
    </div>
</template>

@if ($flashToasts->isNotEmpty())
    <script>
        window.flashToasts = window.flashToasts || [];
        @foreach ($flashToasts as $toast)
            window.flashToasts.push(@json($toast));
        @endforeach
    </script>
@endif
