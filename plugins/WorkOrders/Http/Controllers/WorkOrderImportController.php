<?php

namespace Plugins\WorkOrders\Http\Controllers;

use Plugins\WorkOrders\Exports\WorkOrdersTemplateExport;
use App\Http\Controllers\Controller;
use Plugins\WorkOrders\Http\Requests\ImportWorkOrdersRequest;
use Plugins\WorkOrders\Imports\WorkOrdersImport;
use Plugins\WorkOrders\Models\WorkOrder;
use Plugins\WorkOrders\Services\WorkOrderBulkImporter;
use Plugins\WorkOrders\Services\WorkOrderCompleteImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkOrderImportController extends Controller
{
    /**
     * Import work orders from an uploaded spreadsheet.
     */
    public function store(
        ImportWorkOrdersRequest $request,
        WorkOrderBulkImporter $simplified,
        WorkOrderCompleteImporter $complete,
    ): RedirectResponse {
        $sheets = Excel::toArray(new WorkOrdersImport, $request->file('file'));

        $count = $request->input('mode') === 'completo'
            ? $complete->import($request->user(), $sheets[0] ?? [])
            : $simplified->import($request->user(), $sheets[0] ?? []);

        return redirect()
            ->route('admin.work-orders.index')
            ->with('status', $count === 1
                ? '1 ordem de serviço importada ou atualizada com sucesso.'
                : "{$count} ordens de serviço importadas ou atualizadas com sucesso.");
    }

    /**
     * Download the spreadsheet template used for the import.
     */
    public function template(Request $request): BinaryFileResponse
    {
        $this->authorize('create', WorkOrder::class);

        $complete = $request->query('mode') === 'completo';

        return Excel::download(
            new WorkOrdersTemplateExport($request->user(), $complete),
            $complete ? 'modelo-ordens-de-servico-completo.xlsx' : 'modelo-ordens-de-servico.xlsx',
        );
    }
}
