<?php

namespace App\Enums;

enum RadioLinkStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planejado',
            self::Active => 'Ativo',
            self::Maintenance => 'Em manutenção',
            self::Inactive => 'Desativado',
        };
    }
}
