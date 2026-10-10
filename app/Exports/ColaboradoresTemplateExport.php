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
        return ['Nome', 'Cargo', 'CPF', 'Telefone', 'E-mail', 'Status', 'Observações'];
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
                'Técnico N2',
                '000.000.000-00',
                '(11) 99999-9999',
                'joao@empresa.com.br',
                'ativo',
                'Atua na região central.',
            ],
        ];
    }
}
