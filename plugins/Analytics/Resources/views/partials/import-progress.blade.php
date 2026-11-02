@php
    $activeImports = $imports->filter(fn ($import): bool => ! $import->isFinished());
@endphp

@foreach ($activeImports as $import)
    <div class="flex items-start gap-3 rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/30 dark:bg-indigo-500/10">
        <span class="relative mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white">
            <span class="absolute inset-0 animate-ping rounded-full bg-indigo-400 opacity-40"></span>
            <x-icon name="arrow-path" class="relative h-4 w-4 animate-spin" />
        </span>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">
                Processando importação {{ $import->typeLabel() }}…
            </p>

            <p class="mt-0.5 text-xs text-indigo-700 dark:text-indigo-300">
                @if ($import->total_rows > 0)
                    {{ number_format($import->total_rows, 0, ',', '.') }} linha(s) encontrada(s) na planilha.
                @else
                    Lendo a planilha e preparando os registros.
                @endif
                O painel será atualizado automaticamente quando terminar.
            </p>

            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-indigo-200/70 dark:bg-indigo-500/20">
                <div class="h-full w-2/3 animate-pulse rounded-full bg-indigo-600 dark:bg-indigo-400"></div>
            </div>
        </div>
    </div>
@endforeach
