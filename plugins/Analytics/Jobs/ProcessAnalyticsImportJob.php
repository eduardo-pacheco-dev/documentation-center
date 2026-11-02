<?php

namespace Plugins\Analytics\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Plugins\Analytics\Imports\AnalyticsAflImport;
use Plugins\Analytics\Imports\AnalyticsRecordsImport;
use Plugins\Analytics\Models\AnalyticsImport;
use Plugins\Analytics\Services\AnalyticsAflImporter;
use Plugins\Analytics\Services\AnalyticsRecordImporter;
use Plugins\Analytics\Support\AnalyticsImportProgress;
use Throwable;

/**
 * Parses and imports an analytics spreadsheet in the background.
 *
 * Large FAMs/AFL files can take minutes and must not block the request.
 */
class ProcessAnalyticsImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * Imports are append-only and skip unchanged rows, so retrying after an
     * interrupted worker is safe.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(public AnalyticsImport $import) {}

    /**
     * Execute the job.
     */
    public function handle(AnalyticsRecordImporter $records, AnalyticsAflImporter $afl): void
    {
        $this->import->markProcessing();

        AnalyticsImportProgress::forget($this->import->getKey());

        $disk = Storage::disk('local');

        if (! $disk->exists($this->import->path)) {
            $this->import->markFailed(['O arquivo enviado não foi encontrado.']);

            return;
        }

        $user = User::query()->find($this->import->user_id);

        if (! $user instanceof User) {
            $this->import->markFailed(['O usuário solicitante não existe mais.']);

            return;
        }

        $lastReportAt = 0.0;

        $reportProgress = function (array $progress) use (&$lastReportAt): void {
            $now = microtime(true);
            $finished = ($progress['processed'] ?? 0) >= ($progress['total'] ?? 1);

            if (! $finished && ($now - $lastReportAt) < 1.5) {
                return;
            }

            $lastReportAt = $now;

            AnalyticsImportProgress::put($this->import->getKey(), $progress);
        };

        try {
            $path = $disk->path($this->import->path);

            if ($this->import->type === AnalyticsImport::TYPE_AFL) {
                $sheets = Excel::toArray(new AnalyticsAflImport, $path);
                $rows = $sheets[0] ?? [];

                $this->import->markProcessing(count($rows));

                $result = $afl->import($user, $rows, $reportProgress);
            } else {
                $sheets = Excel::toArray(new AnalyticsRecordsImport, $path);
                $rows = $sheets[0] ?? [];

                $this->import->markProcessing(count($rows));

                $result = $records->import($user, $rows, $reportProgress);
            }

            $this->import->markCompleted($result);
        } catch (ValidationException $exception) {
            $this->import->markFailed($exception->errors()['import'] ?? collect($exception->errors())->flatten()->all());

            throw $exception;
        } catch (Throwable $exception) {
            $this->import->markFailed([$exception->getMessage()]);

            throw $exception;
        } finally {
            AnalyticsImportProgress::forget($this->import->getKey());

            $disk->delete($this->import->path);
        }
    }

    /**
     * Handle a job failure that happened outside the handle method.
     */
    public function failed(Throwable $exception): void
    {
        if ($this->import->fresh()?->isFinished()) {
            return;
        }

        $this->import->markFailed([$exception->getMessage()]);
    }
}
