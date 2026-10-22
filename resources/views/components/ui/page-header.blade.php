@props([
    'category',
    'title',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->class(['flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="flex items-start gap-4">
        @if ($icon)
            <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 sm:inline-flex dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon :name="$icon" class="h-6 w-6" />
            </span>
        @endif

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">{{ $category }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $title }}</h1>

            @if ($description)
                <p class="mt-1.5 max-w-xl text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>
    </div>

    @if (! $slot->isEmpty())
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
