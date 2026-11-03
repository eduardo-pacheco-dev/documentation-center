{{-- Nokia --}}
<div id="panel-nokia" data-panel="nokia" role="tabpanel" aria-labelledby="tab-nokia" class="hidden space-y-4">
    @if (! is_array($nokiaSummary))
        <x-analytics::card class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            A tabela de registros Nokia ainda não foi migrada.
        </x-analytics::card>
    @else
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-analytics::panel title="Status geral Nokia" :description="number_format($nokiaSummary['total'], 0, ',', '.').' registros no total'">
                @if ($nokiaByStatus['labels'] === [])
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum registro Nokia importado.</p>
                @else
                    <x-analytics::chart-canvas id="analytics-nokia-status" height="h-64" class="mt-4" />
                @endif
            </x-analytics::panel>

            <x-analytics::panel title="Regionais Nokia">
                <x-slot:actions>
                    <x-ui.badge tone="sky">{{ $nokiaSummary['total'] }}</x-ui.badge>
                </x-slot:actions>

                <x-analytics::status-list
                    :labels="$nokiaByRegional['labels']"
                    :values="$nokiaByRegional['values']"
                    empty="Nenhuma regional registrada."
                    truncate
                    class="mt-4"
                />

                <div class="mt-4 border-t border-gray-100 pt-4 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
                    {{ number_format($nokiaSummary['in_period'], 0, ',', '.') }} importados no período ·
                    {{ number_format($nokiaSummary['versions'], 0, ',', '.') }} versões no histórico
                </div>
            </x-analytics::panel>
        </div>

        @if ($recentNokiaRecords !== [])
            <x-analytics::table-card title="Últimos registros Nokia" caption="versão mais recente de cada OC">
                <x-analytics::data-table :columns="['OC', 'SITE ID', 'Cidade / UF', 'Regional', 'Projeto', 'GAP', 'Macro status', 'Status geral', 'Versões']">
                    @foreach ($recentNokiaRecords as $record)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $record['oc'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $record['site_id'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['city'] ?: '—' }}{{ $record['uf'] ? ' / '.$record['uf'] : '' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['regional'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['project'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['gap_status'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['macro_status'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['status_geral'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-ui.badge :tone="$record['versions'] > 1 ? 'indigo' : 'gray'">{{ $record['versions'] }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-analytics::data-table>
            </x-analytics::table-card>
        @endif

        @if ($nokiaChanges !== [])
            <x-analytics::table-card title="Histórico de alterações Nokia" description="Novas versões gravadas nas reimportações.">
                <x-analytics::data-table :columns="['Importado em', 'OC', 'SITE ID', 'Status geral']">
                    @foreach ($nokiaChanges as $change)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['imported_at'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $change['oc'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $change['site_id'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['status_geral'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </x-analytics::data-table>
            </x-analytics::table-card>
        @endif
    @endif
</div>
