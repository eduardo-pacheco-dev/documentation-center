<?php

namespace Plugins\TeamTracking\Enums;

enum CheckInStatus: string
{
    case Planned = 'planejado';
    case OnTrack = 'em_dia';
    case Attention = 'atencao';
    case Completed = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planejado',
            self::OnTrack => 'Em dia',
            self::Attention => 'Atenção',
            self::Completed => 'Concluído',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Planned => 'indigo',
            self::OnTrack => 'green',
            self::Attention => 'red',
            self::Completed => 'gray',
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
