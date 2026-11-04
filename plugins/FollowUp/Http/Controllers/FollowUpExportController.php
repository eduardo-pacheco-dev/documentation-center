<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Support\FollowUpExport;
use Plugins\FollowUp\Support\FollowUpFilters;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FollowUpExportController extends Controller
{
    /**
     * Export all follow-ups matching the current filters.
     */
    public function export(Request $request, FollowUpExport $export): StreamedResponse
    {
        $query = FollowUpFilters::apply(
            FollowUp::query()->with(['assignee', 'creator']),
            $request,
        );

        $followUps = $query
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->get();

        return $this->download($export->all($followUps), $export->allFileName());
    }

    /**
     * Export a single follow-up with its history.
     */
    public function exportOne(FollowUp $followUp, FollowUpExport $export): StreamedResponse
    {
        return $this->download($export->single($followUp), $export->singleFileName($followUp));
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
