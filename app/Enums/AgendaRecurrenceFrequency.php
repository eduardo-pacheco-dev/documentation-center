<?php

namespace App\Enums;

enum AgendaRecurrenceFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    /**
     * Get the human readable label for the frequency.
     */
    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Diariamente',
            self::Weekly => 'Semanalmente',
            self::Monthly => 'Mensalmente',
            self::Yearly => 'Anualmente',
        };
    }

    /**
     * Get the RFC 5545 (RRULE) name of the frequency.
     */
    public function rruleName(): string
    {
        return match ($this) {
            self::Daily => 'DAILY',
            self::Weekly => 'WEEKLY',
            self::Monthly => 'MONTHLY',
            self::Yearly => 'YEARLY',
        };
    }
}
