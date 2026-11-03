{{-- Visão geral: somente FAMs e AFL --}}
@php
    $recordsTotal = $recordsSummary['total'] ?? 0;
    $recordsInPeriod = $recordsSummary['in_period'] ?? 0;
    $aflTotal = $aflSummary['total'] ?? 0;
    $aflInPeriod = $aflSummary['in_period'] ?? 0;
@endphp

<div id="panel-overview" data-panel="overview" role="tabpanel" aria-labelledby="tab-overview" class="space-y-4">
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-analytics::panel
            class="xl:col-span-2"
            title="Registros no período"
            :description="'FAMs e AFL ('.($days <= 31 ? 'por dia' : 'por mês').').'"
        >
            <x-slot:actions>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    {{ number_format($recordsInPeriod, 0, ',', '.') }} FAMs · {{ number_format($aflInPeriod, 0, ',', '.') }} AFL
                </span>
            </x-slot:actions>

            <x-analytics::apex-chart id="analytics-series" class="mt-4" />
        </x-analytics::panel>

        <x-analytics::panel title="Status FAMs" :description="number_format($recordsTotal, 0, ',', '.').' registros no total'">
            @if ($recordsByStatus['labels'] === [])
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum status registrado.</p>
            @else
                <x-analytics::chart-canvas id="analytics-overview-records-status" height="h-56" class="mt-3" />
            @endif
        </x-analytics::panel>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <x-analytics::panel title="Etapas do processo FAMs">
            @if ($recordsByStage['labels'] === [])
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhuma etapa registrada.</p>
            @else
                <x-analytics::chart-canvas id="analytics-overview-records-stages" height="h-64" class="mt-4" />
            @endif
        </x-analytics::panel>

        <x-analytics::panel title="Status geral AFL" :description="number_format($aflTotal, 0, ',', '.').' registros no total'">
            @if ($aflByStatus['labels'] === [])
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum status registrado.</p>
            @else
                <x-analytics::chart-canvas id="analytics-overview-afl-status" height="h-64" class="mt-4" />
            @endif
        </x-analytics::panel>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-analytics::panel title="Faturamento AFL">
            @if ($aflByBillingStatus['labels'] === [])
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum status de faturamento registrado.</p>
            @else
                <x-analytics::chart-canvas id="analytics-overview-afl-billing" height="h-56" class="mt-3" />
            @endif
        </x-analytics::panel>

        <x-analytics::panel title="Fornecedores FAMs">
            @if ($recordsBySupplier['labels'] === [])
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum fornecedor registrado.</p>
            @else
                <x-analytics::chart-canvas id="analytics-overview-records-suppliers" height="h-56" class="mt-3" />
            @endif
        </x-analytics::panel>

        <x-analytics::panel title="Projetos AFL">
            @if ($aflByProject['labels'] === [])
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum projeto registrado.</p>
            @else
                <x-analytics::chart-canvas id="analytics-overview-afl-projects" height="h-56" class="mt-3" />
            @endif
        </x-analytics::panel>
    </div>
</div>
