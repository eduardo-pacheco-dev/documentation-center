<?php

namespace App\Enums;

enum ProjectRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Viewer = 'viewer';

    /**
     * Whether the role allows changing the project plan.
     */
    public function canEdit(): bool
    {
        return $this !== self::Viewer;
    }

    /**
     * Whether the role allows managing members and deleting the project.
     */
    public function canManage(): bool
    {
        return $this === self::Owner;
    }

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Proprietário',
            self::Editor => 'Editor',
            self::Viewer => 'Leitor',
        };
    }
}
