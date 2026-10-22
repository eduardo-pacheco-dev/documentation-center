@props(['tone' => 'gray'])

@php
    $toneClasses = match ($tone) {
        'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300',
        'inverse' => 'bg-white/20 text-white',
        'red' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
        'green' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        default => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400',
    };
@endphp

<span {{ $attributes->class(['inline-flex min-w-5 items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold', $toneClasses]) }}>
    {{ $slot }}
</span>
