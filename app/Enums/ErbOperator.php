<?php

namespace App\Enums;

enum ErbOperator: string
{
    case Vivo = 'Vivo';
    case Claro = 'Claro';
    case Tim = 'TIM';
    case Oi = 'Oi';
    case Algar = 'Algar';
    case Sercomtel = 'Sercomtel';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return $this->value;
    }
}
