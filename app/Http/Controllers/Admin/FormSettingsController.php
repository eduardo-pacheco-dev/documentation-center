<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FormSettingsTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportFormSettingsRequest;
use App\Http\Requests\Admin\UpdateFormSettingsRequest;
use App\Imports\FormSettingsImport;
use App\Models\Form;
use App\Models\FormTemplate;
use App\Services\Forms\FormModuleSettings;
use App\Services\Forms\FormSettingsTemplateImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FormSettingsController extends Controller
{
    /**
     * Show the configuration page for the forms module.
     */
    public function edit(Request $request): View
    {
        $this->authorize('viewAny', Form::class);

        return view('admin.forms.settings', [
            'settings' => FormModuleSettings::for($request->user()),
            'templates' => FormTemplate::query()->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    /**
     * Save the configuration for the forms module.
     */
    public function update(UpdateFormSettingsRequest $request): RedirectResponse
    {
        $request->user()->update([
            'form_settings' => [
                'types' => $request->validated('types'),
                'statuses' => $request->validated('statuses'),
                'default_template_id' => $request->validated('default_template_id'),
                'default_view' => $request->validated('default_view'),
                'per_page' => (int) $request->validated('per_page'),
            ],
        ]);

        return redirect()
            ->route('admin.forms.settings.edit')
            ->with('status', 'Configurações do módulo atualizadas com sucesso.');
    }

    /**
     * Download the current configuration as an Excel template.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Form::class);

        $settings = FormModuleSettings::for($request->user());

        $defaultTemplateId = $settings->defaultTemplateId();
        $defaultTemplate = $defaultTemplateId !== null
            ? FormTemplate::query()->find($defaultTemplateId)
            : null;

        return Excel::download(
            new FormSettingsTemplateExport(
                $settings->types(),
                $settings->statuses(),
                $settings->defaultView(),
                $settings->perPage(),
                $defaultTemplate?->name,
            ),
            'configuracoes-formularios.xlsx',
        );
    }

    /**
     * Import a configuration template from an uploaded spreadsheet.
     */
    public function import(ImportFormSettingsRequest $request, FormSettingsTemplateImporter $importer): RedirectResponse
    {
        $sheets = Excel::toArray(new FormSettingsImport, $request->file('file'));

        try {
            $settings = $importer->import($sheets[0] ?? []);
        } catch (ValidationException $exception) {
            throw $exception->redirectTo(route('admin.forms.settings.edit'));
        }

        $request->user()->update([
            'form_settings' => $settings,
        ]);

        return redirect()
            ->route('admin.forms.settings.edit')
            ->with('status', 'Template de configurações importado com sucesso.');
    }
}
