<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Support\FollowUpExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FollowUpExportController extends Controller
{
    /**
     * Export all follow-ups matching the current filters.
     */
    public function export(Request $request, FollowUpExport $export): StreamedResponse
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $priority = $request->string('priority')->trim()->toString();
        $assigned = $request->string('assigned')->trim()->toString();

        $query = FollowUp::query()->with(['assignee', 'creator']);

        if ($search !== '') {
            $query->matching($search);
        }

        if (in_array($status, FollowUpStatus::values(), true)) {
            $query->where('status', $status);
        }

        if (in_array($priority, FollowUpPriority::values(), true)) {
            $query->where('priority', $priority);
        }

        if ($assigned === 'me') {
            $query->where('assigned_to', $request->user()->getKey());
        } elseif (ctype_digit($assigned)) {
            $query->where('assigned_to', (int) $assigned);
        }

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
