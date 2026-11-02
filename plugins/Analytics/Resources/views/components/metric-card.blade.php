@props([
    'label',
    'value',
    'hint' => null,
    'icon' => 'chart-bar',
    'tone' => 'indigo',
])

@php
    $tones = [
        'indigo' => 'from-indigo-500 to-violet-500',
        'sky' => 'from-sky-500 to-cyan-500',
        'emerald' => 'from-emerald-500 to-teal-500',
        'violet' => 'from-violet-500 to-fuchsia-500',
        'amber' => 'from-amber-500 to-orange-500',
        'rose' => 'from-rose-500 to-pink-500',
    ];
@endphp

<x-analytics::card class="group p-5 transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:hover:border-indigo-500/40">
    <div class="flex items-start justify-between gap-3">
        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $tones[$tone] ?? $tones['indigo'] }} text-white shadow-sm">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>

        @if ($hint)
            <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ $hint }}</span>
        @endif
    </div>

    <p class="mt-4 truncate text-xl font-semibold tracking-tight text-gray-900 sm:text-2xl dark:text-white">{{ $value }}</p>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
</x-analytics::card>
