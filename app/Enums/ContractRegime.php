<?php

namespace App\Enums;

enum ContractRegime: string
{
    case Clt = 'CLT';
    case Pj = 'PJ';
    case Estagio = 'Estágio';
    case Aprendiz = 'Aprendiz';
    case Temporario = 'Temporário';

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return $this->value;
    }
}
