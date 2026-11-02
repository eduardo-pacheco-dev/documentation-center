<?php

namespace Plugins\Clients\Http\Controllers;

use Plugins\Clients\Exports\ClientsTemplateExport;
use App\Http\Controllers\Controller;
use Plugins\Clients\Http\Requests\ImportClientsRequest;
use Plugins\Clients\Imports\ClientsImport;
use Plugins\Clients\Models\Client;
use Plugins\Clients\Services\ClientBulkImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClientImportController extends Controller
{
    /**
     * Import clients from an uploaded spreadsheet.
     */
    public function store(ImportClientsRequest $request, ClientBulkImporter $importer): JsonResponse|RedirectResponse
    {
        $sheets = Excel::toArray(new ClientsImport, $request->file('file'));

        $count = $importer->import($request->user(), $sheets[0] ?? []);

        $message = $count === 1
            ? '1 cliente importado com sucesso.'
            : "{$count} clientes importados com sucesso.";

        if ($request->expectsJson()) {
            $request->session()->flash('status', $message);

            return response()->json([
                'message' => $message,
                'redirect' => route('admin.clients.index'),
            ]);
        }

        return redirect()
            ->route('admin.clients.index')
            ->with('status', $message);
    }

    /**
     * Download the spreadsheet template used for the import.
     */
    public function template(): BinaryFileResponse
    {
        $this->authorize('create', Client::class);

        return Excel::download(new ClientsTemplateExport, 'modelo-clientes.xlsx');
    }
}
