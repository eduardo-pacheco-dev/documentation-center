<?php

namespace Plugins\FollowUp\Enums;

enum FollowUpStatus: string
{
    case Pending = 'pendente';
    case InProgress = 'em_andamento';
    case Completed = 'concluido';
    case Cancelled = 'cancelado';

    /**
     * The human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::InProgress => 'Em andamento',
            self::Completed => 'Concluído',
            self::Cancelled => 'Cancelado',
        };
    }

    /**
     * The badge tone used by the interface.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::InProgress => 'indigo',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }

    /**
     * Whether the follow-up is finished.
     */
    public function isClosed(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    /**
     * The options for selects and validation.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
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
