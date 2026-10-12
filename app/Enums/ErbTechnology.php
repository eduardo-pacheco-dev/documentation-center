<?php

namespace App\Enums;

enum ErbTechnology: string
{
    case TwoG = '2G';
    case ThreeG = '3G';
    case FourG = '4G';
    case FiveG = '5G';
    case FourGFiveG = '4G/5G';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return $this->value;
    }
}
