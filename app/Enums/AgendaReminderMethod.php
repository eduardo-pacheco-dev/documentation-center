<?php

namespace App\Enums;

enum AgendaReminderMethod: string
{
    case Email = 'email';
    case Popup = 'popup';

    /**
     * Get the human readable label for the reminder method.
     */
    public function label(): string
    {
        return match ($this) {
            self::Email => 'E-mail',
            self::Popup => 'Notificação',
        };
    }
}
