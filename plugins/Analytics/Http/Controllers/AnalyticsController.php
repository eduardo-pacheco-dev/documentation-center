<?php

namespace Plugins\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Plugins\Analytics\Enums\AnalyticsImportStatus;
use Plugins\Analytics\Exports\AnalyticsAflTemplateExport;
use Plugins\Analytics\Exports\AnalyticsRecordsTemplateExport;
use Plugins\Analytics\Http\Requests\ImportAnalyticsAflRequest;
use Plugins\Analytics\Http\Requests\ImportAnalyticsRecordsRequest;
use Plugins\Analytics\Jobs\ProcessAnalyticsImportJob;
use Plugins\Analytics\Models\AnalyticsImport;
use Plugins\Analytics\Services\AnalyticsContext;
use Plugins\Analytics\Services\AnalyticsSnapshotter;
use Plugins\Analytics\Support\AnalyticsDatabase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    /**
     * Show the analytics dashboard.
     */
    public function index(Request $request): View
    {
        $context = AnalyticsContext::fromRequest($request);
        $metrics = $context->metrics;
        $period = ['from' => $context->from, 'to' => $context->to, 'days' => $context->days];
        $scope = $context->scope;
        $recentImports = $this->recentImports($request);

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
            'recentImports' => $recentImports,
            'hasPendingImports' => $recentImports->contains(fn (AnalyticsImport $import): bool => ! $import->isFinished()),
        ]);
    }

    /**
     * Export the current indicators and breakdowns as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $context = AnalyticsContext::fromRequest($request);
        $metrics = $context->metrics;
        $period = ['from' => $context->from, 'to' => $context->to, 'days' => $context->days];

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
        $context = AnalyticsContext::fromRequest($request);

        if (! AnalyticsDatabase::schema()->hasTable('analytics_snapshots')) {
            return back()->withErrors(['snapshot' => 'A tabela de snapshots ainda não foi migrada no banco de analytics.']);
        }

        $snapshotter->capture($context->metrics->user);

        return redirect()
            ->route('analytics.index', $request->only(['period', 'from', 'to', 'scope']))
            ->with('status', $context->scope === 'global'
                ? 'Snapshot global gerado.'
                : 'Seu snapshot foi gerado com sucesso.');
    }

    /**
     * Queue the FAMs spreadsheet import.
     */
    public function importRecords(ImportAnalyticsRecordsRequest $request): RedirectResponse
    {
        return $this->queueImport($request, AnalyticsImport::TYPE_FAMS, 'Importação FAMs');
    }

    /**
     * Queue the AFL spreadsheet import.
     */
    public function importAfl(ImportAnalyticsAflRequest $request): RedirectResponse
    {
        return $this->queueImport($request, AnalyticsImport::TYPE_AFL, 'Importação AFL');
    }

    /**
     * Return the HTML of the latest imports so the dashboard can refresh them.
     */
    public function importsStatus(Request $request): JsonResponse
    {
        $imports = $this->recentImports($request);

        return response()->json([
            'html' => view('analytics::partials.import-history', ['imports' => $imports])->render(),
            'progress_html' => view('analytics::partials.import-progress', ['imports' => $imports])->render(),
            'pending' => $imports->contains(fn (AnalyticsImport $import): bool => ! $import->isFinished()),
            'imports' => $imports->map(fn (AnalyticsImport $import): array => [
                'id' => $import->getKey(),
                'type' => $import->type,
                'status' => $import->status->value,
                'status_label' => $import->status->label(),
                'original_name' => $import->original_name,
                'total_rows' => $import->total_rows,
                'created_at' => $import->created_at?->toIso8601String(),
                'summary' => $import->summary(),
            ])->values(),
        ]);
    }

    /**
     * Store the uploaded spreadsheet and dispatch its processing job.
     */
    private function queueImport(Request $request, string $type, string $label): RedirectResponse
    {
        if (! AnalyticsDatabase::schema()->hasTable('analytics_imports')) {
            throw ValidationException::withMessages([
                'file' => 'A tabela de importações ainda não foi migrada no banco de analytics.',
            ]);
        }

        $file = $request->file('file');
        $path = $file->store('analytics-imports', 'local');

        if (! is_string($path)) {
            throw ValidationException::withMessages(['file' => 'Não foi possível armazenar o arquivo enviado.']);
        }

        $import = AnalyticsImport::query()->create([
            'user_id' => $request->user()->getKey(),
            'type' => $type,
            'status' => AnalyticsImportStatus::Pending,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
        ]);

        ProcessAnalyticsImportJob::dispatch($import);

        $import->refresh();

        $redirect = redirect()
            ->route('analytics.index', $request->only(['period', 'scope']));

        if ($import->status === AnalyticsImportStatus::Failed) {
            return $redirect->withErrors(['import' => $import->errors ?? ['A importação falhou.']]);
        }

        if ($import->status === AnalyticsImportStatus::Completed) {
            return $redirect->with('status', $this->completedMessage($label, $import));
        }

        return $redirect
            ->with('status', "{$label} enfileirada. O andamento aparece em “Importações recentes”.")
            ->with('import_started', true);
    }

    /**
     * The success message shown after a synchronous import finishes.
     */
    private function completedMessage(string $label, AnalyticsImport $import): string
    {
        $message = sprintf(
            '%s concluída: %d novo(s), %d atualizado(s) e %d sem alteração.',
            $label,
            $import->created_count,
            $import->updated_count,
            $import->unchanged_count,
        );

        if ($import->skipped_count > 0) {
            $message .= sprintf(' %d linha(s) vazia(s) ignorada(s).', $import->skipped_count);
        }

        return $message;
    }

    /**
     * The latest imports requested by the authenticated user.
     *
     * @return Collection<int, AnalyticsImport>
     */
    private function recentImports(Request $request): Collection
    {
        if (! AnalyticsDatabase::schema()->hasTable('analytics_imports')) {
            return collect();
        }

        return AnalyticsImport::query()
            ->ownedBy($request->user())
            ->latest()
            ->limit(5)
            ->get();
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
}
