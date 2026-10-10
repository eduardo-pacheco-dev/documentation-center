<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientsTemplateExport implements FromArray, WithHeadings
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
            'CPF/CNPJ',
            'E-mail',
            'Telefone',
            'Site',
            'Rua',
            'Número',
            'Complemento',
            'Bairro',
            'Cidade',
            'UF',
            'CEP',
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
                '000.000.000-00',
                'joao@empresa.com.br',
                '(11) 99999-9999',
                'https://empresa.com.br',
                'Av. Paulista',
                '1000',
                'Sala 12',
                'Bela Vista',
                'São Paulo',
                'SP',
                '01310-100',
                'ativo',
                'Cliente atendido pela equipe técnica.',
            ],
        ];
    }
}
