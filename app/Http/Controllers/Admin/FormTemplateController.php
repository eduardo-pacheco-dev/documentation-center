<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFormTemplateRequest;
use App\Http\Requests\Admin\UpdateFormTemplateRequest;
use App\Http\Requests\Admin\UpdateFormTemplateStepsRequest;
use App\Models\Form;
use App\Models\FormTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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

        $types = FormTemplate::query()
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->all();

        $type = in_array($request->query('type'), $types, true)
            ? $request->query('type')
            : null;

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
            ->when($type !== null, fn (Builder $query) => $query->where('type', $type))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        $stats = [
            'total' => FormTemplate::query()->count(),
            'types' => count($types),
            'questions' => FormTemplate::query()
                ->pluck('steps')
                ->sum(fn ($steps) => collect($steps)->sum(fn ($step) => count($step['fields'] ?? []))),
            'contributors' => FormTemplate::query()->distinct()->count('user_id'),
        ];

        return view('admin.form-templates.index', [
            'templates' => $templates,
            'search' => $search,
            'type' => $type,
            'types' => $types,
            'view' => $view,
            'perPage' => $perPage,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form to create a template from scratch.
     */
    public function create(): View
    {
        $this->authorize('create', FormTemplate::class);

        return view('admin.form-templates.create', [
            'types' => $this->templateTypes(),
        ]);
    }

    /**
     * Create a shared template, either from scratch or from one of the user's forms.
     */
    public function store(StoreFormTemplateRequest $request): RedirectResponse
    {
        $form = null;

        if ($request->filled('form_id')) {
            $form = Form::query()
                ->where('user_id', $request->user()->getKey())
                ->findOrFail($request->integer('form_id'));
        }

        FormTemplate::create([
            'user_id' => $request->user()->getKey(),
            'name' => $request->string('name')->toString(),
            'type' => $form?->type ?? ($request->string('type')->toString() ?: null),
            'steps' => $form?->steps ?? $request->input('steps', []),
        ]);

        return redirect()
            ->route('admin.form-templates.index')
            ->with('status', 'Template criado com sucesso.');
    }

    /**
     * The template types offered when creating or editing a template.
     *
     * @return list<string>
     */
    private function templateTypes(): array
    {
        return ['Registro Fotográfico', 'Checklist', 'Vistoria', 'Inspeção', 'Laudo Técnico'];
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
            'types' => $this->templateTypes(),
        ]);
    }

    /**
     * Update the template details.
     */
    public function update(UpdateFormTemplateRequest $request, FormTemplate $formTemplate): RedirectResponse
    {
        $attributes = $request->validated();

        $formTemplate->update([
            'name' => $attributes['name'],
            'type' => $attributes['type'] ?? null,
        ]);

        return back()->with('status', 'Template atualizado com sucesso.');
    }

    /**
     * Update the template structure (sections and questions) from the builder.
     */
    public function updateSteps(UpdateFormTemplateStepsRequest $request, FormTemplate $formTemplate): RedirectResponse|JsonResponse
    {
        $formTemplate->update(['steps' => $request->input('steps', [])]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Estrutura atualizada com sucesso.',
                'saved_at' => now()->toIso8601String(),
            ]);
        }

        return redirect()
            ->route('admin.form-templates.edit', $formTemplate)
            ->with('status', 'Estrutura atualizada com sucesso.');
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
