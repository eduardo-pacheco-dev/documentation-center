<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Completed => 'Concluído',
            self::Archived => 'Arquivado',
        };
    }
}
