@props([
    'step',
    'index',
    'total',
    'initialStep' => 0,
])

<section
    data-form-step="{{ $index }}"
    @if ($index !== $initialStep) hidden @endif
    {{ $attributes->class(['scroll-mt-48 rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900']) }}
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
    </div>

    <div class="space-y-6 px-4 py-5 sm:px-5">
        @forelse ($step['fields'] ?? [] as $field)
            <x-public.forms.field :field="$field" />
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma pergunta nesta seção.</p>
        @endforelse
    </div>
</section>
