@php
    $progress = $progress ?? [];
    $activeImports = $imports->filter(fn ($import): bool => ! $import->isFinished());
@endphp

<div class="space-y-3">
    @foreach ($activeImports as $import)
        @php
            $state = $progress[$import->getKey()] ?? null;
            $pending = $import->status === \Plugins\Analytics\Enums\AnalyticsImportStatus::Pending;
            $percentage = $state === null ? null : $import->progressPercentage($state);
            $processed = $import->processedRows($state);
            $total = (int) ($state['total'] ?? $import->total_rows ?? 0);
            $created = (int) ($state['created'] ?? 0);
            $updated = (int) ($state['updated'] ?? 0);
            $unchanged = (int) ($state['unchanged'] ?? 0);
            $skipped = (int) ($state['skipped'] ?? 0);
        @endphp

        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/30 dark:bg-indigo-500/10">
            <div class="flex items-start gap-3">
                <span class="relative mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white">
                    @if (! $pending)
                        <span class="absolute inset-0 animate-ping rounded-full bg-indigo-400 opacity-40"></span>
                    @endif
                    <x-icon :name="$pending ? 'clock' : 'arrow-path'" class="relative h-4 w-4 {{ $pending ? '' : 'animate-spin' }}" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">
                            {{ $pending ? 'Importação '.$import->typeLabel().' na fila' : 'Importando '.$import->typeLabel() }}
                        </p>

                        @if ($percentage !== null)
                            <span class="text-xs font-semibold tabular-nums text-indigo-700 dark:text-indigo-300">{{ $percentage }}%</span>
                        @endif
                    </div>

                    <p class="mt-0.5 text-xs text-indigo-700 dark:text-indigo-300">
                        @if ($pending)
                            Aguardando um worker da fila iniciar o processamento.
                        @elseif ($state !== null && $total > 0)
                            {{ number_format($processed, 0, ',', '.') }} de {{ number_format($total, 0, ',', '.') }} linhas processadas.
                        @else
                            Lendo a planilha e preparando os registros…
                        @endif
                    </p>

                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-indigo-200/70 dark:bg-indigo-500/20">
                        @if ($percentage !== null)
                            <div class="h-full rounded-full bg-indigo-600 transition-all duration-500 dark:bg-indigo-400" style="width: {{ $percentage }}%"></div>
                        @elseif (! $pending)
                            <div class="h-full w-2/3 animate-pulse rounded-full bg-indigo-600 dark:bg-indigo-400"></div>
                        @endif
                    </div>

                    @if (! $pending)
                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-indigo-700 dark:text-indigo-300">
                            <span>{{ number_format($created, 0, ',', '.') }} novo(s)</span>
                            <span>{{ number_format($updated, 0, ',', '.') }} atualizado(s)</span>
                            <span>{{ number_format($unchanged, 0, ',', '.') }} sem alteração</span>
                            @if ($skipped > 0)
                                <span>{{ number_format($skipped, 0, ',', '.') }} ignorada(s)</span>
                            @endif
                        </div>
                    @endif

                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-indigo-600/80 dark:text-indigo-400/80">
                        <span class="truncate">{{ $import->original_name }}</span>
                        @if ($import->started_at !== null)
                            <span>há {{ $import->elapsedLabel() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
