@props([
    'tone' => 'info',
    'icon' => null,
])

@php
    $toneClasses = match ($tone) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300',
        'error' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',
        default => 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300',
    };

    $icon ??= match ($tone) {
        'success' => 'check-circle',
        'error' => 'exclamation-triangle',
        'warning' => 'arrow-path',
        default => 'exclamation-circle',
    };

    $role = match ($tone) {
        'success' => 'status',
        'error' => 'alert',
        default => null,
    };
@endphp

<div @if ($role) role="{{ $role }}" @endif {{ $attributes->class(['flex items-start gap-2.5 rounded-xl border p-4 text-sm', $toneClasses]) }}>
    <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0" />
    <p>{{ $slot }}</p>
</div>
