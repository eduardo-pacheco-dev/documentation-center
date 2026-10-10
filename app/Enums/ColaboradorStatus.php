<?php

namespace App\Enums;

enum ColaboradorStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
        };
    }
}
