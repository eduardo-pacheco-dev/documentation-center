<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ColaboradoresTemplateExport implements FromArray, WithHeadings
{
    /**
     * The heading row of the template.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nome',
            'Regime de Contrato',
            'Regional',
            'UF',
            'PIS',
            'Função',
            'CPF',
            'CNPJ',
            'RG',
            'Órgão Emissor',
            'Data de Nascimento',
            'Nome da Mãe',
            'Contato',
            'E-mail',
            'Status',
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
                'João da Silva',
                'CLT',
                'NO',
                'PA',
                '123.45678.90-1',
                'Técnico N2',
                '000.000.000-00',
                '00.000.000/0000-00',
                '3062601',
                'SSP/PA',
                '13/04/1978',
                'Maria Lúcia da Costa',
                '(11) 99999-9999',
                'joao@empresa.com.br',
                'ativo',
                'Atua na região central.',
            ],
        ];
    }
}
