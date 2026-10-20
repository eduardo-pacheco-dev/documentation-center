<?php

namespace App\Exports;

use App\Enums\WorkOrderItemStatus;
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

    /**
     * The catalog item names available to the user.
     *
     * @var list<string>
     */
    private readonly array $catalogItems;

    public function __construct(
        private readonly User $user,
        private readonly bool $withItems = false,
    ) {
        $this->clients = $user->clients()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $this->catalogItems = $user->catalogItems()
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
        $headings = ['Cliente', 'ERB', 'Radio link', 'Descrição', 'Prioridade', 'Status', 'Abertura', 'Previsão', 'Observações', 'OC', 'CHAVE'];

        if ($this->withItems) {
            $headings = [
                ...$headings,
                'Item',
                'Quantidade',
                'Status do item',
                'OS cliente 1',
                'OS cliente 2',
            ];
        }

        return $headings;
    }

    /**
     * The example row shown in the template.
     *
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        $row = [
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
        ];

        if ($this->withItems) {
            $row = [
                ...$row,
                'Troca de módulo',
                1,
                'pendente',
                'OS-CLI-1',
                'OS-CLI-2',
            ];
        }

        return [$row];
    }

    /**
     * The register map applied to the template sheet.
     *
     * @return array<string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            'A1:'.($this->withItems ? 'P' : 'K').'1' => [
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
        $widths = [
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

        if ($this->withItems) {
            $widths = [
                ...$widths,
                'L' => 32,
                'M' => 12,
                'N' => 16,
                'O' => 16,
                'P' => 16,
            ];
        }

        return $widths;
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

                $instructionRows = [
                    ['Coluna', 'Obrigatória', 'Formato e observações'],
                    ['Cliente', 'Sim', 'Nome do cliente. Se não existir, será criado automaticamente.'],
                    ['OC', $this->withItems ? 'Sim*' : 'Não', $this->withItems
                        ? 'Número da OC (ordem complexa). Somente números. Deve ser único. Informe OC ou CHAVE para agrupar as linhas da mesma OS.'
                        : 'Número da OC (ordem complexa). Somente números. Deve ser único.'],
                    ['CHAVE', $this->withItems ? 'Sim*' : 'Não', $this->withItems
                        ? 'Chave da OS. Deve ser única. Informe OC ou CHAVE para agrupar as linhas da mesma OS.'
                        : 'Chave da OS. Deve ser única.'],
                    ['ERB', 'Não', 'Site ID ou código da ERB. Se não existir, será criada automaticamente. Preencha a ERB ou o Radio link, nunca ambos.'],
                    ['Radio link', 'Não', 'Código existente ou as pontas no formato "A dir B" / "A - B". Se o enlace não existir, será criado. Preencha a ERB ou o Radio link, nunca ambos.'],
                    ['Descrição', 'Não', 'Até 2000 caracteres.'],
                    ['Prioridade', 'Sim', 'baixa, normal, alta ou urgente.'],
                    ['Status', 'Não', 'aberta, em andamento, em espera, concluída ou cancelada. Padrão: aberta.'],
                    ['Abertura', 'Sim', 'dd/mm/aaaa.'],
                    ['Previsão', 'Não', 'dd/mm/aaaa. Deve ser igual ou posterior à abertura.'],
                    ['Observações', 'Não', 'Até 2000 caracteres.'],
                ];

                if ($this->withItems) {
                    $instructionRows = [
                        ...$instructionRows,
                        ['Item', 'Sim', 'Selecione um item do catálogo ou digite uma descrição. Cada linha representa um item. Repita a OC ou a CHAVE nas linhas da mesma OS.'],
                        ['Quantidade', 'Sim', 'Maior que zero.'],
                        ['Status do item', 'Não', 'pendente, em andamento, concluído, rejeitado ou cancelado. Padrão: pendente.'],
                        ['OS cliente 1', 'Não', 'Primeira OS do cliente vinculada ao item.'],
                        ['OS cliente 2', 'Não', 'Segunda OS do cliente vinculada ao item.'],
                    ];
                }

                $instructions->fromArray($instructionRows);

                $lastInstructionRow = count($instructionRows);

                $instructions->getStyle('A1:C1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F46E5'],
                    ],
                ]);

                $instructions->getStyle("A1:C{$lastInstructionRow}")->getAlignment()->setWrapText(true);
                $instructions->getStyle("A2:C{$lastInstructionRow}")->getAlignment()->setVertical('top');

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
                        'Cliente não cadastrado',
                        'Selecione um cliente da lista ou digite um novo nome para criá-lo na importação.',
                        DataValidation::STYLE_WARNING,
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

                if ($this->withItems) {
                    $itemStatuses = array_map(
                        fn (WorkOrderItemStatus $status): string => mb_strtolower($status->label()),
                        WorkOrderItemStatus::cases(),
                    );

                    $lists->setCellValue('D1', 'Status dos itens');
                    $lists->fromArray(
                        array_map(fn (string $status): array => [$status], $itemStatuses),
                        null,
                        'D2',
                    );

                    $lists->setCellValue('E1', 'Itens do catálogo');

                    if ($this->catalogItems !== []) {
                        $lists->fromArray(
                            array_map(fn (string $item): array => [$item], $this->catalogItems),
                            null,
                            'E2',
                        );

                        $this->applyListValidation(
                            $sheet,
                            'L2:L1000',
                            'Listas!$E$2:$E$'.(count($this->catalogItems) + 1),
                            'Item não cadastrado',
                            'Selecione um item do catálogo ou digite uma descrição.',
                            DataValidation::STYLE_WARNING,
                        );
                    }

                    $this->applyListValidation(
                        $sheet,
                        'N2:N1000',
                        'Listas!$D$2:$D$'.(count($itemStatuses) + 1),
                        'Status do item inválido',
                        'Selecione um status da lista.',
                    );
                }

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
        string $errorStyle = DataValidation::STYLE_STOP,
    ): void {
        $validation = new DataValidation;
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle($errorStyle);
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
