<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\Backups\BackupRestorer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RestoreBackupJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(public Backup $backup) {}

    /**
     * Execute the job.
     */
    public function handle(BackupRestorer $restorer): void
    {
        $this->backup->markRestoring();

        try {
            $restorer->restore($this->backup);

            $this->backup->markRestored();
        } catch (Throwable $exception) {
            $this->backup->markRestoreFailed($exception->getMessage());

            throw $exception;
        }
    }
}
