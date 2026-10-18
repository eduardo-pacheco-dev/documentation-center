<?php

namespace App\Enums;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

enum TodoRepeat: string
{
    case None = 'none';
    case Daily = 'daily';
    case Weekdays = 'weekdays';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    /**
     * Get the human readable label for the repeat rule.
     */
    public function label(): string
    {
        return match ($this) {
            self::None => 'Não se repete',
            self::Daily => 'Todos os dias',
            self::Weekdays => 'Dias úteis',
            self::Weekly => 'Todas as semanas',
            self::Monthly => 'Todos os meses',
            self::Yearly => 'Todos os anos',
        };
    }

    /**
     * Advance the given date to the next occurrence of the repeat rule.
     */
    public function next(CarbonInterface $date): CarbonInterface
    {
        $next = Carbon::parse($date->toDateString());

        return match ($this) {
            self::None => $next,
            self::Daily => $next->addDay(),
            self::Weekdays => $this->nextWeekday($next),
            self::Weekly => $next->addWeek(),
            self::Monthly => $next->addMonthNoOverflow(),
            self::Yearly => $next->addYearNoOverflow(),
        };
    }

    /**
     * Advance the date to the next day that is not a weekend day.
     */
    private function nextWeekday(Carbon $date): Carbon
    {
        do {
            $date->addDay();
        } while ($date->isWeekend());

        return $date;
    }
}
