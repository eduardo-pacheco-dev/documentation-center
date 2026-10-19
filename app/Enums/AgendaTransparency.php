<?php

namespace App\Enums;

enum AgendaTransparency: string
{
    case Opaque = 'opaque';
    case Transparent = 'transparent';

    /**
     * Get the human readable label for the transparency.
     */
    public function label(): string
    {
        return match ($this) {
            self::Opaque => 'Ocupado',
            self::Transparent => 'Disponível',
        };
    }

    /**
     * Determine whether the event blocks time in the free/busy view.
     */
    public function blocksTime(): bool
    {
        return $this === self::Opaque;
    }
}
