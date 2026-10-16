<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BackupStatus;
use App\Http\Controllers\Controller;
use App\Jobs\RestoreBackupJob;
use App\Jobs\RunBackupJob;
use App\Models\Backup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    /**
     * List the backups and the surrounding statistics.
     */
    public function index(): View
    {
        $backups = Backup::query()
            ->with('user')
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Backup::query()->count(),
            'completed' => Backup::query()->where('status', BackupStatus::Completed)->count(),
            'in_progress' => Backup::query()->whereIn('status', [BackupStatus::Pending, BackupStatus::Running])->count(),
            'failed' => Backup::query()->where('status', BackupStatus::Failed)->count(),
            'restoring' => Backup::query()->whereIn('restore_status', [BackupStatus::Pending, BackupStatus::Running])->count(),
        ];

        return view('admin.backups.index', [
            'backups' => $backups,
            'stats' => $stats,
        ]);
    }

    /**
     * Queue a new full backup.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($this->restoreInProgress()) {
            return back()->withErrors(['backup' => 'Não é possível iniciar um backup enquanto uma restauração está em andamento.']);
        }

        $backup = $request->user()->backups()->create([
            'disk' => 'backups',
            'status' => BackupStatus::Pending,
        ]);

        RunBackupJob::dispatch($backup);

        return back()->with('status', 'Backup iniciado. O andamento é exibido abaixo.');
    }

    /**
     * Queue a restore of the given backup over the application.
     */
    public function restore(Backup $backup): RedirectResponse
    {
        abort_unless($backup->isRestorable(), 404);

        if ($this->restoreInProgress()) {
            return back()->withErrors(['backup' => 'Já existe uma restauração em andamento.']);
        }

        if ($this->backupInProgress()) {
            return back()->withErrors(['backup' => 'Aguarde o backup em andamento terminar antes de restaurar.']);
        }

        RestoreBackupJob::dispatch($backup);

        return back()->with('status', 'Restauração iniciada. A aplicação ficará em manutenção até terminar.');
    }

    /**
     * Return the current status of the most recent backups.
     */
    public function status(): JsonResponse
    {
        $backups = Backup::query()
            ->latest()
            ->limit(20)
            ->get(['id', 'status', 'size', 'error', 'created_at', 'completed_at', 'restore_status', 'restore_error', 'restored_at']);

        return response()->json($backups);
    }

    /**
     * Download a finished backup archive.
     */
    public function download(Backup $backup): StreamedResponse
    {
        abort_unless($backup->isDownloadable(), 404);

        return Storage::disk($backup->disk)->download($backup->path, $backup->filename());
    }

    /**
     * Remove a backup and its stored archive.
     */
    public function destroy(Backup $backup): RedirectResponse
    {
        if ($backup->path !== null) {
            Storage::disk($backup->disk)->delete($backup->path);
        }

        $backup->delete();

        return back()->with('status', 'Backup excluído.');
    }

    /**
     * Determine whether a restore is currently queued or running.
     */
    private function restoreInProgress(): bool
    {
        return Backup::query()
            ->whereIn('restore_status', [BackupStatus::Pending, BackupStatus::Running])
            ->exists();
    }

    /**
     * Determine whether a backup is currently queued or running.
     */
    private function backupInProgress(): bool
    {
        return Backup::query()
            ->whereIn('status', [BackupStatus::Pending, BackupStatus::Running])
            ->exists();
    }
}
