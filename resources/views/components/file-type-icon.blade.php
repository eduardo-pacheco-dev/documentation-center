@props(['name', 'size' => 'sm'])

@php
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    $palettes = [
        'pdf' => 'bg-red-50 text-red-600 ring-red-600/10 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-400/20',
        'doc' => 'bg-sky-50 text-sky-600 ring-sky-600/10 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-400/20',
        'docx' => 'bg-sky-50 text-sky-600 ring-sky-600/10 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-400/20',
        'odt' => 'bg-sky-50 text-sky-600 ring-sky-600/10 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-400/20',
        'xls' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-400/20',
        'xlsx' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-400/20',
        'csv' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-400/20',
        'ods' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-400/20',
        'ppt' => 'bg-amber-50 text-amber-600 ring-amber-600/10 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-400/20',
        'pptx' => 'bg-amber-50 text-amber-600 ring-amber-600/10 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-400/20',
        'txt' => 'bg-gray-100 text-gray-600 ring-gray-600/10 dark:bg-gray-500/10 dark:text-gray-400 dark:ring-gray-400/20',
        'jpg' => 'bg-violet-50 text-violet-600 ring-violet-600/10 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-400/20',
        'jpeg' => 'bg-violet-50 text-violet-600 ring-violet-600/10 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-400/20',
        'png' => 'bg-violet-50 text-violet-600 ring-violet-600/10 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-400/20',
    ];

    $palette = $palettes[$extension] ?? 'bg-gray-100 text-gray-500 ring-gray-500/10 dark:bg-gray-500/10 dark:text-gray-400 dark:ring-gray-400/20';
    $label = $extension !== '' ? $extension : 'file';

    $frame = match ($size) {
        'lg' => 'h-20 w-16 gap-1',
        'xs' => 'h-8 w-8',
        default => 'h-10 w-10',
    };

    $labelSize = match ($size) {
        'lg' => 'text-[10px]',
        'xs' => 'text-[8px]',
        default => 'text-[9px]',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 flex-col items-center justify-center rounded-lg ring-1 ring-inset '.$frame.' '.$palette]) }}>
    @if ($size === 'lg')
        <x-icon name="document" class="h-7 w-7" />
    @endif
    <span class="{{ $labelSize }} font-bold uppercase tracking-wider">{{ $label }}</span>
</span>
