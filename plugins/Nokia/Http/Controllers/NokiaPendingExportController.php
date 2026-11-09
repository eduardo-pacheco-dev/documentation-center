<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Nokia\Support\NokiaPendingExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NokiaPendingExportController extends Controller
{
    /**
     * Export the pending activities and tasks to an Excel file.
     */
    public function export(NokiaPendingExport $export): StreamedResponse
    {
        return $this->download($export->spreadsheet(), $export->fileName());
    }

    private function download(Spreadsheet $spreadsheet, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');

            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
