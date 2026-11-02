<?php

namespace Plugins\Stations\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StationsTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [[
            'SITE-00001',
            'PABLM_0001',
            'Vivo',
            '5G',
            'SMP-1024',
            'ativa',
            '10/01/2026',
            '31/12/2026',
            'Av. Paulista',
            '1000',
            'Conjunto 12',
            'Bela Vista',
            'São Paulo',
            'SP',
            '01310-100',
            '-23.56143',
            '-46.65590',
            'Acesso pelo estacionamento.',
        ]];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Site ID',
            'End ID',
            'Operadora',
            'Tecnologia',
            'Nokia SMP ID',
            'Status',
            'Início',
            'Término',
            'Logradouro',
            'Número',
            'Complemento',
            'Bairro',
            'Cidade',
            'Estado',
            'CEP',
            'Latitude',
            'Longitude',
            'Observações',
        ];
    }
}
