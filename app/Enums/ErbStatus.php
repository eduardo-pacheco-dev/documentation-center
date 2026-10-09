<?php

namespace App\Enums;

enum ErbStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Maintenance => 'Em manutenção',
            self::Inactive => 'Desativada',
        };
    }
}
