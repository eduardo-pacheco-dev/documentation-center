<?php

namespace Plugins\Stations\Support;

use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationAttachment;

final class StationExport
{
    /**
     * Build the workbook with every data related to the station.
     */
    public function spreadsheet(Station $station): Spreadsheet
    {
        $station->loadMissing([
            'attachments.uploader',
            'comments.user',
        ]);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Estação '.($station->station_id ?: $station->site_id))
            ->setDescription('Exportação completa dos dados da estação.');

        $this->stationSheet($spreadsheet, $station);
        $this->attachmentsSheet($spreadsheet, $station);
        $this->commentsSheet($spreadsheet, $station);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * The download filename for the exported workbook.
     */
    public function fileName(Station $station): string
    {
        $identifier = Str::slug((string) ($station->station_id ?: $station->site_id)) ?: 'estacao';

        return 'estacao-'.$identifier.'-'.now()->format('Y-m-d').'.xlsx';
    }

    private function stationSheet(Spreadsheet $spreadsheet, Station $station): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Estação');
        $sheet->getTabColor()->setRGB('4F46E5');

        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'Estação '.($station->station_id ?: $station->site_id));
        $this->styleTitle($sheet, 'A1:B1');

        $sheet->setCellValue('A2', 'Campo');
        $sheet->setCellValue('B2', 'Valor');
        $this->styleTableHeader($sheet, 'A2:B2');

        $row = 4;

        foreach (config('stations.fields', []) as $group) {
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, $group['title']);
            $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FF312E81']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
            ]);
            $row++;

            foreach ($group['fields'] as $field) {
                $this->writeRow($sheet, $row, $field['label'], $station->{$field['name']});
                $row++;
            }

            $row++;
        }

        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->setCellValue('A'.$row, 'Registro');
        $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF312E81']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
        ]);
        $row++;

        $this->writeRow($sheet, $row++, 'Criado em', $station->created_at?->format('d/m/Y H:i'));
        $this->writeRow($sheet, $row, 'Atualizado em', $station->updated_at?->format('d/m/Y H:i'));

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(80);
        $sheet->getStyle('A4:B'.$row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('B4:B'.$row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A4:B'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
        $sheet->freezePane('A4');
    }

    private function attachmentsSheet(Spreadsheet $spreadsheet, Station $station): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Anexos');
        $sheet->getTabColor()->setRGB('0EA5E9');

        $headings = ['Categoria', 'Arquivo', 'Tamanho', 'Enviado por', 'Data'];
        $this->writeTable($sheet, 'A1', $headings);

        $row = 2;

        foreach ($station->attachments as $attachment) {
            $sheet->setCellValueExplicit('A'.$row, $this->categoryLabel($attachment), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$row, $attachment->original_name, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $attachment->humanSize(), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, $attachment->uploader?->full_name ?? 'Usuário removido', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$row, $attachment->created_at?->format('d/m/Y H:i') ?? '—', DataType::TYPE_STRING);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getStyle('A1:E'.max(1, $row - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('B2:B'.max(1, $row - 1))->getAlignment()->setWrapText(true);
        $sheet->freezePane('A2');
    }

    private function commentsSheet(Spreadsheet $spreadsheet, Station $station): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Comentários');
        $sheet->getTabColor()->setRGB('F59E0B');

        $this->writeTable($sheet, 'A1', ['Autor', 'Data', 'Comentário']);

        $row = 2;

        foreach ($station->comments as $comment) {
            $sheet->setCellValueExplicit('A'.$row, $comment->user?->full_name ?? 'Usuário removido', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$row, $comment->created_at?->format('d/m/Y H:i') ?? '—', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $comment->body, DataType::TYPE_STRING);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(80);
        $sheet->getStyle('A1:C'.max(1, $row - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('C2:C'.max(1, $row - 1))->getAlignment()->setWrapText(true);
        $sheet->freezePane('A2');
    }

    private function writeRow(Worksheet $sheet, int $row, string $label, mixed $value): void
    {
        $sheet->setCellValueExplicit('A'.$row, $label, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit(
            'B'.$row,
            filled($value) ? (string) $value : '—',
            DataType::TYPE_STRING,
        );
    }

    /**
     * @param  list<string>  $headings
     */
    private function writeTable(Worksheet $sheet, string $startCell, array $headings): void
    {
        $columnIndex = Coordinate::columnIndexFromString(
            preg_replace('/\d+/', '', $startCell) ?: 'A',
        );
        $row = (int) preg_replace('/\D+/', '', $startCell);

        foreach ($headings as $heading) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex).$row, $heading);
            $columnIndex++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex($columnIndex - 1);
        $this->styleTableHeader($sheet, $startCell.':'.$lastColumn.$row);
    }

    private function styleTitle(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension((int) preg_replace('/\D+/', '', explode(':', $range)[0]))->setRowHeight(34);
    }

    private function styleTableHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    private function categoryLabel(StationAttachment $attachment): string
    {
        return $attachment->category === StationAttachment::CATEGORY_PPI_TSSR
            ? 'PPI/TSSR'
            : 'Anexo';
    }
}
