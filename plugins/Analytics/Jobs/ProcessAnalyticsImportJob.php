<?php

namespace Plugins\Analytics\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Plugins\Analytics\Enums\AnalyticsImportStatus;
use Plugins\Analytics\Exceptions\AnalyticsImportCanceledException;
use Plugins\Analytics\Imports\AnalyticsAflChunkImport;
use Plugins\Analytics\Imports\AnalyticsRecordsChunkImport;
use Plugins\Analytics\Models\AnalyticsImport;
use Plugins\Analytics\Services\AnalyticsAflImporter;
use Plugins\Analytics\Services\AnalyticsRecordImporter;
use Plugins\Analytics\Support\AnalyticsDatabase;
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
        $this->raiseMemoryLimit();

        $this->import->refresh();

        $disk = Storage::disk('local');

        if ($this->import->isFinished()) {
            AnalyticsImportProgress::forget($this->import->getKey());

            if ($disk->exists($this->import->path)) {
                $disk->delete($this->import->path);
            }

            return;
        }

        $this->import->markProcessing();

        AnalyticsImportProgress::forget($this->import->getKey());

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

        $shouldCancel = fn (): bool => AnalyticsImport::query()
            ->whereKey($this->import->getKey())
            ->where('status', AnalyticsImportStatus::Canceled->value)
            ->exists();

        try {
            $path = $disk->path($this->import->path);
            $totalRows = $this->totalRows($path);

            $this->import->markProcessing($totalRows > 0 ? $totalRows : null);

            if ($shouldCancel()) {
                throw new AnalyticsImportCanceledException;
            }

            if ($this->import->type === AnalyticsImport::TYPE_AFL) {
                $result = AnalyticsDatabase::transaction(function () use ($path, $user, $totalRows, $afl, $reportProgress, $shouldCancel): array {
                    $state = $afl->beginImport($totalRows);

                    $chunkImport = new AnalyticsAflChunkImport(function (array $rows) use ($afl, $user, &$state, $reportProgress, $shouldCancel): void {
                        $afl->importChunk($user, $rows, $state, $reportProgress, $shouldCancel);
                    });

                    Excel::import($chunkImport, $path);

                    return $afl->finishImport($state);
                });
            } else {
                $result = AnalyticsDatabase::transaction(function () use ($path, $user, $totalRows, $records, $reportProgress, $shouldCancel): array {
                    $state = $records->beginImport($totalRows);

                    $chunkImport = new AnalyticsRecordsChunkImport(function (array $rows) use ($records, $user, &$state, $reportProgress, $shouldCancel): void {
                        $records->importChunk($user, $rows, $state, $reportProgress, $shouldCancel);
                    });

                    Excel::import($chunkImport, $path);

                    return $records->finishImport($state);
                });
            }

            $this->import->refresh();

            if ($this->import->status !== AnalyticsImportStatus::Canceled) {
                $this->import->markCompleted($result);
            }
        } catch (AnalyticsImportCanceledException) {
            $this->import->refresh();

            if (! $this->import->isFinished()) {
                $this->import->markCanceled();
            }
        } catch (ValidationException $exception) {
            $this->import->refresh();

            if (! $this->import->isFinished()) {
                $this->import->markFailed($exception->errors()['import'] ?? collect($exception->errors())->flatten()->all());
            }

            throw $exception;
        } catch (Throwable $exception) {
            $this->import->refresh();

            if (! $this->import->isFinished()) {
                $this->import->markFailed([$exception->getMessage()]);
            }

            throw $exception;
        } finally {
            AnalyticsImportProgress::forget($this->import->getKey());

            $disk->delete($this->import->path);
        }
    }

    /**
     * Read the worksheet dimensions without loading the cells into memory.
     */
    private function totalRows(string $path): int
    {
        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);

            $sheets = $reader->listWorksheetInfo($path);

            return (int) ($sheets[0]['totalRows'] ?? 0);
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Raise the worker memory limit before reading a large spreadsheet.
     */
    private function raiseMemoryLimit(): void
    {
        $target = trim((string) config('analytics.import_memory_limit', '1G'));

        if ($target === '' || $target === '-1') {
            return;
        }

        $current = trim((string) ini_get('memory_limit'));

        if ($current === '-1') {
            return;
        }

        $targetBytes = $this->memoryLimitInBytes($target);

        if ($targetBytes === null) {
            return;
        }

        $currentBytes = $this->memoryLimitInBytes($current);

        if ($currentBytes !== null && $currentBytes >= $targetBytes) {
            return;
        }

        @ini_set('memory_limit', $target);
    }

    /**
     * Convert a PHP memory limit shorthand ("128M", "1G", ...) to bytes.
     */
    private function memoryLimitInBytes(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return null;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) rtrim($value, 'gGmMkK');

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
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
