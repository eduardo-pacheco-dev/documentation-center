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

<x-analytics::panel :title="$title" :description="$description">
    <a href="{{ route($templateRoute) }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold {{ $palette['link'] }}">
        <x-icon name="arrow-down-tray" class="h-4 w-4" />
        {{ $templateLabel }}
    </a>

    <form method="POST" action="{{ route($action) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
        @csrf
        <input type="hidden" name="period" value="{{ $days }}">
        <input type="hidden" name="scope" value="{{ $scope }}">

        <label for="{{ $fileId }}" class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-gray-50/60 px-4 py-6 text-center transition {{ $palette['dropzone'] }} dark:border-white/15 dark:bg-white/[0.02]">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm {{ $palette['icon'] }} dark:bg-white/10">
                <x-icon name="arrow-up-tray" class="h-5 w-5" />
            </span>

            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Selecionar planilha</span>
            <span class="text-xs text-gray-400 dark:text-gray-500">.xlsx, .xls ou .csv (até {{ $maxSize }})</span>

            <input id="{{ $fileId }}" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only">
        </label>

        <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl px-4 text-sm font-semibold text-white transition {{ $palette['button'] }}">
            <x-icon name="arrow-up-tray" class="h-4 w-4" />
            {{ $submitLabel }}
        </button>
    </form>

    @if ($summary !== [])
        <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
            @foreach ($summary as $line)
                <span>{{ $line }}</span>

                @unless ($loop->last)
                    <span>·</span>
                @endunless
            @endforeach
        </div>
    @endif
</x-analytics::panel>
