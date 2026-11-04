<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Plugins\FollowUp\Http\Requests\StoreFollowUpImportRequest;
use Plugins\FollowUp\Support\FollowUpImporter;
use Plugins\FollowUp\Support\FollowUpImportException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FollowUpImportController extends Controller
{
    /**
     * Show the bulk import page.
     */
    public function create(): View
    {
        return view('follow-up::import');
    }

    /**
     * Import the follow-ups from the uploaded file.
     */
    public function store(StoreFollowUpImportRequest $request, FollowUpImporter $importer): RedirectResponse
    {
        try {
            $count = $importer->import($request->file('file'), $request->user()->getKey());
        } catch (FollowUpImportException $exception) {
            $messages = array_slice($exception->errors, 0, 5);
            $message = implode(' ', $messages);

            if (count($exception->errors) > 5) {
                $message .= ' E mais '.(count($exception->errors) - 5).' erro(s).';
            }

            return back()->withErrors(['file' => $message]);
        }

        return redirect()
            ->route('admin.follow-ups.index')
            ->with('status', $count === 1
                ? '1 follow-up importado com sucesso.'
                : "{$count} follow-ups importados com sucesso.");
    }

    /**
     * Download the Excel template shipped with the plugin.
     */
    public function template(): StreamedResponse
    {
        $path = dirname(__DIR__, 2).'/Resources/templates/follow-ups-import.xlsx';

        abort_unless(is_file($path), 404);

        return response()->streamDownload(function () use ($path): void {
            readfile($path);
        }, 'modelo-importacao-follow-ups.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
