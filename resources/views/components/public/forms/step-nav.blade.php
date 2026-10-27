@props([
    'steps' => [],
    'initialStep' => 0,
    'sectionCount' => 0,
])

@php
    $initialStepTitle = ($steps[$initialStep]['title'] ?? null) ?: 'Seção '.($initialStep + 1);
    $percent = $sectionCount > 0
        ? (int) round((($initialStep + 1) / $sectionCount) * 100)
        : 0;
@endphp

<div data-form-step-nav {{ $attributes->class(['sticky top-2 z-20 rounded-2xl border border-gray-200/70 bg-white p-3 dark:border-gray-800 dark:bg-gray-900']) }}>
    <div class="flex items-center justify-between gap-3">
        <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
            <x-icon name="rectangle-stack" class="h-3.5 w-3.5" />
            Etapa <span data-form-step-current class="font-semibold text-gray-900 dark:text-gray-100">{{ $initialStep + 1 }}</span> de {{ $sectionCount }}
        </span>
        <span data-form-step-title class="min-w-0 truncate text-right text-xs font-semibold text-gray-900 dark:text-gray-100">{{ $initialStepTitle }}</span>
    </div>

    <x-ui.progress-bar :percent="$percent" height="h-1.5" indicator="data-form-step-bar" class="mt-2" />

    <div class="-mb-0.5 mt-3 flex gap-1.5 overflow-x-auto pb-0.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @for ($index = 0; $index < $sectionCount; $index++)
            @php($sectionTitle = ($steps[$index]['title'] ?? null) ?: 'Seção '.($index + 1))
            <button
                type="button"
                data-form-step-go="{{ $index }}"
                data-state="{{ $index === $initialStep ? 'current' : ($index < $initialStep ? 'complete' : 'upcoming') }}"
                @if ($index === $initialStep) aria-current="step" @endif
                aria-label="Ir para a etapa {{ $index + 1 }}: {{ $sectionTitle }}"
                class="inline-flex min-h-9 shrink-0 items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 data-[state=current]:border-gray-900 data-[state=current]:bg-gray-900 data-[state=current]:text-white dark:data-[state=current]:border-white dark:data-[state=current]:bg-white dark:data-[state=current]:text-gray-900 dark:focus-visible:ring-offset-gray-900 data-[state=complete]:border-emerald-200 data-[state=complete]:bg-emerald-50 data-[state=complete]:text-emerald-700 dark:data-[state=complete]:border-emerald-500/30 dark:data-[state=complete]:bg-emerald-500/10 dark:data-[state=complete]:text-emerald-300 data-[state=upcoming]:border-gray-200 data-[state=upcoming]:bg-white data-[state=upcoming]:text-gray-600 dark:data-[state=upcoming]:border-gray-800 dark:data-[state=upcoming]:bg-gray-900 dark:data-[state=upcoming]:text-gray-400"
            >
                <span class="text-[10px] font-bold tabular-nums opacity-60">{{ $index + 1 }}</span>
                <span class="max-w-32 truncate">{{ $sectionTitle }}</span>
            </button>
        @endfor
    </div>
</div>
