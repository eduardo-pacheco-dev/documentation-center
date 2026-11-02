<?php

namespace Plugins\Analytics\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AnalyticsRecordsTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['364007', '417', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Obra Civil, Infra Cabeamento, Quadro de Energia e Energização', 'ENODEB', 'R. João Pessoa, 116-190, Aimorés - MG, 35200-000, Brazil', 'Aimorés', 'MG', '7NLAMGV28', 'MGAOR_0001', 'Max da Silva Costa', 'Aceito com Ressalvas'],
            ['362293', '8171', '26/10/2017', 'NOKIA', '', 'Acesso FAM', 'Instalação', 'ENODEB', 'R. Pres. Costa e Silva, 2-50, Nova Viçosa - BA, 45920-000, Brazil', 'Nova Viçosa', 'BA', '7NLNVIT01', 'BANVV_0001', '', 'Criado pelo NetFlow'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'OC',
            'OS',
            'Data Criação',
            'Fornecedor',
            'Fornecedor Fiscalização',
            'Tipo Projeto',
            'Etapa Processo',
            'Tipo Elemento',
            'Endereço de Atendimento',
            'Cidade',
            'Estado',
            'Node Name',
            'Localização',
            'Operador',
            'Status',
        ];
    }
}
