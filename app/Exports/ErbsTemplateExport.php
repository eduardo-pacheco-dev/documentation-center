<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ErbsTemplateExport implements FromArray, WithHeadings
{
    /**
     * The heading row of the template.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Site ID',
            'End ID',
            'Operadora',
            'Tecnologia',
            'Status',
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

    /**
     * The example row shown in the template.
     *
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            [
                'SITE-00001',
                'PABLM_0001',
                'Vivo',
                '5G',
                'ativa',
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
            ],
        ];
    }
}
