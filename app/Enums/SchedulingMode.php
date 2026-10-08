<?php

namespace App\Enums;

enum SchedulingMode: string
{
    case Auto = 'auto';
    case Manual = 'manual';

    /**
     * Whether the engine recalculates the task dates.
     */
    public function isAutoScheduled(): bool
    {
        return $this === self::Auto;
    }

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Auto => 'Agendamento automático',
            self::Manual => 'Agendamento manual',
        };
    }
}
