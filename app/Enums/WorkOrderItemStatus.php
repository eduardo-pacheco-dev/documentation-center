<?php

namespace App\Enums;

enum WorkOrderItemStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Get the human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::InProgress => 'Em andamento',
            self::Completed => 'Concluído',
            self::Cancelled => 'Cancelado',
        };
    }

    /**
     * Determine whether the line item has been finished.
     */
    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
