<?php

namespace App\Models;

use App\Enums\BackupStatus;
use Database\Factories\BackupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'disk', 'status'])]
class Backup extends Model
{
    /** @use HasFactory<BackupFactory> */
    use HasFactory;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'disk' => 'backups',
        'status' => 'pending',
    ];

    /**
     * The user that requested the backup.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine whether the backup is still being prepared.
     */
    public function isInProgress(): bool
    {
        return $this->status->isInProgress();
    }

    /**
     * Determine whether the backup archive is ready to download.
     */
    public function isDownloadable(): bool
    {
        return $this->status === BackupStatus::Completed
            && $this->path !== null
            && Storage::disk($this->disk)->exists($this->path);
    }

    /**
     * The file name of the backup archive, when it exists.
     */
    public function filename(): ?string
    {
        return $this->path !== null ? basename($this->path) : null;
    }

    /**
     * Flag the backup as being prepared.
     */
    public function markRunning(): void
    {
        $this->forceFill([
            'status' => BackupStatus::Running,
            'started_at' => now(),
            'error' => null,
        ])->save();
    }

    /**
     * Flag the backup as ready and record the stored archive.
     */
    public function markCompleted(string $path, int $size): void
    {
        $this->forceFill([
            'status' => BackupStatus::Completed,
            'path' => $path,
            'size' => $size,
            'completed_at' => now(),
            'error' => null,
        ])->save();
    }

    /**
     * Flag the backup as failed and store the reason.
     */
    public function markFailed(string $error): void
    {
        $this->forceFill([
            'status' => BackupStatus::Failed,
            'error' => Str::limit($error, 2000),
            'completed_at' => now(),
        ])->save();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BackupStatus::class,
            'size' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
