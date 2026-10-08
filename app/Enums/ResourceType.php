<?php

namespace App\Enums;

enum ResourceType: string
{
    case Work = 'work';
    case Material = 'material';
    case Equipment = 'equipment';
    case Cost = 'cost';

    /**
     * Whether the resource consumes working time and can be overallocated.
     */
    public function isSchedulable(): bool
    {
        return $this === self::Work || $this === self::Equipment;
    }

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Work => 'Trabalho',
            self::Material => 'Material',
            self::Equipment => 'Equipamento',
            self::Cost => 'Custo',
        };
    }
}
