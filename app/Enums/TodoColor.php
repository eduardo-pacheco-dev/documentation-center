<?php

namespace App\Enums;

enum TodoColor: string
{
    case Indigo = 'indigo';
    case Blue = 'blue';
    case Sky = 'sky';
    case Teal = 'teal';
    case Green = 'green';
    case Lime = 'lime';
    case Amber = 'amber';
    case Orange = 'orange';
    case Rose = 'rose';
    case Pink = 'pink';
    case Violet = 'violet';
    case Slate = 'slate';

    /**
     * Get the human readable label for the color.
     */
    public function label(): string
    {
        return match ($this) {
            self::Indigo => 'Índigo',
            self::Blue => 'Azul',
            self::Sky => 'Céu',
            self::Teal => 'Verde-água',
            self::Green => 'Verde',
            self::Lime => 'Lima',
            self::Amber => 'Âmbar',
            self::Orange => 'Laranja',
            self::Rose => 'Rosé',
            self::Pink => 'Rosa',
            self::Violet => 'Violeta',
            self::Slate => 'Cinza',
        };
    }

    /**
     * Get the solid background class used for the color dot.
     */
    public function dotClasses(): string
    {
        return match ($this) {
            self::Indigo => 'bg-indigo-500',
            self::Blue => 'bg-blue-500',
            self::Sky => 'bg-sky-500',
            self::Teal => 'bg-teal-500',
            self::Green => 'bg-green-500',
            self::Lime => 'bg-lime-500',
            self::Amber => 'bg-amber-500',
            self::Orange => 'bg-orange-500',
            self::Rose => 'bg-rose-500',
            self::Pink => 'bg-pink-500',
            self::Violet => 'bg-violet-500',
            self::Slate => 'bg-slate-500',
        };
    }

    /**
     * Get the tinted classes used for badges and icon backgrounds.
     */
    public function softClasses(): string
    {
        return match ($this) {
            self::Indigo => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
            self::Blue => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            self::Sky => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
            self::Teal => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400',
            self::Green => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
            self::Lime => 'bg-lime-50 text-lime-600 dark:bg-lime-500/10 dark:text-lime-400',
            self::Amber => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            self::Orange => 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400',
            self::Rose => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
            self::Pink => 'bg-pink-50 text-pink-600 dark:bg-pink-500/10 dark:text-pink-400',
            self::Violet => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
            self::Slate => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300',
        };
    }

    /**
     * Get the text class used for the color.
     */
    public function textClasses(): string
    {
        return match ($this) {
            self::Indigo => 'text-indigo-600 dark:text-indigo-400',
            self::Blue => 'text-blue-600 dark:text-blue-400',
            self::Sky => 'text-sky-600 dark:text-sky-400',
            self::Teal => 'text-teal-600 dark:text-teal-400',
            self::Green => 'text-green-600 dark:text-green-400',
            self::Lime => 'text-lime-600 dark:text-lime-400',
            self::Amber => 'text-amber-600 dark:text-amber-400',
            self::Orange => 'text-orange-600 dark:text-orange-400',
            self::Rose => 'text-rose-600 dark:text-rose-400',
            self::Pink => 'text-pink-600 dark:text-pink-400',
            self::Violet => 'text-violet-600 dark:text-violet-400',
            self::Slate => 'text-slate-600 dark:text-slate-300',
        };
    }

    /**
     * Get the ring class used to highlight the selected color swatch.
     */
    public function ringClasses(): string
    {
        return match ($this) {
            self::Indigo => 'ring-indigo-500',
            self::Blue => 'ring-blue-500',
            self::Sky => 'ring-sky-500',
            self::Teal => 'ring-teal-500',
            self::Green => 'ring-green-500',
            self::Lime => 'ring-lime-500',
            self::Amber => 'ring-amber-500',
            self::Orange => 'ring-orange-500',
            self::Rose => 'ring-rose-500',
            self::Pink => 'ring-pink-500',
            self::Violet => 'ring-violet-500',
            self::Slate => 'ring-slate-500',
        };
    }
}
