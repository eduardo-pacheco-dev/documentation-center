<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ColaboradoresTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportColaboradoresRequest;
use App\Imports\ColaboradoresImport;
use App\Models\Colaborador;
use App\Services\Colaboradores\ColaboradorBulkImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ColaboradorImportController extends Controller
{
    /**
     * Import colaboradores from an uploaded spreadsheet.
     */
    public function store(ImportColaboradoresRequest $request, ColaboradorBulkImporter $importer): JsonResponse|RedirectResponse
    {
        $sheets = Excel::toArray(new ColaboradoresImport, $request->file('file'));

        $count = $importer->import($request->user(), $sheets[0] ?? []);

        $message = $count === 1
            ? '1 colaborador importado com sucesso.'
            : "{$count} colaboradores importados com sucesso.";

        if ($request->expectsJson()) {
            $request->session()->flash('status', $message);

            return response()->json([
                'message' => $message,
                'redirect' => route('admin.colaboradores.index'),
            ]);
        }

        return redirect()
            ->route('admin.colaboradores.index')
            ->with('status', $message);
    }

    /**
     * Download the spreadsheet template used for the import.
     */
    public function template(): BinaryFileResponse
    {
        $this->authorize('create', Colaborador::class);

        return Excel::download(new ColaboradoresTemplateExport, 'modelo-colaboradores.xlsx');
    }
}
