@props([
    'form',
    'totalQuestions' => 0,
    'approvedQuestions' => 0,
    'percent' => 0,
])

<header {{ $attributes->class(['overflow-hidden rounded-2xl border border-gray-200/70 bg-white dark:border-gray-800 dark:bg-gray-900']) }}>
    <div class="flex items-start gap-3 p-4 sm:p-5">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white dark:bg-indigo-500">
            <x-icon name="clipboard-document-list" class="h-5 w-5" />
        </span>

        <div class="min-w-0 flex-1">
            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                Formulário externo
            </span>

            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                {{ $form->code }}
            </p>
            <h1 class="mt-1 text-lg font-semibold leading-snug tracking-tight text-gray-900 dark:text-gray-100">
                {{ $form->name }}
            </h1>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="document-text" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                    {{ $form->type }}
                </span>

                @if ($form->project)
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <x-icon name="briefcase" class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" />
                        <span class="truncate">[{{ $form->project->code }}] {{ $form->project->name }}</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if ($totalQuestions > 0)
        <div
            class="border-t border-gray-100 px-4 py-3.5 dark:border-gray-800"
            data-form-progress
            role="progressbar"
            aria-label="Percentual de itens aprovados"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow="{{ $percent }}"
        >
            <div class="flex items-center justify-between gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                    Aprovados
                </span>
                <span class="tabular-nums">
                    <span data-progress-count class="font-semibold text-gray-700 dark:text-gray-200">{{ $approvedQuestions }} de {{ $totalQuestions }}</span>
                    <span class="mx-0.5 text-gray-300 dark:text-gray-600">&middot;</span>
                    <span data-progress-percent class="font-semibold text-gray-700 dark:text-gray-200">{{ $percent }}%</span>
                </span>
            </div>

            <x-ui.progress-bar :percent="$percent" indicator="data-progress-bar" class="mt-2" />
        </div>
    @endif
</header>
