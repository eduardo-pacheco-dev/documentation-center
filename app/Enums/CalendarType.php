<?php

namespace App\Enums;

enum CalendarType: string
{
    case Standard = 'standard';
    case Hours24 = '24_hours';
    case NightShift = 'night_shift';

    /**
     * The working windows of a day, in minutes from midnight.
     *
     * @return list<array{0: int, 1: int}>
     */
    public function segments(): array
    {
        return match ($this) {
            self::Standard => [[480, 720], [780, 1020]],
            self::Hours24 => [[0, 1440]],
            self::NightShift => [[1320, 1440], [0, 360]],
        };
    }

    /**
     * The weekday numbers (1 = Monday ... 7 = Sunday) treated as working days.
     *
     * @return list<int>
     */
    public function workingDays(): array
    {
        return match ($this) {
            self::Standard, self::NightShift => [1, 2, 3, 4, 5],
            self::Hours24 => [1, 2, 3, 4, 5, 6, 7],
        };
    }

    /**
     * The default window stored on the calendar record for display purposes.
     *
     * @return array{start: int, end: int, lunch_start: int|null, lunch_end: int|null}
     */
    public function window(): array
    {
        return match ($this) {
            self::Standard => ['start' => 480, 'end' => 1020, 'lunch_start' => 720, 'lunch_end' => 780],
            self::Hours24 => ['start' => 0, 'end' => 1440, 'lunch_start' => null, 'lunch_end' => null],
            self::NightShift => ['start' => 1320, 'end' => 360, 'lunch_start' => null, 'lunch_end' => null],
        };
    }

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Padrão (seg–sex)',
            self::Hours24 => '24 horas',
            self::NightShift => 'Turno noturno',
        };
    }
}
