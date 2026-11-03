<?php

namespace Plugins\Stations\Support;

use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Plugins\Stations\Models\Station;

final class StationExportAll
{
    /**
     * Build a workbook with every station field and a site summary.
     */
    public function spreadsheet(): Spreadsheet
    {
        $stations = Station::query()
            ->orderBy('site_id')
            ->orderBy('station_id')
            ->get();

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Estações')
            ->setDescription('Exportação de todas as estações cadastradas.');

        $this->stationsSheet($spreadsheet, $stations);
        $this->sitesSheet($spreadsheet, $stations);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * The download filename for the workbook.
     */
    public function fileName(): string
    {
        return 'estacoes-'.now()->format('Y-m-d').'.xlsx';
    }

    /**
     * @param  Collection<int, Station>  $stations
     */
    private function stationsSheet(Spreadsheet $spreadsheet, Collection $stations): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Estações');
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
        foreach ($stations as $station) {
            foreach ($columns as $index => $column) {
                $value = $column['value']($station);
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
     * @param  Collection<int, Station>  $stations
     */
    private function sitesSheet(Spreadsheet $spreadsheet, Collection $stations): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Sites');
        $sheet->getTabColor()->setRGB('0EA5E9');

        $headings = ['ID do site', 'Município', 'UF', 'Estações', 'Atualizado em'];
        $columnIndex = 1;

        foreach ($headings as $heading) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex).'1', $heading);
            $columnIndex++;
        }

        $row = 2;
        foreach ($stations->groupBy('site_id')->sortKeys() as $siteId => $siteStations) {
            $latest = $siteStations->sortByDesc('updated_at')->first();

            $sheet->setCellValueExplicit('A'.$row, (string) $siteId, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$row, (string) ($latest?->municipio ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, (string) ($latest?->estado ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, (string) $siteStations->count(), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$row, $latest?->updated_at?->format('d/m/Y H:i') ?? '', DataType::TYPE_STRING);

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(20);

        $this->styleTableHeader($sheet, 'A1:E1');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:E1');

        if ($row > 2) {
            $sheet->getStyle('A2:E'.($row - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        }
    }

    /**
     * All exported columns in display order.
     *
     * @return list<array{label: string, width: int, value: callable(Station): mixed}>
     */
    private function columns(): array
    {
        $widths = [
            'site_id' => 18,
            'endereco_id' => 18,
            'tipo_de_elemento' => 18,
            'tecnologia' => 13,
            'tipo_de_conexao' => 17,
            'classificacao' => 15,
            'status' => 14,
            'station_id' => 18,
            'detentor_area' => 21,
            'tipo_de_infra' => 21,
            'tipo_de_ev' => 13,
            'tipo_da_torre' => 16,
            'regional' => 12,
            'cep' => 13,
            'logradouro' => 30,
            'numero' => 11,
            'complemento' => 22,
            'bairro' => 20,
            'municipio' => 20,
            'estado' => 12,
            'latitude' => 15,
            'longitude' => 15,
        ];

        $columns = collect(config('stations.fields', []))
            ->flatMap(fn (array $group): array => $group['fields'])
            ->map(fn (array $field): array => [
                'label' => $field['label'],
                'width' => $widths[$field['name']] ?? 16,
                'value' => fn (Station $station): mixed => $station->{$field['name']},
            ])
            ->values()
            ->all();

        $columns[] = [
            'label' => 'Criado em',
            'width' => 20,
            'value' => fn (Station $station): ?string => $station->created_at?->format('d/m/Y H:i'),
        ];

        $columns[] = [
            'label' => 'Atualizado em',
            'width' => 20,
            'value' => fn (Station $station): ?string => $station->updated_at?->format('d/m/Y H:i'),
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
