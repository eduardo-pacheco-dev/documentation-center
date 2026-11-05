<?php

declare(strict_types=1);

namespace Plugins\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Plugins\Analytics\Http\Requests\StoreAnalyticsImportRequest;
use Plugins\Analytics\Support\AnalyticsImporter;
use Plugins\Analytics\Support\AnalyticsImportException;

class AnalyticsImportController extends Controller
{
    /**
     * Show the spreadsheet import page.
     */
    public function create(): View
    {
        return view('analytics::import');
    }

    /**
     * Import the analytics records from the uploaded spreadsheet.
     */
    public function store(StoreAnalyticsImportRequest $request, AnalyticsImporter $importer): RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        try {
            $result = $importer->import($file);
        } catch (AnalyticsImportException $exception) {
            $messages = array_slice($exception->errors, 0, 5);
            $message = implode(' ', $messages);

            if (count($exception->errors) > 5) {
                $message .= ' E mais '.(count($exception->errors) - 5).' erro(s).';
            }

            return back()->withErrors(['file' => $message]);
        }

        return redirect()
            ->route('admin.analytics.index')
            ->with('status', "Importação concluída: {$result['created']} criado(s) e {$result['updated']} atualizado(s).");
    }
}
