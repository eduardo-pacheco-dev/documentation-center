@props([
    'step',
    'index',
    'total',
    'initialStep' => 0,
])

@php
    $fields = $step['fields'] ?? [];
    $questionCount = count($fields);
    $answeredCount = collect($fields)->filter(
        fn (array $field) => filled($field['value'] ?? null)
            || ! empty($field['images'] ?? [])
            || ! empty($field['files'] ?? []),
    )->count();
    $progress = $questionCount > 0
        ? (int) round(($answeredCount / $questionCount) * 100)
        : 0;
@endphp

<section
    data-form-step="{{ $index }}"
    @if ($index !== $initialStep) hidden @endif
    {{ $attributes->class(['scroll-mt-48 rounded-2xl border border-gray-200/70 bg-white dark:border-gray-800 dark:bg-gray-900']) }}
>
    <div class="flex items-start gap-3 border-b border-gray-100 px-4 py-4 dark:border-gray-800 sm:px-5">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-sm font-semibold tabular-nums text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            {{ $index + 1 }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                Etapa {{ $index + 1 }} de {{ $total }}
            </p>
            <h2 data-form-step-heading class="mt-0.5 text-base font-semibold leading-snug text-gray-900 dark:text-gray-100">
                {{ ($step['title'] ?? null) ?: 'Seção '.($index + 1) }}
            </h2>
        </div>

        <div
            class="relative flex h-11 w-11 shrink-0 items-center justify-center self-center"
            data-step-progress
            role="progressbar"
            aria-label="Progresso da etapa {{ $index + 1 }}"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow="{{ $progress }}"
            aria-valuetext="{{ $answeredCount }} de {{ $questionCount }} perguntas"
        >
            <svg class="h-11 w-11 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                <circle
                    cx="18"
                    cy="18"
                    r="15.5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3"
                    class="text-gray-200 dark:text-gray-800"
                />
                <circle
                    cx="18"
                    cy="18"
                    r="15.5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3"
                    stroke-linecap="round"
                    pathLength="100"
                    stroke-dasharray="100"
                    stroke-dashoffset="{{ 100 - $progress }}"
                    data-step-progress-ring
                    class="text-indigo-600 transition-all duration-300 dark:text-indigo-400"
                />
            </svg>

            <span class="absolute text-[10px] font-semibold tabular-nums text-gray-700 dark:text-gray-200" data-step-progress-label>
                {{ $progress }}%
            </span>
        </div>
    </div>

    <div class="space-y-6 px-4 py-5 sm:px-5">
        @forelse ($step['fields'] ?? [] as $field)
            <x-public.forms.field :field="$field" />
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma pergunta nesta seção.</p>
        @endforelse
    </div>
</section>
