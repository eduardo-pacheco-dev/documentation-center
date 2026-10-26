<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFormRequest;
use App\Http\Requests\Admin\UpdateFormRequest;
use App\Http\Requests\Admin\UpdateFormStepsRequest;
use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\User;
use App\Services\Forms\FormModuleSettings;
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

        $settings = FormModuleSettings::for($request->user());

        $search = (string) $request->query('search', '');

        $status = (string) $request->query('status', '');

        if (in_array($request->query('sort'), ['name', 'code', 'type', 'status', 'created_at', 'updated_at'], true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'updated_at' ? 'desc' : 'asc');
        } else {
            $sort = 'updated_at';
            $direction = 'desc';
        }

        $perPage = in_array($request->integer('per_page'), FormModuleSettings::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : $settings->perPage();

        $forms = Form::query()
            ->ownedBy($request->user())
            ->with('project:id,code,name')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), array_keys(FormModuleSettings::VIEWS), true)
            ? $request->query('view')
            : $settings->defaultView();

        $templates = FormTemplate::query()->orderBy('name')->get(['id', 'name', 'type']);

        return view('admin.forms.index', [
            'forms' => $forms,
            'projects' => $request->user()->projects()->orderBy('name')->get(['id', 'code', 'name']),
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'perPage' => $perPage,
            'types' => $this->mergeOptions(
                $settings->types(),
                [...$this->existingValues($request->user(), 'type'), ...$templates->pluck('type')->all()],
            ),
            'statuses' => $this->mergeOptions($settings->statuses(), $this->existingValues($request->user(), 'status')),
            'templates' => $templates,
            'defaultTemplateId' => $settings->defaultTemplateId(),
        ]);
    }

    /**
     * Show the form to create a form.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Form::class);

        $settings = FormModuleSettings::for($request->user());

        $templates = FormTemplate::query()->orderBy('name')->get(['id', 'name', 'type']);

        return view('admin.forms.create', [
            'projects' => $request->user()->projects()->orderBy('name')->get(['id', 'code', 'name']),
            'types' => $this->mergeOptions($settings->types(), $templates->pluck('type')->all()),
            'statuses' => $settings->statuses(),
            'templates' => $templates,
            'defaultTemplateId' => $settings->defaultTemplateId(),
        ]);
    }

    /**
     * Create a form owned by the authenticated user.
     */
    public function store(StoreFormRequest $request): RedirectResponse
    {
        $attributes = $request->validated();

        $template = isset($attributes['template_id'])
            ? FormTemplate::query()->find($attributes['template_id'])
            : null;

        $steps = $request->filled('steps_json')
            ? json_decode($request->input('steps_json'), true)
            : ($template?->steps ?? []);

        $defaultType = FormModuleSettings::for($request->user())->types()[0] ?? FormModuleSettings::DEFAULT_TYPES[0];

        $form = $request->user()->forms()->create([
            'project_id' => $attributes['project_id'] ?? null,
            'name' => $attributes['name'],
            'type' => $template?->type ?: (($attributes['type'] ?? null) ?: $defaultType),
            'status' => ($attributes['status'] ?? null) ?: 'Pendente',
            'allows_multiple_responses' => (bool) ($attributes['allows_multiple_responses'] ?? false),
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

        $responses = $form->responses()
            ->with('user:id,name')
            ->withCount('evaluations')
            ->latest('submitted_at')
            ->latest('id')
            ->get();

        $requestedResponse = $request->integer('response');
        $selectedResponse = $requestedResponse > 0
            ? $responses->firstWhere('id', $requestedResponse)
            : $responses->first();

        abort_if($requestedResponse > 0 && $selectedResponse === null, 404);

        return view('admin.forms.show', [
            'form' => $form,
            'steps' => $selectedResponse?->steps ?? ($responses->isEmpty() ? ($form->steps ?? []) : []),
            'responses' => $responses,
            'selectedResponse' => $selectedResponse,
        ]);
    }

    /**
     * Show the form details and allow editing them.
     */
    public function edit(Request $request, Form $form): View
    {
        $this->authorize('update', $form);

        $form->load('project:id,code,name', 'user:id,name');

        $settings = FormModuleSettings::for($request->user());

        return view('admin.forms.edit', [
            'form' => $form,
            'projects' => $request->user()->projects()->orderBy('name')->get(['id', 'code', 'name']),
            'types' => $this->mergeOptions($settings->types(), [$form->type]),
            'statuses' => $this->mergeOptions($settings->statuses(), [$form->status]),
            'steps' => $form->steps ?? [],
        ]);
    }

    /**
     * Merge the configured options with values already stored on forms.
     *
     * @param  list<string>  $configured
     * @param  array<int, string|null>  $existing
     * @return list<string>
     */
    private function mergeOptions(array $configured, array $existing): array
    {
        return collect($configured)
            ->merge($existing)
            ->filter(fn (mixed $value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => trim($value))
            ->unique(fn (string $value) => mb_strtolower($value))
            ->values()
            ->all();
    }

    /**
     * Get the values already stored on the user's forms for the given column.
     *
     * @return list<string>
     */
    private function existingValues(User $user, string $column): array
    {
        return Form::query()
            ->ownedBy($user)
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    /**
     * Update the form details.
     */
    public function update(UpdateFormRequest $request, Form $form): RedirectResponse
    {
        $attributes = $request->validated();

        $form->update([
            'project_id' => $attributes['project_id'] ?? null,
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'status' => $attributes['status'],
            'allows_multiple_responses' => (bool) ($attributes['allows_multiple_responses'] ?? $form->allows_multiple_responses),
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
