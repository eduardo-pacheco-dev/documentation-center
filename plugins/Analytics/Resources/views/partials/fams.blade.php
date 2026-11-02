{{-- FAMs --}}
<div id="panel-fams" data-panel="fams" role="tabpanel" aria-labelledby="tab-fams" class="hidden space-y-4">
    @if (! is_array($recordsSummary))
        <x-analytics::card class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            A tabela de registros FAMs ainda não foi migrada.
        </x-analytics::card>
    @else
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <x-analytics::import-panel
                title="Importar dados FAMs"
                description="OC, OS, fornecedor, etapa, elemento, endereço, node name, localização e status. Reimportar cria uma nova versão quando houver alteração."
                action="analytics.records.import"
                template-route="analytics.records.template"
                submit-label="Importar FAMs"
                file-id="records-file"
                max-size="10 MB"
                :summary="[
                    $recordsSummary['total'].' registros',
                    $recordsSummary['versions'].' versões',
                    $recordsSummary['in_period'].' no período',
                ]"
                :days="$days"
                :scope="$scope"
            />

            <x-analytics::panel title="Etapas do processo">
                @if ($recordsByStage['labels'] === [])
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum registro FAMs importado.</p>
                @else
                    <x-analytics::chart-canvas id="analytics-records-stages" height="h-64" class="mt-4" />
                @endif
            </x-analytics::panel>

            <x-analytics::panel title="Status FAMs">
                <x-slot:actions>
                    <x-ui.badge tone="indigo">{{ $recordsSummary['total'] }}</x-ui.badge>
                </x-slot:actions>

                <x-analytics::status-list
                    :labels="$recordsByStatus['labels']"
                    :values="$recordsByStatus['values']"
                    empty="Nenhum status registrado."
                    truncate
                    class="mt-4"
                />

                @if ($recordsBySupplier['labels'] !== [])
                    <div class="mt-4 border-t border-gray-100 pt-4 dark:border-white/10">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Fornecedores</p>

                        <x-analytics::status-list
                            :labels="$recordsBySupplier['labels']"
                            :values="$recordsBySupplier['values']"
                            truncate
                            class="mt-2"
                        />
                    </div>
                @endif
            </x-analytics::panel>
        </div>

        @if ($recentRecords !== [])
            <x-analytics::table-card title="Últimos registros FAMs" caption="versão mais recente de cada OC + OS">
                <x-analytics::data-table :columns="['OC', 'OS', 'Criação', 'Fornecedor', 'Etapa', 'Local', 'Node', 'Status', 'Versões']">
                    @foreach ($recentRecords as $record)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $record['oc'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $record['os'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['created_on'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['supplier'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['process_stage'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['city'] ?: '—' }}{{ $record['state'] ? ' / '.$record['state'] : '' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $record['node_name'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['status'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-ui.badge :tone="$record['versions'] > 1 ? 'indigo' : 'gray'">{{ $record['versions'] }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-analytics::data-table>
            </x-analytics::table-card>
        @endif

        @if ($recordChanges !== [])
            <x-analytics::table-card title="Histórico de alterações FAMs" description="Novas versões gravadas nas reimportações.">
                <x-analytics::data-table :columns="['Importado em', 'OC', 'OS', 'Etapa', 'Status']">
                    @foreach ($recordChanges as $change)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['imported_at'] ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $change['oc'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $change['os'] }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['process_stage'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['status'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </x-analytics::data-table>
            </x-analytics::table-card>
        @endif
    @endif
</div>
