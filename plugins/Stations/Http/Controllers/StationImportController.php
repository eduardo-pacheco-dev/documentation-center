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
     * Download the CSV template with all accepted columns.
     */
    public function template(): StreamedResponse
    {
        $fields = collect(config('stations.fields', []))
            ->flatMap(fn (array $group): array => $group['fields'])
            ->pluck('name')
            ->all();

        return response()->streamDownload(function () use ($fields): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $fields, ';');
            fclose($output);
        }, 'modelo-importacao-estacoes.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
