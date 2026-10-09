<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WorkOrdersTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportWorkOrdersRequest;
use App\Imports\WorkOrdersImport;
use App\Models\WorkOrder;
use App\Services\WorkOrders\WorkOrderBulkImporter;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkOrderImportController extends Controller
{
    /**
     * Import work orders from an uploaded spreadsheet.
     */
    public function store(ImportWorkOrdersRequest $request, WorkOrderBulkImporter $importer): RedirectResponse
    {
        $sheets = Excel::toArray(new WorkOrdersImport, $request->file('file'));

        $count = $importer->import($request->user(), $sheets[0] ?? []);

        return redirect()
            ->route('admin.work-orders.index')
            ->with('status', $count === 1
                ? '1 ordem de serviço importada com sucesso.'
                : "{$count} ordens de serviço importadas com sucesso.");
    }

    /**
     * Download the spreadsheet template used for the import.
     */
    public function template(): BinaryFileResponse
    {
        $this->authorize('create', WorkOrder::class);

        return Excel::download(new WorkOrdersTemplateExport, 'modelo-ordens-de-servico.xlsx');
    }
}
