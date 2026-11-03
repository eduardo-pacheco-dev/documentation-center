<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Support\StationExport;
use Plugins\Stations\Support\StationExportAll;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StationExportController extends Controller
{
    /**
     * Export every data related to the station to an Excel file.
     */
    public function export(Station $station, StationExport $export): StreamedResponse
    {
        return $this->download($export->spreadsheet($station), $export->fileName($station));
    }

    /**
     * Export every registered station and a summary of the sites.
     */
    public function exportAll(StationExportAll $export): StreamedResponse
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
