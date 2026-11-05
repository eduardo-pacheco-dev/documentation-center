<?php

declare(strict_types=1);

namespace Plugins\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Analytics\Http\Requests\StoreAnalyticsImportRequest;
use Plugins\Analytics\Support\AnalyticsImporter;
use Plugins\Analytics\Support\AnalyticsImportException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsImportController extends Controller
{
    /**
     * Show the spreadsheet import page.
     */
    public function create(): View
    {
        return view('analytics::import');
    }

    /**
     * Download the import template.
     */
    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Analytics');

        $sheet->fromArray([
            [
                'OC', 'OS', 'Data Criação', 'Fornecedor', 'Fornecedor Fiscalização',
                'Tipo Projeto', 'Etapa Processo', 'Tipo Elemento', 'Endereço Atendimento',
                'Cidade', 'Estado', 'Node Name', 'Localização', 'Operador', 'Status',
            ],
            [
                'OC-2026-0001', 'OS-2026-0001', '2026-10-20', 'NOKIA', 'VANTAGE',
                'Acesso', 'Instalação', 'MISTO', 'Rua das Telecomunicações, 100',
                'São Paulo', 'SP', 'SN-BLMOK3', 'PABLM_0037', 'TIM', 'Aceito pela TIM',
            ],
        ]);

        $sheet->getStyle('A1:O1')->getFont()->setBold(true);

        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'modelo-analytics.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import the analytics records from the uploaded spreadsheet.
     */
    public function store(StoreAnalyticsImportRequest $request, AnalyticsImporter $importer): RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        try {
            $result = $importer->import($file);
        } catch (AnalyticsImportException $exception) {
            $messages = array_slice($exception->errors, 0, 5);
            $message = implode(' ', $messages);

            if (count($exception->errors) > 5) {
                $message .= ' E mais '.(count($exception->errors) - 5).' erro(s).';
            }

            return back()->withErrors(['file' => $message]);
        }

        return redirect()
            ->route('admin.analytics.index')
            ->with('status', "Importação concluída: {$result['created']} criado(s) e {$result['updated']} atualizado(s).");
    }
}
