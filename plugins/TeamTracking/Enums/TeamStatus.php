<?php

namespace Plugins\TeamTracking\Enums;

enum TeamStatus: string
{
    case Active = 'ativa';
    case Paused = 'pausada';
    case Closed = 'encerrada';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Paused => 'Pausada',
            self::Closed => 'Encerrada',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Paused => 'amber',
            self::Closed => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
