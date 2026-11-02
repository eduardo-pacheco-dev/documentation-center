<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Plugins\Stations\Exports\StationsTemplateExport;
use Plugins\Stations\Http\Requests\ImportStationsRequest;
use Plugins\Stations\Imports\StationsImport;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Services\StationBulkImporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StationImportController extends Controller
{
    /**
     * Import stations from an uploaded spreadsheet.
     */
    public function store(ImportStationsRequest $request, StationBulkImporter $importer): RedirectResponse
    {
        $sheets = Excel::toArray(new StationsImport, $request->file('file'));

        $count = $importer->import($request->user(), $sheets[0] ?? []);

        return redirect()
            ->route('stations.index')
            ->with('status', $count.' station(s) importada(s) com sucesso.');
    }

    /**
     * Download the import template.
     */
    public function template(): BinaryFileResponse
    {
        $this->authorize('create', Station::class);

        return Excel::download(new StationsTemplateExport, 'modelo-stations.xlsx');
    }
}
