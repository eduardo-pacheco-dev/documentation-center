<?php

namespace Plugins\Stations\Enums;

enum StationLinkStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planejado',
            self::Active => 'Ativo',
            self::Maintenance => 'Em manutenção',
            self::Inactive => 'Desativado',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Planned => 'indigo',
            self::Active => 'green',
            self::Maintenance => 'amber',
            self::Inactive => 'gray',
        };
    }
}
