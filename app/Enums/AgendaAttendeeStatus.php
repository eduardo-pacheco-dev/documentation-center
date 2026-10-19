<?php

namespace App\Enums;

enum AgendaAttendeeStatus: string
{
    case NeedsAction = 'needs_action';
    case Declined = 'declined';
    case Tentative = 'tentative';
    case Accepted = 'accepted';

    /**
     * Get the human readable label for the response status.
     */
    public function label(): string
    {
        return match ($this) {
            self::NeedsAction => 'Sem resposta',
            self::Declined => 'Não vou',
            self::Tentative => 'Talvez',
            self::Accepted => 'Vou',
        };
    }

    /**
     * Get the badge classes used to display the response status.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::NeedsAction => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
            self::Declined => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
            self::Tentative => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            self::Accepted => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
        };
    }
}
