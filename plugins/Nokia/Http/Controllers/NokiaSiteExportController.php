<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Nokia\Support\NokiaSiteExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NokiaSiteExportController extends Controller
{
    /**
     * Export the registered sites to an Excel file.
     */
    public function export(Request $request, NokiaSiteExport $export): StreamedResponse
    {
        $search = $request->string('search')->trim()->toString();

        return $this->download($export->spreadsheet($search), $export->fileName());
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
