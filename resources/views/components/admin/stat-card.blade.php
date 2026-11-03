@props([
    'title',
    'description' => null,
    'value',
    'icon' => 'squares-2x2',
    'href',
    'tone' => 'indigo',
])

@php
    $toneClasses = match ($tone) {
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
        default => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    };
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group relative block h-full rounded-3xl border border-gray-200/80 bg-white p-5 transition hover:border-indigo-200 dark:border-white/10 dark:bg-[#171716] dark:hover:border-indigo-500/40']) }}
>
    <div class="flex items-start justify-between">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $toneClasses }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
        <x-icon name="arrow-top-right-on-square" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-600 dark:text-gray-600 dark:group-hover:text-gray-300" />
    </div>

    <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $value }}</p>
    <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $title }}</p>

    @if ($description)
        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $description }}</p>
    @endif
</a>
