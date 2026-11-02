<?php

namespace Plugins\Erbs\Http\Controllers;

use Plugins\Erbs\Exports\ErbsTemplateExport;
use App\Http\Controllers\Controller;
use Plugins\Erbs\Http\Requests\ImportErbsRequest;
use Plugins\Erbs\Imports\ErbsImport;
use Plugins\Erbs\Models\Erb;
use Plugins\Erbs\Services\ErbBulkImporter;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ErbImportController extends Controller
{
    /**
     * Import ERBs from an uploaded spreadsheet.
     */
    public function store(ImportErbsRequest $request, ErbBulkImporter $importer): RedirectResponse
    {
        $sheets = Excel::toArray(new ErbsImport, $request->file('file'));

        $count = $importer->import($request->user(), $sheets[0] ?? []);

        return redirect()
            ->route('admin.erbs.index')
            ->with('status', $count === 1
                ? '1 ERB importada com sucesso.'
                : "{$count} ERBs importadas com sucesso.");
    }

    /**
     * Download the spreadsheet template used for the import.
     */
    public function template(): BinaryFileResponse
    {
        $this->authorize('create', Erb::class);

        return Excel::download(new ErbsTemplateExport, 'modelo-erbs.xlsx');
    }
}
