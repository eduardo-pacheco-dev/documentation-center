<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WorkOrdersTemplateExport implements FromArray, WithHeadings
{
    /**
     * The heading row of the template.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Cliente', 'Título', 'Descrição', 'Prioridade', 'Status', 'Abertura', 'Previsão', 'Observações'];
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
                'Construtora Exemplo',
                'Manutenção elétrica do galpão',
                'Revisão completa do quadro de distribuição.',
                'alta',
                'aberta',
                '10/01/2026',
                '24/01/2026',
                'Acesso liberado pela portaria.',
            ],
        ];
    }
}
