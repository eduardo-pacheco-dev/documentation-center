<?php

namespace Plugins\FollowUp\Support;

use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Plugins\FollowUp\Models\FollowUp;

final class FollowUpExport
{
    /**
     * Build a workbook with the given follow-ups.
     *
     * @param  Collection<int, FollowUp>  $followUps
     */
    public function all(Collection $followUps): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Follow-ups')
            ->setDescription('Exportação de follow-ups.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Follow-ups');
        $sheet->getTabColor()->setRGB('4F46E5');

        $columns = [
            ['label' => 'Código', 'width' => 16, 'value' => fn (FollowUp $followUp): string => (string) ($followUp->code ?? '')],
            ['label' => 'Título', 'width' => 34, 'value' => fn (FollowUp $followUp): string => $followUp->title],
            ['label' => 'Descrição', 'width' => 50, 'value' => fn (FollowUp $followUp): string => (string) ($followUp->description ?? '')],
            ['label' => 'Status', 'width' => 16, 'value' => fn (FollowUp $followUp): string => $followUp->status->label()],
            ['label' => 'Prioridade', 'width' => 14, 'value' => fn (FollowUp $followUp): string => $followUp->priority->label()],
            ['label' => 'Responsável', 'width' => 28, 'value' => fn (FollowUp $followUp): string => $followUp->assignee?->full_name ?? 'Não atribuído'],
            ['label' => 'Prazo', 'width' => 14, 'value' => fn (FollowUp $followUp): string => $followUp->due_date?->format('d/m/Y') ?? ''],
            ['label' => 'Atrasado', 'width' => 11, 'value' => fn (FollowUp $followUp): string => $followUp->isOverdue() ? 'Sim' : 'Não'],
            ['label' => 'Concluído em', 'width' => 20, 'value' => fn (FollowUp $followUp): string => $followUp->completed_at?->format('d/m/Y') ?? ''],
            ['label' => 'Criado por', 'width' => 28, 'value' => fn (FollowUp $followUp): string => $followUp->creator?->full_name ?? 'Usuário removido'],
            ['label' => 'Criado em', 'width' => 20, 'value' => fn (FollowUp $followUp): string => $followUp->created_at?->format('d/m/Y H:i') ?? ''],
            ['label' => 'Atualizado em', 'width' => 20, 'value' => fn (FollowUp $followUp): string => $followUp->updated_at?->format('d/m/Y H:i') ?? ''],
        ];

        $columnIndex = 1;
        foreach ($columns as $column) {
            $letter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($letter.'1', $column['label']);
            $sheet->getColumnDimension($letter)->setWidth($column['width']);
            $columnIndex++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($columns));
        $row = 2;

        foreach ($followUps as $followUp) {
            foreach ($columns as $index => $column) {
                $letter = Coordinate::stringFromColumnIndex($index + 1);

                $sheet->setCellValueExplicit(
                    $letter.$row,
                    $column['value']($followUp),
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
            $sheet->getStyle('B2:B'.($row - 1))->getAlignment()->setWrapText(true);
            $sheet->getStyle($bodyRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
        }

        return $spreadsheet;
    }

    /**
     * Build a workbook with the details and history of one follow-up.
     */
    public function single(FollowUp $followUp): Spreadsheet
    {
        $followUp->loadMissing(['assignee', 'creator', 'updates.user']);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle($followUp->title)
            ->setDescription('Exportação do follow-up.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Follow-up');
        $sheet->getTabColor()->setRGB('4F46E5');

        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', $followUp->title);
        $this->styleTitle($sheet, 'A1:B1');

        $sheet->setCellValue('A2', 'Campo');
        $sheet->setCellValue('B2', 'Valor');
        $this->styleTableHeader($sheet, 'A2:B2');

        $rows = [
            ['Código', $followUp->code ?? '—'],
            ['Descrição', $followUp->description ?? '—'],
            ['Status', $followUp->status->label()],
            ['Prioridade', $followUp->priority->label()],
            ['Responsável', $followUp->assignee?->full_name ?? 'Não atribuído'],
            ['Prazo', $followUp->due_date?->format('d/m/Y') ?? '—'],
            ['Atrasado', $followUp->isOverdue() ? 'Sim' : 'Não'],
            ['Concluído em', $followUp->completed_at?->format('d/m/Y') ?? '—'],
            ['Criado por', $followUp->creator?->full_name ?? 'Usuário removido'],
            ['Criado em', $followUp->created_at?->format('d/m/Y H:i') ?? '—'],
            ['Atualizado em', $followUp->updated_at?->format('d/m/Y H:i') ?? '—'],
        ];

        $row = 4;
        foreach ($rows as [$label, $value]) {
            $sheet->setCellValueExplicit('A'.$row, $label, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$row, (string) $value, DataType::TYPE_STRING);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(80);
        $sheet->getStyle('A4:B'.($row - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('B4:B'.($row - 1))->getAlignment()->setWrapText(true);
        $sheet->getStyle('A4:B'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
        $sheet->freezePane('A4');

        $this->historySheet($spreadsheet, $followUp);

        return $spreadsheet;
    }

    public function allFileName(): string
    {
        return 'follow-ups-'.now()->format('Y-m-d').'.xlsx';
    }

    public function singleFileName(FollowUp $followUp): string
    {
        return 'follow-up-'.$followUp->getKey().'-'.now()->format('Y-m-d').'.xlsx';
    }

    private function historySheet(Spreadsheet $spreadsheet, FollowUp $followUp): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Histórico');
        $sheet->getTabColor()->setRGB('F59E0B');

        $sheet->setCellValue('A1', 'Autor');
        $sheet->setCellValue('B1', 'Data');
        $sheet->setCellValue('C1', 'De');
        $sheet->setCellValue('D1', 'Para');
        $sheet->setCellValue('E1', 'Atualização');
        $this->styleTableHeader($sheet, 'A1:E1');

        $row = 2;
        foreach ($followUp->updates as $update) {
            $sheet->setCellValueExplicit('A'.$row, $update->user?->full_name ?? 'Usuário removido', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$row, $update->created_at?->format('d/m/Y H:i') ?? '—', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $update->status_from?->label() ?? '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, $update->status_to?->label() ?? '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$row, $update->body, DataType::TYPE_STRING);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(70);
        $sheet->getStyle('A1:E'.max(1, $row - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('E2:E'.max(1, $row - 1))->getAlignment()->setWrapText(true);
        $sheet->freezePane('A2');
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
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }
}
