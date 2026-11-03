{{-- AFL --}}
<div id="panel-afl" data-panel="afl" role="tabpanel" aria-labelledby="tab-afl" class="hidden space-y-4">
    @if (! is_array($aflSummary))
        <x-analytics::card class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            A tabela de registros AFL ainda não foi migrada.
        </x-analytics::card>
    @else
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-analytics::panel title="Status geral AFL">
                @if ($aflByStatus['labels'] === [])
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum registro AFL importado.</p>
                @else
                    <x-analytics::chart-canvas id="analytics-afl-status" height="h-64" class="mt-4" />
                @endif
            </x-analytics::panel>

            <x-analytics::panel title="Faturamento AFL">
                <x-analytics::status-list
                    :labels="$aflByBillingStatus['labels']"
                    :values="$aflByBillingStatus['values']"
                    empty="Nenhum status de faturamento registrado."
                    truncate
                    class="mt-4"
                />

                @if ($aflByProject['labels'] !== [])
                    <div class="mt-4 border-t border-gray-100 pt-4 dark:border-white/10">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Projetos</p>

                        <x-analytics::status-list
                            :labels="$aflByProject['labels']"
                            :values="$aflByProject['values']"
                            truncate
                            class="mt-2"
                        />
                    </div>
                @endif
            </x-analytics::panel>
        </div>

        @if ($recentAflRecords !== [])
            <x-analytics::table-card title="Últimos registros AFL" caption="versão mais recente de cada Cód_AFL">
                <x-analytics::data-table :columns="['Código', 'Projeto', 'Site A / B', 'Supervisor', 'Status geral', 'Faturamento', 'Versões']">
                    @foreach ($recentAflRecords as $record)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $record['code'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['project'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $record['site_a'] ?: '—' }} / {{ $record['site_b'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['supervisor'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['general_status'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['billing_status'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-ui.badge :tone="$record['versions'] > 1 ? 'indigo' : 'gray'">{{ $record['versions'] }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-analytics::data-table>
            </x-analytics::table-card>
        @endif

        @if ($aflChanges !== [])
            <x-analytics::table-card title="Histórico de alterações AFL" description="Novas versões gravadas nas reimportações.">
                <x-analytics::data-table :columns="['Importado em', 'Código', 'Status geral', 'Faturamento']">
                    @foreach ($aflChanges as $change)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['imported_at'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $change['code'] }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['general_status'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['billing_status'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </x-analytics::data-table>
            </x-analytics::table-card>
        @endif
    @endif
</div>
