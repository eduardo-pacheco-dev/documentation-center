<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\Backups\BackupManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RunBackupJob implements ShouldQueue
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
    public function handle(BackupManager $manager): void
    {
        $this->backup->markRunning();

        try {
            $path = $manager->build($this->backup);

            $size = (int) Storage::disk($this->backup->disk)->size($path);

            $this->backup->markCompleted($path, $size);
        } catch (Throwable $exception) {
            $this->backup->markFailed($exception->getMessage());

            throw $exception;
        }
    }
}
