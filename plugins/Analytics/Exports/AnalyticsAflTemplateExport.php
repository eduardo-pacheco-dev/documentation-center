<?php

namespace Plugins\Analytics\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AnalyticsAflTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Cód_AFL',
            'Status_Geral',
            'Site_ID A',
            'END_ID A',
            'Site_ID B',
            'END_ID B',
            'Projeto',
            'Descrição',
            'Supervisor',
            'Coordenador',
            'OC (TIM)',
            'Chave_MW',
            'SMP_Nokia',
            'OBS GERAL',
            'Data_Cadastro_Ativ',
            'Status_BOQ',
            'Data Solicit.',
            'Data Criação',
            'N° BOQ',
            'Obs BOQ',
            'status_acesso',
            'Data Solic. A',
            'Data Aprov. A',
            'Data Início A',
            'Data Fim A',
            'CRQ A',
            'CRQ Início A',
            'CRQ Fim A',
            'Data Solic. B',
            'Data Aprov. B',
            'Data Início B',
            'Data Fim B',
            'CRQ B',
            'CRQ Início B',
            'CRQ Fim B',
            'Obs Acesso',
            'Status_MOS',
            'Data Plan.',
            'Data Real',
            'Obs MOS',
            'Status_Atividade',
            'Responsável campo A_Atividade',
            'Data Plan Início A_Atividade',
            'Data Real início A_Atividade',
            'Data Plan final A_Atividade',
            'Data real final A_Atividade',
            'Responsável campo B_Atividade',
            'Data Plan Início B_Atividade',
            'Data Real início B_Atividade',
            'Data Plan final B_Atividade',
            'Data real final B_Atividade',
            'OBS Atividade_Atividade',
            'Status_Ativacao',
            'Responsável campo A_Ativacao',
            'Data Plan Início A_Ativacao',
            'Data Real início A_Ativacao',
            'Data Plan final A_Ativacao',
            'Data real final A_Ativacao',
            'Responsável campo B_Ativacao',
            'Data Plan Início B_Ativacao',
            'Data Real início B_Ativacao',
            'Data Plan final B_Ativacao',
            'Data real final B_Ativacao',
            'OBS Ativação_Ativacao',
            'Status_Documentacao',
            'Responsável doc A_Documentacao',
            'Data Plan Início A_Documentacao',
            'Data Real início A_Documentacao',
            'Data Plan final A_Documentacao',
            'Data real final A_Documentacao',
            'Data envio A_Documentacao',
            'Responsável doc B_Documentacao',
            'Data Plan Início B_Documentacao',
            'Data Real início B_Documentacao',
            'Data Plan final B_Documentacao',
            'Data real final B_Documentacao',
            'Data envio B_Documentacao',
            'Diretório_Documentacao',
            'OBS Documentação_Documentacao',
            'Status_Aceitacao',
            'Responsável campo A_Aceitacao',
            'Data Plan Início A_Aceitacao',
            'Data Real início A_Aceitacao',
            'Data Plan final A_Aceitacao',
            'Data real final A_Aceitacao',
            'Responsável campo B_Aceitacao',
            'Data Plan Início B_Aceitacao',
            'Data Real início B_Aceitacao',
            'Data Plan final B_Aceitacao',
            'Data real final B_Aceitacao',
            'OBS Aceitação_Aceitacao',
            'Status_Faturamento',
            'Valor_Faturado',
            'Valor_Contratado',
            'Valor_Fatura',
            'Nun_NF',
            'Data_Emissão',
            'Data_Faturamento',
            'PO SERVIÇO',
            'GR/IA',
            'DATA GR/IA',
            'Faturado?',
            'Faturamento_Antecipado',
            '% Faturado',
            'OBS_Faturamento',
            'OS_Cancelada',
            'Ultima_Alteracao',
            'Ultimo_Usuario',
        ];
    }
}
