<?php

namespace App\Enums;

enum AgendaEventStatus: string
{
    case Confirmed = 'confirmed';
    case Tentative = 'tentative';
    case Cancelled = 'cancelled';

    /**
     * Get the human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Confirmado',
            self::Tentative => 'Provisório',
            self::Cancelled => 'Cancelado',
        };
    }

    /**
     * Get the badge classes used to display the status.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Confirmed => 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
            self::Tentative => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
            self::Cancelled => 'bg-gray-100 text-gray-500 line-through dark:bg-gray-800 dark:text-gray-400',
        };
    }
}
