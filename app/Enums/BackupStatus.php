<?php

namespace App\Enums;

enum BackupStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * Get the human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Na fila',
            self::Running => 'Em andamento',
            self::Completed => 'Concluído',
            self::Failed => 'Falhou',
        };
    }

    /**
     * Determine whether the backup is still being prepared.
     */
    public function isInProgress(): bool
    {
        return in_array($this, [self::Pending, self::Running], true);
    }

    /**
     * Determine whether the backup reached a final state.
     */
    public function isFinished(): bool
    {
        return ! $this->isInProgress();
    }
}
