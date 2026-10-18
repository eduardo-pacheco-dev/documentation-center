<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkOrdersTemplateExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    /**
     * The heading row of the template.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Cliente', 'ERB', 'Radio link', 'Descrição', 'Prioridade', 'Status', 'Abertura', 'Previsão', 'Observações'];
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
                'SITE-00001',
                '',
                'Revisão completa do quadro de distribuição.',
                'alta',
                'aberta',
                '10/01/2026',
                '24/01/2026',
                'Acesso liberado pela portaria.',
            ],
        ];
    }

    /**
     * The register map applied to the template sheet.
     *
     * @return array<string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            'A1:I1' => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }

    /**
     * The column widths of the template sheet.
     *
     * @return array<string, int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 24,
            'B' => 16,
            'C' => 14,
            'D' => 40,
            'E' => 12,
            'F' => 14,
            'G' => 12,
            'H' => 12,
            'I' => 34,
        ];
    }

    /**
     * The events registered by the export.
     *
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                /** @var Worksheet $sheet */
                $sheet = $event->getSheet()->getDelegate();
                $instructions = $sheet->getParent()->createSheet()->setTitle('Instruções');

                $instructions->fromArray([
                    ['Coluna', 'Obrigatória', 'Formato e observações'],
                    ['Cliente', 'Sim', 'Nome exatamente como cadastrado.'],
                    ['ERB', 'Não', 'Site ID ou código da ERB (ex.: SITE-00001, ERB-0001). Preencha a ERB ou o Radio link, nunca ambos.'],
                    ['Radio link', 'Não', 'Código do radio link (ex.: RL-00001). Preencha a ERB ou o Radio link, nunca ambos.'],
                    ['Descrição', 'Não', 'Até 2000 caracteres.'],
                    ['Prioridade', 'Sim', 'baixa, normal, alta ou urgente.'],
                    ['Status', 'Não', 'aberta, em andamento, em espera, concluída ou cancelada. Padrão: aberta.'],
                    ['Abertura', 'Sim', 'dd/mm/aaaa.'],
                    ['Previsão', 'Não', 'dd/mm/aaaa. Deve ser igual ou posterior à abertura.'],
                    ['Observações', 'Não', 'Até 2000 caracteres.'],
                ]);

                $instructions->getStyle('A1:C1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F46E5'],
                    ],
                ]);

                $instructions->getStyle('A1:C11')->getAlignment()->setWrapText(true);
                $instructions->getStyle('A2:C11')->getAlignment()->setVertical('top');

                $instructions->getColumnDimension('A')->setWidth(16);
                $instructions->getColumnDimension('B')->setWidth(14);
                $instructions->getColumnDimension('C')->setWidth(80);
            },
        ];
    }
}
