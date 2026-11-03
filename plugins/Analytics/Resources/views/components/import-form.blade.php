@props([
    'title',
    'description',
    'action',
    'templateRoute',
    'templateLabel' => 'Baixar modelo (.xlsx)',
    'submitLabel' => 'Importar',
    'fileId',
    'tone' => 'indigo',
    'maxSize' => '10 MB',
    'summary' => [],
    'days',
    'scope',
    'modalTarget',
    'disabled' => false,
])

@php
    $tones = [
        'indigo' => [
            'link' => 'text-indigo-600 hover:text-indigo-500 dark:text-indigo-400',
            'dropzone' => 'hover:border-indigo-300 hover:bg-indigo-50/40 dark:hover:border-indigo-500/40',
            'icon' => 'text-indigo-600 dark:text-indigo-300',
            'button' => 'bg-indigo-600 hover:bg-indigo-500',
        ],
        'rose' => [
            'link' => 'text-rose-600 hover:text-rose-500 dark:text-rose-400',
            'dropzone' => 'hover:border-rose-300 hover:bg-rose-50/40 dark:hover:border-rose-500/40',
            'icon' => 'text-rose-600 dark:text-rose-300',
            'button' => 'bg-rose-600 hover:bg-rose-500',
        ],
    ];

    $palette = $tones[$tone] ?? $tones['indigo'];
@endphp

<div {{ $attributes->class('rounded-2xl border border-gray-200/80 p-4 dark:border-white/10') }}>
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
            <p class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $description }}</p>
        </div>

        <a href="{{ route($templateRoute) }}" class="inline-flex shrink-0 items-center gap-1.5 text-xs font-semibold {{ $palette['link'] }}">
            <x-icon name="arrow-down-tray" class="h-4 w-4" />
            {{ $templateLabel }}
        </a>
    </div>

    <form method="POST" action="{{ route($action) }}" enctype="multipart/form-data" class="mt-3 space-y-3">
        @csrf
        <input type="hidden" name="period" value="{{ $days }}">
        <input type="hidden" name="scope" value="{{ $scope }}">
        <input type="hidden" name="modal" value="{{ $modalTarget }}">

        <label for="{{ $fileId }}" data-import-dropzone @class([
            'group flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50/60 px-4 py-3 text-left transition',
            $palette['dropzone'],
            'dark:border-white/15 dark:bg-white/[0.02]',
            'pointer-events-none opacity-60' => $disabled,
        ])>
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white shadow-sm {{ $palette['icon'] }} dark:bg-white/10">
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
            </span>

            <span class="min-w-0">
                <span data-import-file-title class="block text-sm font-medium text-gray-700 dark:text-gray-200">Selecionar planilha</span>
                <span data-import-file-hint class="block text-xs text-gray-400 dark:text-gray-500">.xlsx, .xls ou .csv (até {{ $maxSize }})</span>
            </span>

            <input id="{{ $fileId }}" name="file" type="file" accept=".xlsx,.xls,.csv" required data-import-file @disabled($disabled) class="sr-only">
        </label>

        <div data-import-file-preview class="hidden items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/70 px-3 py-2 dark:border-white/10 dark:bg-white/[0.03]">
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm {{ $palette['icon'] }} dark:bg-white/10">
                <x-icon name="document-text" class="h-4 w-4" />
            </span>

            <div class="min-w-0 flex-1">
                <p data-import-file-name class="truncate text-xs font-semibold text-gray-800 dark:text-gray-100"></p>
                <p data-import-file-size class="text-[11px] text-gray-500 dark:text-gray-400"></p>
            </div>

            <button type="button" data-import-file-clear class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200">
                <x-icon name="x-mark" class="h-4 w-4" />
                <span class="sr-only">Remover arquivo</span>
            </button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            @if ($summary !== [])
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ implode(' · ', $summary) }}</p>
            @endif

            <button type="submit" data-import-submit @disabled($disabled) class="ml-auto inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-xl px-4 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60 {{ $palette['button'] }}">
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>
