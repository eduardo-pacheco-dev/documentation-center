<?php

namespace Plugins\Nokia\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class NokiaPendingExport
{
    public function __construct(private readonly NokiaDashboardMetrics $metrics) {}

    /**
     * Build a workbook with one row per pending activity or task.
     */
    public function spreadsheet(): Spreadsheet
    {
        $pending = $this->metrics->build()['pending'];

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Pendências Nokia')
            ->setDescription('Atividades e tarefas pendentes dos projetos Nokia.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pendências');
        $sheet->getTabColor()->setRGB('4F46E5');

        $columns = $this->columns();
        $columnIndex = 1;

        foreach ($columns as $column) {
            $letter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($letter.'1', $column['label']);
            $sheet->getColumnDimension($letter)->setWidth($column['width']);
            $columnIndex++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($columns));

        $row = 2;
        foreach ($pending as $item) {
            foreach ($columns as $index => $column) {
                $value = $column['value']($item);
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

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * The download filename for the workbook.
     */
    public function fileName(): string
    {
        return 'pendencias-nokia-'.now()->format('Y-m-d').'.xlsx';
    }

    /**
     * All exported columns in display order.
     *
     * @return list<array{label: string, width: int, value: callable(array<string, mixed>): mixed}>
     */
    private function columns(): array
    {
        return [
            ['label' => 'Site ID', 'width' => 18, 'value' => fn (array $item): ?string => $item['site_id']],
            ['label' => 'OC', 'width' => 16, 'value' => fn (array $item): ?string => $item['oc']],
            ['label' => 'Regional', 'width' => 16, 'value' => fn (array $item): ?string => $item['regional']],
            ['label' => 'Subcon', 'width' => 16, 'value' => fn (array $item): ?string => $item['subcon']],
            ['label' => 'ROM', 'width' => 24, 'value' => fn (array $item): ?string => $item['rom']],
            ['label' => 'Status do site', 'width' => 18, 'value' => fn (array $item): ?string => $item['status_label']],
            ['label' => 'Tipo', 'width' => 14, 'value' => fn (array $item): ?string => $item['kind_label']],
            ['label' => 'Pendência', 'width' => 30, 'value' => fn (array $item): ?string => $item['description']],
            ['label' => 'Situação', 'width' => 18, 'value' => fn (array $item): ?string => $item['detail']],
            ['label' => 'Aging (dias)', 'width' => 14, 'value' => fn (array $item): ?string => (string) $item['aging']],
        ];
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
