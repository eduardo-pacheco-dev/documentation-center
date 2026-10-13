<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFormTemplateRequest;
use App\Http\Requests\Admin\UpdateFormTemplateRequest;
use App\Models\Form;
use App\Models\FormTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    /**
     * List the templates shared across the organization.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FormTemplate::class);

        $search = (string) $request->query('search', '');

        if (in_array($request->query('sort'), ['name', 'type', 'created_at', 'updated_at'], true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'updated_at' ? 'desc' : 'asc');
        } else {
            $sort = 'name';
            $direction = 'asc';
        }

        $perPage = in_array($request->integer('per_page'), [12, 24, 36], true)
            ? $request->integer('per_page')
            : 12;

        $templates = FormTemplate::query()
            ->with('user:id,name')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.form-templates.index', [
            'templates' => $templates,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Create a shared template from one of the user's forms.
     */
    public function store(StoreFormTemplateRequest $request): RedirectResponse
    {
        $form = Form::query()
            ->where('user_id', $request->user()->getKey())
            ->findOrFail($request->integer('form_id'));

        FormTemplate::create([
            'user_id' => $request->user()->getKey(),
            'name' => $request->string('name')->toString(),
            'type' => $form->type,
            'steps' => $form->steps ?? [],
        ]);

        return redirect()
            ->route('admin.form-templates.index')
            ->with('status', 'Template criado com sucesso.');
    }

    /**
     * Show the form to edit a template.
     */
    public function edit(FormTemplate $formTemplate): View
    {
        $this->authorize('update', $formTemplate);

        $formTemplate->load('user:id,name');

        return view('admin.form-templates.edit', [
            'template' => $formTemplate,
            'types' => ['Registro Fotográfico', 'Checklist', 'Vistoria', 'Inspeção', 'Laudo Técnico'],
        ]);
    }

    /**
     * Update the template details and structure.
     */
    public function update(UpdateFormTemplateRequest $request, FormTemplate $formTemplate): RedirectResponse
    {
        $attributes = $request->validated();

        $formTemplate->update([
            'name' => $attributes['name'],
            'type' => $attributes['type'] ?? null,
            'steps' => $attributes['steps'] ?? [],
        ]);

        return back()->with('status', 'Template atualizado com sucesso.');
    }

    /**
     * Delete the template.
     */
    public function destroy(FormTemplate $formTemplate): RedirectResponse
    {
        $this->authorize('delete', $formTemplate);

        $formTemplate->delete();

        return redirect()
            ->route('admin.form-templates.index')
            ->with('status', 'Template excluído com sucesso.');
    }
}
