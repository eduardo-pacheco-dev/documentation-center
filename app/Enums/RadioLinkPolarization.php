<?php

namespace App\Enums;

enum RadioLinkPolarization: string
{
    case Vertical = 'vertical';
    case Horizontal = 'horizontal';
    case Dual = 'dual';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Vertical => 'Vertical',
            self::Horizontal => 'Horizontal',
            self::Dual => 'Dupla (V+H)',
        };
    }
}
