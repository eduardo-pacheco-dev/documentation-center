<?php

namespace Plugins\FollowUp\Enums;

enum FollowUpPriority: string
{
    case Low = 'baixa';
    case Medium = 'media';
    case High = 'alta';
    case Urgent = 'urgente';

    /**
     * The human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Baixa',
            self::Medium => 'Média',
            self::High => 'Alta',
            self::Urgent => 'Urgente',
        };
    }

    /**
     * The badge tone used by the interface.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Low => 'gray',
            self::Medium => 'indigo',
            self::High => 'amber',
            self::Urgent => 'red',
        };
    }

    /**
     * The options for selects and validation.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $priority): array => [$priority->value => $priority->label()])
            ->all();
    }

    /**
     * The values used by validation rules.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
