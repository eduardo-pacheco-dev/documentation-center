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

final class NokiaSiteExport
{
    /**
     * Build a workbook with every registered site and its checklist.
     */
    public function spreadsheet(?string $search = null): Spreadsheet
    {
        $query = NokiaSite::query()->orderBy('site_id');

        $search = trim((string) $search);

        if ($search !== '') {
            $query->matching($search);
        }

        $sites = $query->get();

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Projetos Nokia')
            ->setDescription('Exportação dos sites Nokia cadastrados.');

        $this->sitesSheet($spreadsheet, $sites);

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
     */
    private function sitesSheet(Spreadsheet $spreadsheet, Collection $sites): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sites');
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
     * @return list<array{label: string, width: int, value: callable(NokiaSite): mixed}>
     */
    private function columns(): array
    {
        $checklists = config('nokia.checklists', []);

        $columns = [
            ['label' => 'Site ID', 'width' => 18, 'value' => fn (NokiaSite $site): ?string => $site->site_id],
            ['label' => 'End ID', 'width' => 18, 'value' => fn (NokiaSite $site): ?string => $site->end_id],
            ['label' => 'OC', 'width' => 16, 'value' => fn (NokiaSite $site): ?string => $site->oc],
            ['label' => 'OS FAM Entrega', 'width' => 20, 'value' => fn (NokiaSite $site): ?string => $site->os_fam_entrega],
            ['label' => 'OS FAM Instalação', 'width' => 20, 'value' => fn (NokiaSite $site): ?string => $site->os_fam_instalacao],
        ];

        foreach ($checklists as $field => $label) {
            $columns[] = [
                'label' => $label,
                'width' => 20,
                'value' => fn (NokiaSite $site): string => $site->{$field} ? 'Sim' : 'Não',
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
