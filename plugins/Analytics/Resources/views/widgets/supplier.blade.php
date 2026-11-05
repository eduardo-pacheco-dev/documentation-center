@php
    $maxSupplier = max(1, (int) $suppliers->max('total'));
    $maxStage = max(1, (int) $stages->max('total'));
@endphp

<section class="flex h-full flex-col rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="building-office" class="h-4 w-4" />
            </span>

            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Fornecedores e etapas</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Distribuição das {{ $total }} OS</p>
            </div>
        </div>

        <x-ui.badge tone="gray">{{ $total }}</x-ui.badge>
    </div>

    <div class="grid flex-1 gap-5 px-5 py-4 sm:grid-cols-2">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Por fornecedor</p>

            <div class="mt-3 space-y-3">
                @forelse ($suppliers as $supplier)
                    <div>
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="truncate font-medium text-gray-700 dark:text-gray-200">{{ $supplier->fornecedor }}</span>
                            <span class="shrink-0 font-mono text-gray-400 dark:text-gray-500">{{ $supplier->total }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-violet-500" style="width: {{ max(4, ((int) $supplier->total / $maxSupplier) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500">Nenhum fornecedor importado.</p>
                @endforelse
            </div>
        </div>

        <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Por etapa</p>

            <div class="mt-3 space-y-3">
                @forelse ($stages as $stage)
                    <div>
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="truncate font-medium text-gray-700 dark:text-gray-200">{{ $stage->etapa_processo }}</span>
                            <span class="shrink-0 font-mono text-gray-400 dark:text-gray-500">{{ $stage->total }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-sky-500" style="width: {{ max(4, ((int) $stage->total / $maxStage) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500">Nenhuma etapa importada.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 px-5 py-3 dark:border-white/10">
        <a href="{{ route('admin.analytics.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            Ver analytics
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        </a>
    </div>
</section>
