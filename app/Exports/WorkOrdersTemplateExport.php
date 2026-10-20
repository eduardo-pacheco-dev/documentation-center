<?php

namespace App\Exports;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkOrdersTemplateExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    /**
     * The client names available to the user.
     *
     * @var list<string>
     */
    private readonly array $clients;

    public function __construct(private readonly User $user)
    {
        $this->clients = $user->clients()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    /**
     * The heading row of the template.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Cliente', 'ERB', 'Radio link', 'Descrição', 'Prioridade', 'Status', 'Abertura', 'Previsão', 'Observações', 'OC', 'CHAVE'];
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
                $this->clients[0] ?? 'Construtora Exemplo',
                'SITE-00001',
                '',
                'Revisão completa do quadro de distribuição.',
                'alta',
                'aberta',
                '10/01/2026',
                '24/01/2026',
                'Acesso liberado pela portaria.',
                '12345',
                'CHAVE-00001',
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
            'A1:K1' => [
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
            'J' => 16,
            'K' => 16,
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
                    ['OC', 'Não', 'Número da OC (ordem complexa). Somente números. Deve ser único.'],
                    ['CHAVE', 'Não', 'Chave da OS. Deve ser única.'],
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

                $instructions->getStyle('A1:C12')->getAlignment()->setWrapText(true);
                $instructions->getStyle('A2:C12')->getAlignment()->setVertical('top');

                $instructions->getColumnDimension('A')->setWidth(16);
                $instructions->getColumnDimension('B')->setWidth(14);
                $instructions->getColumnDimension('C')->setWidth(80);

                $lists = $sheet->getParent()->createSheet()->setTitle('Listas');
                $lists->setCellValue('A1', 'Clientes');
                $lists->setCellValue('B1', 'Prioridades');
                $lists->setCellValue('C1', 'Status');

                $priorities = array_map(
                    fn (WorkOrderPriority $priority): string => mb_strtolower($priority->label()),
                    WorkOrderPriority::cases(),
                );

                $statuses = array_map(
                    fn (WorkOrderStatus $status): string => mb_strtolower($status->label()),
                    WorkOrderStatus::cases(),
                );

                if ($this->clients !== []) {
                    $lists->fromArray(
                        array_map(fn (string $client): array => [$client], $this->clients),
                        null,
                        'A2',
                    );

                    $this->applyListValidation(
                        $sheet,
                        'A2:A1000',
                        'Listas!$A$2:$A$'.(count($this->clients) + 1),
                        'Cliente inválido',
                        'Selecione um cliente da lista.',
                    );
                }

                $lists->fromArray(
                    array_map(fn (string $priority): array => [$priority], $priorities),
                    null,
                    'B2',
                );

                $lists->fromArray(
                    array_map(fn (string $status): array => [$status], $statuses),
                    null,
                    'C2',
                );

                $lists->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                $this->applyListValidation(
                    $sheet,
                    'E2:E1000',
                    'Listas!$B$2:$B$'.(count($priorities) + 1),
                    'Prioridade inválida',
                    'Selecione baixa, normal, alta ou urgente.',
                );

                $this->applyListValidation(
                    $sheet,
                    'F2:F1000',
                    'Listas!$C$2:$C$'.(count($statuses) + 1),
                    'Status inválido',
                    'Selecione um status da lista.',
                );
            },
        ];
    }

    /**
     * Apply a dropdown list validation to the given range.
     */
    private function applyListValidation(
        Worksheet $sheet,
        string $range,
        string $source,
        string $title,
        string $message,
    ): void {
        $validation = new DataValidation;
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle($title);
        $validation->setPrompt($message);
        $validation->setErrorTitle($title);
        $validation->setError($message);
        $validation->setFormula1($source);

        $sheet->setDataValidation($range, $validation);
    }
}
