@if ($imports->isNotEmpty())
    <div class="mt-6 border-t border-gray-100 pt-4 dark:border-white/10">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Importações recentes</h3>
            <span class="text-xs text-gray-400 dark:text-gray-500">atualiza automaticamente</span>
        </div>

        <ul class="mt-2 space-y-2">
            @foreach ($imports as $import)
                <li class="rounded-xl border border-gray-200/80 px-3 py-2 dark:border-white/10">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <x-ui.badge :tone="$import->status->tone()">{{ $import->status->label() }}</x-ui.badge>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ $import->typeLabel() }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $import->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            @if ($import->total_rows > 0)
                                {{ number_format($import->total_rows, 0, ',', '.') }} linha(s)
                            @endif
                            @if ($import->durationLabel() !== null)
                                · {{ $import->durationLabel() }}
                            @endif
                        </span>
                    </div>

                    <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-gray-600 dark:text-gray-300">{{ $import->original_name }}</span>
                        — {{ $import->summary() }}
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
@endif
