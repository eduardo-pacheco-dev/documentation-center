<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteImportRequest;
use Plugins\Nokia\Models\NokiaTask;
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
        return view('nokia::import', [
            'tasks' => NokiaTask::query()->orderBy('position')->orderBy('name')->get(),
        ]);
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
                ? '1 site importado ou atualizado com sucesso.'
                : "{$count} sites importados ou atualizados com sucesso.");
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
            'OC *',
            'OS FAM Entrega',
            'OS FAM Instalação',
            'Regional',
            'Subcon',
            'FAM Entrega',
            'FAM Instalação',
            'FAM Panorâmica',
            'MOS Plan',
            'MOS Real',
            'Instalação Plan',
            'Instalação Real',
            'Integração Plan',
            'Integração Real',
            'RFA Plan',
            'RFA Real',
            'FAM Entrega Aprovado',
            'FAM Instalação Aprovado',
            'FAM Panorâmica Aprovado',
        ];

        foreach (NokiaTask::query()->orderBy('position')->orderBy('name')->pluck('name') as $taskName) {
            $headings[] = 'Tarefa - '.$taskName;
        }

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

        $this->addBooleanValidations($sheet, $headings);

        $example = $spreadsheet->createSheet();
        $example->setTitle('Exemplo');

        $exampleValues = [
            'Site ID *' => 'SPEXEMPLO01',
            'End ID' => 'END-00001',
            'OC *' => 'OC-0001',
            'OS FAM Entrega' => 'OS-E-00001',
            'OS FAM Instalação' => 'OS-I-00001',
            'Regional' => 'TNO',
            'Subcon' => 'AFL',
            'FAM Entrega' => 'Sim',
            'FAM Instalação' => 'Não',
            'FAM Panorâmica' => 'Não',
        ];

        foreach ($headings as $index => $heading) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);
            $example->setCellValue($letter.'1', $heading);
            $example->getColumnDimension($letter)->setWidth($letter === 'A' ? 20 : 22);
            $example->setCellValue($letter.'2', $exampleValues[$heading] ?? $this->exampleValueFor($heading));
        }

        $example->getStyle('A1:'.$lastColumn.'1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $example->getRowDimension(1)->setRowHeight(28);
        $example->freezePane('A2');

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instruções');
        $instructions->setCellValue('A1', 'Instruções');
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructions->getColumnDimension('A')->setWidth(110);

        $lines = [
            'Preencha a aba "Sites" a partir da linha 2. A linha 1 contém os nomes das colunas.',
            'A aba "Exemplo" mostra uma linha preenchida apenas como referência; ela não é importada.',
            'As colunas "Site ID" e "OC" são obrigatórias.',
            'No checklist e nas colunas "Tarefa - ...", use Sim ou Não (também aceito: 1/0, x).',
            'Nas colunas de data, use dd/mm/aaaa (também aceito: aaaa-mm-dd). Deixe vazio para limpar a data.',
            'Sites com um OC já cadastrado são atualizados com os dados da planilha.',
            'A importação é tudo ou nada: se alguma linha for inválida, nenhum site é importado ou atualizado.',
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

    /**
     * Add a Sim/Não dropdown to the checklist and task columns.
     *
     * @param  array<int, string>  $headings
     */
    private function addBooleanValidations(Worksheet $sheet, array $headings): void
    {
        $checklists = array_values(config('nokia.checklists', []));

        foreach ($headings as $index => $heading) {
            $isChecklist = in_array($heading, $checklists, true);
            $isTask = str_starts_with($heading, 'Tarefa - ');

            if (! $isChecklist && ! $isTask) {
                continue;
            }

            $letter = Coordinate::stringFromColumnIndex($index + 1);

            $validation = $sheet->getCell($letter.'2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Use Sim ou Não.');
            $validation->setFormula1('"Sim,Não"');

            $sheet->setDataValidation($letter.'2:'.$letter.'501', $validation);
        }
    }

    /**
     * The illustrative value used on the example sheet.
     */
    private function exampleValueFor(string $heading): string
    {
        if (str_starts_with($heading, 'Tarefa - ') || str_contains($heading, 'Aprovado')) {
            return 'Sim';
        }

        if (in_array($heading, ['MOS Plan', 'Instalação Plan', 'Integração Plan', 'RFA Plan'], true)) {
            return '10/01/2026';
        }

        if (in_array($heading, ['MOS Real', 'Instalação Real', 'Integração Real', 'RFA Real'], true)) {
            return '12/01/2026';
        }

        return '';
    }
}
