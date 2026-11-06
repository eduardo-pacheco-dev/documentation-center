<?php

namespace Plugins\Nokia\Support;

use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaTask;

final class NokiaSiteExport
{
    /**
     * The milestone dates exported in display order.
     *
     * @var array<string, string>
     */
    private const DATE_COLUMNS = [
        'MOS Plan' => 'mos_plan_at',
        'MOS Real' => 'mos_real_at',
        'Instalação Plan' => 'instalacao_plan_at',
        'Instalação Real' => 'instalacao_real_at',
        'Integração Plan' => 'integracao_plan_at',
        'Integração Real' => 'integracao_real_at',
        'RFA Plan' => 'rfa_plan_at',
        'RFA Real' => 'rfa_real_at',
        'FAM Entrega Aprovado' => 'fam_entrega_approved_at',
        'FAM Instalação Aprovado' => 'fam_instalacao_approved_at',
        'FAM Panorâmica Aprovado' => 'fam_panoramica_approved_at',
    ];

    /**
     * Build a workbook with every registered site and all its data.
     */
    public function spreadsheet(?string $search = null): Spreadsheet
    {
        $query = NokiaSite::query()
            ->with(['rom', 'tasks'])
            ->orderBy('site_id');

        $search = trim((string) $search);

        if ($search !== '') {
            $query->matching($search);
        }

        $sites = $query->get();

        $tasks = NokiaTask::query()
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Projetos Nokia')
            ->setDescription('Exportação dos projetos Nokia cadastrados.');

        $this->sitesSheet($spreadsheet, $sites, $tasks);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * The download filename for the workbook.
     */
    public function fileName(): string
    {
        return 'sites-nokia-'.now()->format('Y-m-d').'.xlsx';
    }

    /**
     * @param  Collection<int, NokiaSite>  $sites
     * @param  Collection<int, NokiaTask>  $tasks
     */
    private function sitesSheet(Spreadsheet $spreadsheet, Collection $sites, Collection $tasks): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sites');
        $sheet->getTabColor()->setRGB('4F46E5');

        $columns = $this->columns($tasks);
        $columnIndex = 1;

        foreach ($columns as $column) {
            $letter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($letter.'1', $column['label']);
            $sheet->getColumnDimension($letter)->setWidth($column['width']);
            $columnIndex++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($columns));

        $row = 2;
        foreach ($sites as $site) {
            foreach ($columns as $index => $column) {
                $value = $column['value']($site);
                $letter = Coordinate::stringFromColumnIndex($index + 1);

                $sheet->setCellValueExplicit(
                    $letter.$row,
                    filled($value) ? (string) $value : '',
                    DataType::TYPE_STRING,
                );
            }

            $row++;
        }

        $headerRange = 'A1:'.$lastColumn.'1';
        $this->styleTableHeader($sheet, $headerRange);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);

        if ($row > 2) {
            $bodyRange = 'A2:'.$lastColumn.($row - 1);
            $sheet->getStyle($bodyRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle($bodyRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
        }
    }

    /**
     * All exported columns in display order.
     *
     * @param  Collection<int, NokiaTask>  $tasks
     * @return list<array{label: string, width: int, value: callable(NokiaSite): mixed}>
     */
    private function columns(Collection $tasks): array
    {
        $checklists = config('nokia.checklists', []);

        $columns = [
            ['label' => 'Site ID', 'width' => 18, 'value' => fn (NokiaSite $site): ?string => $site->site_id],
            ['label' => 'End ID', 'width' => 18, 'value' => fn (NokiaSite $site): ?string => $site->end_id],
            ['label' => 'OC', 'width' => 16, 'value' => fn (NokiaSite $site): ?string => $site->oc],
            ['label' => 'ROM', 'width' => 24, 'value' => fn (NokiaSite $site): ?string => $site->rom?->fullName()],
            ['label' => 'OS FAM Entrega', 'width' => 20, 'value' => fn (NokiaSite $site): ?string => $site->os_fam_entrega],
            ['label' => 'OS FAM Instalação', 'width' => 20, 'value' => fn (NokiaSite $site): ?string => $site->os_fam_instalacao],
        ];

        foreach ($checklists as $field => $label) {
            $columns[] = [
                'label' => 'Checklist - '.$label,
                'width' => 22,
                'value' => fn (NokiaSite $site): string => $site->checklistStatusLabel($field),
            ];
        }

        foreach ($tasks as $task) {
            $columns[] = [
                'label' => 'Tarefa - '.$task->name,
                'width' => 22,
                'value' => function (NokiaSite $site) use ($task): string {
                    $assigned = $site->tasks->firstWhere('id', $task->getKey());

                    return $assigned?->pivot?->completed_at !== null ? 'Concluída' : 'Pendente';
                },
            ];
        }

        $columns = [
            ...$columns,
            [
                'label' => 'Interrupção - Motivo',
                'width' => 28,
                'value' => fn (NokiaSite $site): ?string => $site->isInterrupted() ? $site->interruption_reason : null,
            ],
            [
                'label' => 'Interrupção - Início',
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->isInterrupted() ? $site->interruption_started_at?->format('d/m/Y H:i') : null,
            ],
            [
                'label' => 'Interrupção - Prazo (dias)',
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->isInterrupted() ? (string) $site->interruption_return_days : null,
            ],
            [
                'label' => 'Interrupção - Retorno previsto',
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->interruptionReturnAt()?->format('d/m/Y'),
            ],
            [
                'label' => 'Rejeição - Motivo',
                'width' => 28,
                'value' => fn (NokiaSite $site): ?string => $site->isRejected() ? $site->rejection_reason : null,
            ],
            [
                'label' => 'Rejeição - Início',
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->isRejected() ? $site->rejection_started_at?->format('d/m/Y H:i') : null,
            ],
            [
                'label' => 'Rejeição - Prazo (dias)',
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->isRejected() ? (string) $site->rejection_return_days : null,
            ],
            [
                'label' => 'Rejeição - Retorno previsto',
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->rejectionReturnAt()?->format('d/m/Y'),
            ],
            [
                'label' => 'Rejeição - Retorno a campo',
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->isRejected()
                    ? ($site->rejection_requires_field_return ? 'Sim' : 'Não')
                    : null,
            ],
        ];

        foreach (self::DATE_COLUMNS as $label => $field) {
            $columns[] = [
                'label' => $label,
                'width' => 20,
                'value' => fn (NokiaSite $site): ?string => $site->{$field}?->format('d/m/Y'),
            ];
        }

        $columns[] = [
            'label' => 'Criado em',
            'width' => 20,
            'value' => fn (NokiaSite $site): ?string => $site->created_at?->format('d/m/Y H:i'),
        ];

        $columns[] = [
            'label' => 'Atualizado em',
            'width' => 20,
            'value' => fn (NokiaSite $site): ?string => $site->updated_at?->format('d/m/Y H:i'),
        ];

        return $columns;
    }

    private function styleTableHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }
}
