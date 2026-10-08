<?php

namespace App\Enums;

enum DependencyType: string
{
    case FinishToStart = 'fs';
    case StartToStart = 'ss';
    case FinishToFinish = 'ff';
    case StartToFinish = 'sf';

    /**
     * The dhtmlx-gantt numeric link type used by the chart.
     */
    public function ganttType(): int
    {
        return match ($this) {
            self::FinishToStart => 0,
            self::StartToStart => 1,
            self::FinishToFinish => 2,
            self::StartToFinish => 3,
        };
    }

    /**
     * Resolve a dependency type from the gantt chart numeric value.
     */
    public static function fromGanttType(int $type): self
    {
        return match ($type) {
            1 => self::StartToStart,
            2 => self::FinishToFinish,
            3 => self::StartToFinish,
            default => self::FinishToStart,
        };
    }

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::FinishToStart => 'Término → Início',
            self::StartToStart => 'Início → Início',
            self::FinishToFinish => 'Término → Término',
            self::StartToFinish => 'Início → Término',
        };
    }
}
