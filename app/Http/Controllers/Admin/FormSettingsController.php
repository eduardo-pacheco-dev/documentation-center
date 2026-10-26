<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFormSettingsRequest;
use App\Models\Form;
use App\Models\FormTemplate;
use App\Services\Forms\FormModuleSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
}
