@props(['status'])

@php
    $styles = match ($status) {
        'aprovado' => ['bg-green-100 dark:bg-green-500/15 text-green-700 dark:text-green-300', 'check-circle'],
        'reprovado' => ['bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-300', 'x-circle'],
        'enviado' => ['bg-blue-100 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300', 'paper-airplane'],
        'pendente' => ['bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300', 'clock'],
        'nao_aplicavel' => ['bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300', 'minus-circle'],
        default => ['bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400', 'minus'],
    };
@endphp

<span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $styles[0] }}">
    <x-icon :name="$styles[1]" class="h-3.5 w-3.5" />
    {{ $status ?: '—' }}
</span>