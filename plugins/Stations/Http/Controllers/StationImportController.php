<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Plugins\Stations\Http\Requests\StoreStationImportRequest;
use Plugins\Stations\Support\StationImporter;
use Plugins\Stations\Support\StationImportException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StationImportController extends Controller
{
    /**
     * Show the bulk import page.
     */
    public function create(): View
    {
        return view('stations::import');
    }

    /**
     * Import the stations from the uploaded CSV file.
     */
    public function store(StoreStationImportRequest $request, StationImporter $importer): RedirectResponse
    {
        try {
            $count = $importer->import($request->file('file'));
        } catch (StationImportException $exception) {
            $messages = array_slice($exception->errors, 0, 5);
            $message = implode(' ', $messages);

            if (count($exception->errors) > 5) {
                $message .= ' E mais '.(count($exception->errors) - 5).' erro(s).';
            }

            return back()->withErrors(['file' => $message]);
        }

        return redirect()
            ->route('admin.stations.index')
            ->with('status', $count === 1
                ? '1 estação importada com sucesso.'
                : "{$count} estações importadas com sucesso.");
    }

    /**
     * Download the Excel template shipped with the plugin.
     */
    public function template(): StreamedResponse
    {
        $path = dirname(__DIR__, 2).'/Resources/templates/stations-import.xlsx';

        abort_unless(is_file($path), 404);

        return response()->streamDownload(function () use ($path): void {
            readfile($path);
        }, 'modelo-importacao-estacoes.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
