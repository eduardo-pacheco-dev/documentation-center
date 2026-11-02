<?php

namespace Plugins\Stations\Enums;

enum StationStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Maintenance => 'Em manutenção',
            self::Inactive => 'Desativada',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Maintenance => 'amber',
            self::Inactive => 'gray',
        };
    }
}
