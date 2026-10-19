<?php

namespace App\Enums;

/**
 * The event and calendar color palette, mirroring the Google Calendar hues.
 */
enum AgendaColor: string
{
    case Tomato = 'tomato';
    case Flamingo = 'flamingo';
    case Tangerine = 'tangerine';
    case Banana = 'banana';
    case Sage = 'sage';
    case Basil = 'basil';
    case Peacock = 'peacock';
    case Blueberry = 'blueberry';
    case Lavender = 'lavender';
    case Grape = 'grape';
    case Graphite = 'graphite';

    /**
     * Get the human readable label for the color.
     */
    public function label(): string
    {
        return match ($this) {
            self::Tomato => 'Tomate',
            self::Flamingo => 'Flamingo',
            self::Tangerine => 'Tangerina',
            self::Banana => 'Banana',
            self::Sage => 'Sálvia',
            self::Basil => 'Manjericão',
            self::Peacock => 'Pavão',
            self::Blueberry => 'Mirtilo',
            self::Lavender => 'Lavanda',
            self::Grape => 'Uva',
            self::Graphite => 'Grafite',
        };
    }

    /**
     * Get the solid hex color used by the calendar UI.
     */
    public function hex(): string
    {
        return match ($this) {
            self::Tomato => '#d50000',
            self::Flamingo => '#e67c73',
            self::Tangerine => '#f4511e',
            self::Banana => '#f6bf26',
            self::Sage => '#33b679',
            self::Basil => '#0b8043',
            self::Peacock => '#039be5',
            self::Blueberry => '#3f51b5',
            self::Lavender => '#7986cb',
            self::Grape => '#8e24aa',
            self::Graphite => '#616161',
        };
    }
}
