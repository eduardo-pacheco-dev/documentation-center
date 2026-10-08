<?php

namespace App\Enums;

enum TaskType: string
{
    case FixedDuration = 'fixed_duration';
    case FixedWork = 'fixed_work';
    case FixedUnits = 'fixed_units';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::FixedDuration => 'Duração fixa',
            self::FixedWork => 'Esforço fixo',
            self::FixedUnits => 'Unidades fixas',
        };
    }
}
