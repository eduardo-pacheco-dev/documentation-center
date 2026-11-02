<?php

namespace Plugins\Analytics\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\Analytics\Enums\AnalyticsImportStatus;

#[Fillable([
    'user_id',
    'type',
    'status',
    'original_name',
    'path',
    'total_rows',
    'created_count',
    'updated_count',
    'unchanged_count',
    'skipped_count',
    'errors',
    'started_at',
    'completed_at',
])]
class AnalyticsImport extends AnalyticsModel
{
    public const TYPE_FAMS = 'fams';

    public const TYPE_AFL = 'afl';

    /**
     * The user that requested the import.
     */
    public function user(): BelongsTo
    {
        $related = (new User)->setConnection(config('database.default'));

        return $this->newBelongsTo(
            $related->newQuery(),
            $this,
            'user_id',
            $related->getKeyName(),
            'user',
        );
    }

    /**
     * Restrict a query to the imports the user owns.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /**
     * Whether the import has already stopped running.
     */
    public function isFinished(): bool
    {
        return $this->status->isFinished();
    }

    /**
     * The human readable import type.
     */
    public function typeLabel(): string
    {
        return $this->type === self::TYPE_AFL ? 'AFL' : 'FAMs';
    }

    /**
     * A short summary of the import progress or result.
     */
    public function summary(): string
    {
        if ($this->status === AnalyticsImportStatus::Failed) {
            return $this->errors[0] ?? 'A importação falhou.';
        }

        if ($this->status === AnalyticsImportStatus::Pending) {
            return 'Aguardando o processamento na fila.';
        }

        if ($this->status === AnalyticsImportStatus::Processing) {
            return 'Processando a planilha…';
        }

        $summary = sprintf(
            '%d novo(s) · %d atualizado(s) · %d sem alteração',
            $this->created_count,
            $this->updated_count,
            $this->unchanged_count,
        );

        if ($this->skipped_count > 0) {
            $summary .= sprintf(' · %d linha(s) ignorada(s)', $this->skipped_count);
        }

        return $summary;
    }

    /**
     * The number of rows already handled by the importer.
     *
     * @param  array{processed?: int}|null  $progress
     */
    public function processedRows(?array $progress = null): int
    {
        if ($progress !== null) {
            return max(0, (int) ($progress['processed'] ?? 0));
        }

        return (int) $this->created_count
            + (int) $this->updated_count
            + (int) $this->unchanged_count
            + (int) $this->skipped_count;
    }

    /**
     * The progress percentage while the import runs, or 100 when completed.
     *
     * @param  array{processed?: int, total?: int}|null  $progress
     */
    public function progressPercentage(?array $progress = null): ?int
    {
        if ($this->status === AnalyticsImportStatus::Completed) {
            return 100;
        }

        $total = (int) ($progress['total'] ?? $this->total_rows ?? 0);

        if ($total <= 0) {
            return null;
        }

        return max(0, min(99, (int) floor($this->processedRows($progress) / $total * 100)));
    }

    /**
     * How long the running import has been processing.
     */
    public function elapsedLabel(): ?string
    {
        if ($this->started_at === null) {
            return null;
        }

        return $this->formatDuration(max(0, (int) $this->started_at->diffInSeconds(now())));
    }

    /**
     * How long the finished import took.
     */
    public function durationLabel(): ?string
    {
        if ($this->started_at === null || $this->completed_at === null) {
            return null;
        }

        return $this->formatDuration(max(0, (int) $this->started_at->diffInSeconds($this->completed_at)));
    }

    /**
     * Format a number of seconds as a short human readable duration.
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return $remainingSeconds > 0 ? "{$minutes}min {$remainingSeconds}s" : "{$minutes}min";
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0 ? "{$hours}h {$remainingMinutes}min" : "{$hours}h";
    }

    /**
     * Flag the import as being processed.
     */
    public function markProcessing(?int $totalRows = null): void
    {
        $attributes = [
            'status' => AnalyticsImportStatus::Processing,
            'started_at' => now(),
            'errors' => null,
        ];

        if ($totalRows !== null) {
            $attributes['total_rows'] = $totalRows;
        }

        $this->forceFill($attributes)->save();
    }

    /**
     * Store the result of a successful import.
     *
     * @param  array{created: int, updated: int, unchanged: int, skipped?: int}  $result
     */
    public function markCompleted(array $result): void
    {
        $this->forceFill([
            'status' => AnalyticsImportStatus::Completed,
            'created_count' => $result['created'],
            'updated_count' => $result['updated'],
            'unchanged_count' => $result['unchanged'],
            'skipped_count' => $result['skipped'] ?? 0,
            'errors' => null,
            'completed_at' => now(),
        ])->save();
    }

    /**
     * Store the errors that prevented the import from finishing.
     *
     * @param  array<int, string>  $errors
     */
    public function markFailed(array $errors): void
    {
        $this->forceFill([
            'status' => AnalyticsImportStatus::Failed,
            'errors' => array_values(array_filter($errors, fn ($error): bool => is_string($error) && $error !== '')),
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
            'status' => AnalyticsImportStatus::class,
            'errors' => 'array',
            'total_rows' => 'integer',
            'created_count' => 'integer',
            'updated_count' => 'integer',
            'unchanged_count' => 'integer',
            'skipped_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
