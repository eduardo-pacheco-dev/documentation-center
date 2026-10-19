<?php

namespace App\Enums;

enum AgendaEventVisibility: string
{
    case Default = 'default';
    case Public = 'public';
    case Private = 'private';

    /**
     * Get the human readable label for the visibility.
     */
    public function label(): string
    {
        return match ($this) {
            self::Default => 'Padrão do calendário',
            self::Public => 'Público',
            self::Private => 'Privado',
        };
    }
}
