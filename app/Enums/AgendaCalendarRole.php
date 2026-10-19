<?php

namespace App\Enums;

enum AgendaCalendarRole: string
{
    case Owner = 'owner';
    case Writer = 'writer';
    case Reader = 'reader';
    case FreeBusyReader = 'free_busy_reader';

    /**
     * Get the human readable label for the sharing role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Proprietário',
            self::Writer => 'Pode editar',
            self::Reader => 'Pode ver os detalhes',
            self::FreeBusyReader => 'Vê apenas disponibilidade',
        };
    }

    /**
     * Determine whether the role may create and edit events.
     */
    public function canWrite(): bool
    {
        return in_array($this, [self::Owner, self::Writer], true);
    }

    /**
     * Determine whether the role may read the event details.
     */
    public function canReadDetails(): bool
    {
        return in_array($this, [self::Owner, self::Writer, self::Reader], true);
    }

    /**
     * The roles that may be granted through a share.
     *
     * @return list<self>
     */
    public static function assignable(): array
    {
        return [self::Writer, self::Reader, self::FreeBusyReader];
    }
}
