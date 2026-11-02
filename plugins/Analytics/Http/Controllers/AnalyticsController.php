<?php

namespace Plugins\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Plugins\Analytics\Exports\AnalyticsAflTemplateExport;
use Plugins\Analytics\Exports\AnalyticsRecordsTemplateExport;
use Plugins\Analytics\Http\Requests\ImportAnalyticsAflRequest;
use Plugins\Analytics\Http\Requests\ImportAnalyticsRecordsRequest;
use Plugins\Analytics\Imports\AnalyticsAflImport;
use Plugins\Analytics\Imports\AnalyticsRecordsImport;
use Plugins\Analytics\Services\AnalyticsAflImporter;
use Plugins\Analytics\Services\AnalyticsMetrics;
use Plugins\Analytics\Services\AnalyticsRecordImporter;
use Plugins\Analytics\Services\AnalyticsSnapshotter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    /**
     * Show the analytics dashboard.
     */
    public function index(Request $request): View
    {
        [$metrics, $period, $scope] = $this->resolve($request);

        return view('analytics::index', [
            'summary' => $metrics->summary(),
            'series' => $metrics->series(),
            'workOrderStatuses' => $metrics->workOrderStatuses(),
            'projectStatuses' => $metrics->projectStatuses(),
            'agendaStatuses' => $metrics->agendaStatuses(),
            'formStatuses' => $metrics->formStatuses(),
            'taskSplit' => $metrics->taskSplit(),
            'tasksByList' => $metrics->tasksByList(),
            'topProjects' => $metrics->topProjects(),
            'snapshots' => $metrics->snapshots(),
            'snapshotSeries' => $metrics->snapshotSeries(),
            'recordsSummary' => $metrics->recordsSummary(),
            'recordsByStage' => $metrics->recordsByStage(),
            'recordsByStatus' => $metrics->recordsByStatus(),
            'recordsBySupplier' => $metrics->recordsBySupplier(),
            'recentRecords' => $metrics->recentRecords(),
            'recordChanges' => $metrics->recentRecordChanges(),
            'aflSummary' => $metrics->aflSummary(),
            'aflByStatus' => $metrics->aflByStatus(),
            'aflByBillingStatus' => $metrics->aflByBillingStatus(),
            'aflByProject' => $metrics->aflByProject(),
            'recentAflRecords' => $metrics->recentAflRecords(),
            'aflChanges' => $metrics->recentAflChanges(),
            'from' => $period['from'],
            'to' => $period['to'],
            'days' => $period['days'],
            'scope' => $scope,
            'isGlobal' => $metrics->isGlobal(),
            'periods' => (array) config('analytics.periods', [30, 7, 90, 365]),
        ]);
    }

    /**
     * Export the current indicators and breakdowns as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        [$metrics, $period] = $this->resolve($request);

        $filename = sprintf('analytics-%s-%s.csv', $period['from']->format('Ymd'), $period['to']->format('Ymd'));

        return response()->streamDownload(function () use ($metrics): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Período', $metrics->from->format('d/m/Y').' a '.$metrics->to->format('d/m/Y')]);
            fputcsv($handle, ['Escopo', $metrics->isGlobal() ? 'Global' : 'Meus dados']);
            fputcsv($handle, []);

            fputcsv($handle, ['Indicador', 'Valor']);

            foreach ($metrics->summary() as $group => $values) {
                if (! is_array($values)) {
                    continue;
                }

                foreach ($values as $key => $value) {
                    fputcsv($handle, [$group.'.'.$key, $value]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Status de OS', 'Quantidade']);

            foreach ($metrics->workOrderStatuses() as $row) {
                fputcsv($handle, [$row['label'], $row['value']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Status de projeto', 'Quantidade']);

            foreach ($metrics->projectStatuses() as $row) {
                fputcsv($handle, [$row['label'], $row['value']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Etapa do processo', 'Registros']);

            foreach ($metrics->recordsByStage()['labels'] as $index => $label) {
                fputcsv($handle, [$label, $metrics->recordsByStage()['values'][$index] ?? 0]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Status FAMs', 'Registros']);

            foreach ($metrics->recordsByStatus()['labels'] as $index => $label) {
                fputcsv($handle, [$label, $metrics->recordsByStatus()['values'][$index] ?? 0]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Status geral AFL', 'Registros']);

            foreach ($metrics->aflByStatus()['labels'] as $index => $label) {
                fputcsv($handle, [$label, $metrics->aflByStatus()['values'][$index] ?? 0]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Status de faturamento AFL', 'Registros']);

            foreach ($metrics->aflByBillingStatus()['labels'] as $index => $label) {
                fputcsv($handle, [$label, $metrics->aflByBillingStatus()['values'][$index] ?? 0]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Lista de tarefas', 'Tarefas']);

            foreach ($metrics->tasksByList()['labels'] as $index => $label) {
                fputcsv($handle, [$label, $metrics->tasksByList()['values'][$index] ?? 0]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Store the snapshot of the day for the current scope.
     */
    public function snapshot(Request $request, AnalyticsSnapshotter $snapshotter): RedirectResponse
    {
        [$metrics, $period, $scope] = $this->resolve($request);

        $snapshotter->capture($metrics->user);

        return redirect()
            ->route('analytics.index', $request->only(['period', 'from', 'to', 'scope']))
            ->with('status', $scope === 'global'
                ? 'Snapshot global gerado.'
                : 'Seu snapshot foi gerado com sucesso.');
    }

    /**
     * Import the FAMs spreadsheet into the analytics records.
     */
    public function importRecords(ImportAnalyticsRecordsRequest $request, AnalyticsRecordImporter $importer): RedirectResponse
    {
        $sheets = Excel::toArray(new AnalyticsRecordsImport, $request->file('file'));

        $result = $importer->import($request->user(), $sheets[0] ?? []);

        return redirect()
            ->route('analytics.index', $request->only(['period', 'scope']))
            ->with('status', sprintf(
                'Importação FAMs concluída: %d novo(s), %d atualizado(s) e %d sem alteração.',
                $result['created'],
                $result['updated'],
                $result['unchanged'],
            ));
    }

    /**
     * Import the AFL spreadsheet into the analytics records.
     */
    public function importAfl(ImportAnalyticsAflRequest $request, AnalyticsAflImporter $importer): RedirectResponse
    {
        $sheets = Excel::toArray(new AnalyticsAflImport, $request->file('file'));

        $result = $importer->import($request->user(), $sheets[0] ?? []);

        return redirect()
            ->route('analytics.index', $request->only(['period', 'scope']))
            ->with('status', sprintf(
                'Importação AFL concluída: %d novo(s), %d atualizado(s) e %d sem alteração.',
                $result['created'],
                $result['updated'],
                $result['unchanged'],
            ));
    }

    /**
     * Download the AFL import template.
     */
    public function aflTemplate(): BinaryFileResponse
    {
        return Excel::download(new AnalyticsAflTemplateExport, 'modelo-analytics-afl.xlsx');
    }

    /**
     * Download the FAMs import template.
     */
    public function recordsTemplate(): BinaryFileResponse
    {
        return Excel::download(new AnalyticsRecordsTemplateExport, 'modelo-analytics-fams.xlsx');
    }

    /**
     * Resolve the period and scope of the request.
     *
     * @return array{0: AnalyticsMetrics, 1: array{from: CarbonImmutable, to: CarbonImmutable, days: int}, 2: string}
     */
    private function resolve(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'scope' => ['nullable', 'in:user,global'],
        ]);

        $days = (int) ($validated['period'] ?? (config('analytics.periods')[0] ?? 30));

        $to = isset($validated['to'])
            ? CarbonImmutable::parse($validated['to'])->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $from = isset($validated['from'])
            ? CarbonImmutable::parse($validated['from'])->startOfDay()
            : $to->subDays(max(1, $days) - 1)->startOfDay();

        $maxDays = (int) config('analytics.max_period_days', 366);

        if ($from->diffInDays($to) > $maxDays) {
            $from = $to->subDays($maxDays - 1)->startOfDay();
        }

        $scope = ($validated['scope'] ?? 'user') === 'global' ? 'global' : 'user';

        if ($scope === 'global') {
            abort_unless($request->user()?->is_admin === true, 403);
        }

        $metrics = new AnalyticsMetrics($from, $to, $scope === 'global' ? null : $request->user());

        return [
            $metrics,
            ['from' => $from, 'to' => $to, 'days' => (int) $from->diffInDays($to) + 1],
            $scope,
        ];
    }
}
