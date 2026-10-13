<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFormRequest;
use App\Http\Requests\Admin\UpdateFormRequest;
use App\Http\Requests\Admin\UpdateFormStepsRequest;
use App\Models\Form;
use App\Models\FormTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormController extends Controller
{
    /**
     * List the forms the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Form::class);

        $search = (string) $request->query('search', '');

        $status = (string) $request->query('status', '');

        $projectId = $request->integer('project_id') ?: null;

        if (in_array($request->query('sort'), ['name', 'code', 'type', 'status', 'created_at', 'updated_at'], true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'updated_at' ? 'desc' : 'asc');
        } else {
            $sort = 'updated_at';
            $direction = 'desc';
        }

        $perPage = in_array($request->integer('per_page'), [12, 24, 36], true)
            ? $request->integer('per_page')
            : 12;

        $forms = Form::query()
            ->ownedBy($request->user())
            ->with('project:id,code,name')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($projectId !== null, fn (Builder $query) => $query->where('project_id', $projectId))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.forms.index', [
            'forms' => $forms,
            'projects' => $request->user()->projects()->orderBy('name')->get(['id', 'code', 'name']),
            'search' => $search,
            'status' => $status,
            'projectId' => $projectId,
            'perPage' => $perPage,
            'types' => ['Registro Fotográfico', 'Checklist', 'Vistoria', 'Inspeção', 'Laudo Técnico'],
            'statuses' => ['Pendente', 'Em preenchimento', 'Preenchido', 'Aprovado', 'Reprovado'],
            'templates' => FormTemplate::query()->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    /**
     * Show the form to create a form.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Form::class);

        return view('admin.forms.create', [
            'projects' => $request->user()->projects()->orderBy('name')->get(['id', 'code', 'name']),
            'types' => ['Registro Fotográfico', 'Checklist', 'Vistoria', 'Inspeção', 'Laudo Técnico'],
            'statuses' => ['Pendente', 'Em preenchimento', 'Preenchido', 'Aprovado', 'Reprovado'],
            'templates' => FormTemplate::query()->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    /**
     * Create a form owned by the authenticated user.
     */
    public function store(StoreFormRequest $request): RedirectResponse
    {
        $attributes = $request->validated();

        $steps = $request->filled('steps_json')
            ? json_decode($request->input('steps_json'), true)
            : $this->templateSteps($attributes['template_id'] ?? null);

        $form = $request->user()->forms()->create([
            'project_id' => $attributes['project_id'],
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'status' => $attributes['status'],
            'steps' => $steps,
            'fields' => $request->filled('fields_json') ? json_decode($request->input('fields_json'), true) : [],
        ]);

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('status', 'Formulário criado com sucesso.');
    }

    /**
     * Show the form and its application answers in read only mode.
     */
    public function show(Request $request, Form $form): View
    {
        $this->authorize('view', $form);

        $form->load('project:id,code,name', 'user:id,name');

        return view('admin.forms.show', [
            'form' => $form,
            'steps' => $form->steps ?? [],
        ]);
    }

    /**
     * Resolve the starting structure from an optional template.
     *
     * @return array<int, array<string, mixed>>
     */
    private function templateSteps(?int $templateId): array
    {
        if ($templateId === null) {
            return [];
        }

        return FormTemplate::query()->find($templateId)?->steps ?? [];
    }

    /**
     * Show the form details and allow editing them.
     */
    public function edit(Request $request, Form $form): View
    {
        $this->authorize('update', $form);

        $form->load('project:id,code,name', 'user:id,name');

        return view('admin.forms.edit', [
            'form' => $form,
            'projects' => $request->user()->projects()->orderBy('name')->get(['id', 'code', 'name']),
            'types' => ['Registro Fotográfico', 'Checklist', 'Vistoria', 'Inspeção', 'Laudo Técnico'],
            'statuses' => ['Pendente', 'Em preenchimento', 'Preenchido', 'Aprovado', 'Reprovado'],
            'steps' => $form->steps ?? [],
        ]);
    }

    /**
     * Update the form details.
     */
    public function update(UpdateFormRequest $request, Form $form): RedirectResponse
    {
        $attributes = $request->validated();

        $form->update([
            'project_id' => $attributes['project_id'],
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'status' => $attributes['status'],
            'steps' => $request->filled('steps_json') ? json_decode($request->input('steps_json'), true) : $form->steps,
            'fields' => $request->filled('fields_json') ? json_decode($request->input('fields_json'), true) : $form->fields,
        ]);

        return back()->with('status', 'Formulário atualizado com sucesso.');
    }

    /**
     * Update the form structure (sections and questions) from the builder.
     */
    public function updateSteps(UpdateFormStepsRequest $request, Form $form): RedirectResponse
    {
        $form->update(['steps' => $request->input('steps', [])]);

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('status', 'Formulário atualizado com sucesso.');
    }

    /**
     * Delete the form.
     */
    public function destroy(Form $form): RedirectResponse
    {
        $this->authorize('delete', $form);

        $form->delete();

        return redirect()
            ->route('admin.forms.index')
            ->with('status', 'Formulário excluído com sucesso.');
    }
}
