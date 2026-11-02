{{-- Snapshots --}}
<div id="panel-snapshots" data-panel="snapshots" role="tabpanel" aria-labelledby="tab-snapshots" class="hidden space-y-4">
    <x-analytics::panel title="Histórico de snapshots" description="Evolução dos totais registrados dia a dia.">
        @if ($hasSnapshotSeries)
            <x-slot:actions>
                <x-ui.badge tone="gray">{{ count($snapshotSeries['labels']) }} dias</x-ui.badge>
            </x-slot:actions>

            <x-analytics::apex-chart id="analytics-snapshots" class="mt-4" />
        @else
            <div class="mt-4 flex flex-col items-start gap-3 rounded-xl border border-dashed border-gray-300 p-5 text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                <p>Ainda não há histórico suficiente para o gráfico. Gere o snapshot diário para acompanhar a evolução.</p>

                <form method="POST" action="{{ route('analytics.snapshot') }}">
                    @csrf
                    <input type="hidden" name="period" value="{{ $days }}">
                    <input type="hidden" name="scope" value="{{ $scope }}">

                    <x-ui.action type="submit" icon="camera">Gerar snapshot de hoje</x-ui.action>
                </form>

                <p class="text-xs text-gray-400 dark:text-gray-500">
                    Ou agende <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-white/[0.06]">php artisan analytics:snapshot</code> no cron.
                </p>
            </div>
        @endif
    </x-analytics::panel>

    @if ($snapshots !== [])
        <x-analytics::table-card title="Últimos snapshots">
            <x-analytics::data-table :columns="['Data', 'Projetos', 'OS', 'Tarefas', 'Documentos', 'Formulários']">
                @foreach ($snapshots as $snapshot)
                    <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                        <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $snapshot['captured_on'] }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['projects']['total'] ?? 0 }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['work_orders']['total'] ?? 0 }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['tasks']['total'] ?? 0 }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['documents']['total'] ?? 0 }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['forms']['total'] ?? 0 }}</td>
                    </tr>
                @endforeach
            </x-analytics::data-table>
        </x-analytics::table-card>
    @endif
</div>
