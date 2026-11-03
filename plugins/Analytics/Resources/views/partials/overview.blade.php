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
            <x-analytics::status-list
                :labels="$recordsByStatus['labels']"
                :values="$recordsByStatus['values']"
                empty="Nenhum status registrado."
                truncate
                class="mt-4"
            />
        </x-analytics::panel>

        <x-analytics::panel title="Etapas do processo FAMs">
            <x-analytics::status-list
                :labels="$recordsByStage['labels']"
                :values="$recordsByStage['values']"
                empty="Nenhuma etapa registrada."
                truncate
                class="mt-4"
            />
        </x-analytics::panel>

        <x-analytics::panel title="Status geral AFL" :description="number_format($aflTotal, 0, ',', '.').' registros no total'">
            <x-analytics::status-list
                :labels="$aflByStatus['labels']"
                :values="$aflByStatus['values']"
                empty="Nenhum status registrado."
                truncate
                class="mt-4"
            />
        </x-analytics::panel>

        <x-analytics::panel title="Faturamento AFL">
            <x-analytics::status-list
                :labels="$aflByBillingStatus['labels']"
                :values="$aflByBillingStatus['values']"
                empty="Nenhum status de faturamento registrado."
                truncate
                class="mt-4"
            />
        </x-analytics::panel>
    </div>

    @if ($recordsBySupplier['labels'] !== [] || $aflByProject['labels'] !== [])
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @if ($recordsBySupplier['labels'] !== [])
                <x-analytics::panel title="Fornecedores FAMs">
                    <x-analytics::status-list
                        :labels="$recordsBySupplier['labels']"
                        :values="$recordsBySupplier['values']"
                        truncate
                        class="mt-4"
                    />
                </x-analytics::panel>
            @endif

            @if ($aflByProject['labels'] !== [])
                <x-analytics::panel title="Projetos AFL">
                    <x-analytics::status-list
                        :labels="$aflByProject['labels']"
                        :values="$aflByProject['values']"
                        truncate
                        class="mt-4"
                    />
                </x-analytics::panel>
            @endif
        </div>
    @endif
</div>
