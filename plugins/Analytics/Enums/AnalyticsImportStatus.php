<?php

namespace Plugins\Analytics\Enums;

enum AnalyticsImportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Canceled = 'canceled';

    /**
     * Whether the import has already stopped running.
     */
    public function isFinished(): bool
    {
        return $this === self::Completed || $this === self::Failed || $this === self::Canceled;
    }

    /**
     * The human readable label shown in the dashboard.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Na fila',
            self::Processing => 'Importando…',
            self::Completed => 'Concluída',
            self::Failed => 'Falhou',
            self::Canceled => 'Cancelada',
        };
    }

    /**
     * The badge tone used by the interface.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Processing => 'indigo',
            self::Completed => 'green',
            self::Failed => 'red',
            self::Canceled => 'amber',
        };
    }
}
