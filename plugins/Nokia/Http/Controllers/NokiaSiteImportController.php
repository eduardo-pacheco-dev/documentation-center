<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteImportRequest;
use Plugins\Nokia\Support\NokiaSiteImporter;
use Plugins\Nokia\Support\NokiaSiteImportException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NokiaSiteImportController extends Controller
{
    /**
     * Show the bulk import page.
     */
    public function create(): View
    {
        return view('nokia::import');
    }

    /**
     * Import the sites from the uploaded file.
     */
    public function store(StoreNokiaSiteImportRequest $request, NokiaSiteImporter $importer): RedirectResponse
    {
        try {
            $count = $importer->import($request->file('file'));
        } catch (NokiaSiteImportException $exception) {
            $messages = array_slice($exception->errors, 0, 5);
            $message = implode(' ', $messages);

            if (count($exception->errors) > 5) {
                $message .= ' E mais '.(count($exception->errors) - 5).' erro(s).';
            }

            return back()->withErrors(['file' => $message]);
        }

        return redirect()
            ->route('admin.nokia.sites.index')
            ->with('status', $count === 1
                ? '1 site importado com sucesso.'
                : "{$count} sites importados com sucesso.");
    }

    /**
     * Download the Excel template with the expected columns.
     */
    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Importação de sites Nokia')
            ->setDescription('Modelo para importação em massa de sites Nokia.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sites');

        $headings = [
            'Site ID *',
            'End ID',
            'OC',
            'OS FAM Entrega',
            'OS FAM Instalação',
            'Tarefas enviadas',
            'FAM Entrega',
            'FAM Instalação',
            'FAM Panorâmica',
        ];

        foreach ($headings as $index => $heading) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'1', $heading);
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headings));

        foreach (range('A', $lastColumn) as $letter) {
            $sheet->getColumnDimension($letter)->setWidth($letter === 'A' ? 20 : 22);
        }

        $sheet->getStyle('A1:'.$lastColumn.'1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->freezePane('A2');

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instruções');
        $instructions->setCellValue('A1', 'Instruções');
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructions->getColumnDimension('A')->setWidth(110);

        $lines = [
            'Preencha a aba "Sites" a partir da linha 2. A linha 1 contém os nomes das colunas.',
            'A coluna "Site ID" é obrigatória.',
            'No checklist, use Sim ou Não (também aceito: 1/0, x, vazio).',
            'A importação é tudo ou nada: se alguma linha for inválida, nenhum site é importado.',
            'Limite de '.NokiaSiteImporter::MAX_SITES.' sites por arquivo.',
            'Também é possível importar um arquivo CSV com as mesmas colunas.',
        ];

        foreach ($lines as $index => $line) {
            $instructions->setCellValue('A'.($index + 3), $line);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');

            $spreadsheet->disconnectWorksheets();
        }, 'modelo-importacao-sites-nokia.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
