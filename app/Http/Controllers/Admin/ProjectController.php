<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CalendarType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Calendar;
use App\Models\Project;
use App\Services\EarnedValue\EarnedValueService;
use App\Services\Scheduling\WbsCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly EarnedValueService $earnedValue) {}

    /**
     * List the projects the authenticated user can open.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $search = (string) $request->query('search', '');

        $sortableColumns = ['name', 'status', 'start_date', 'finish_date', 'percent_complete', 'tasks_count', 'budget', 'updated_at'];

        if (in_array($request->query('sort'), $sortableColumns, true)) {
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

        $projects = Project::query()
            ->accessibleBy($request->user())
            ->withCount('tasks')
            ->with('user:id,name')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.projects.index', [
            'projects' => $projects,
            'search' => $search,
            'view' => $view,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form to create a project.
     */
    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('admin.projects.create');
    }

    /**
     * Create a project owned by the authenticated user.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $request->user()->projects()->create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? 'active',
            'start_date' => $request->validated('start_date'),
        ]);

        Calendar::createDefault($project, CalendarType::Standard, 'Calendário padrão');

        (new WbsCalculator)->recalculate($project);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Projeto criado com sucesso.');
    }

    /**
     * Show a project with its plan.
     */
    public function show(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()->with('children:id,parent_id', 'resources:id,name')->orderBy('wbs')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'role' => $project->roleFor($request->user()),
            'resources' => $project->resources()->orderBy('name')->get(),
            'members' => $project->members()->with('user:id,name,email')->orderBy('id')->get(),
            'baselines' => $project->baselines()->orderByDesc('saved_at')->get(['id', 'name', 'saved_at']),
            'dependencies' => $project->dependencies()->with('predecessor:id,name', 'successor:id,name')->orderBy('id')->get(),
            'assignments' => $project->assignments()->with('task:id,name,wbs', 'resource:id,name')->orderBy('id')->get(),
            'evm' => $this->earnedValue->report($project),
            'canPlan' => $request->user()->can('plan', $project),
        ]);
    }

    /**
     * Show the form to edit the project details.
     */
    public function edit(Request $request, Project $project): View
    {
        $this->authorize('plan', $project);

        return view('admin.projects.edit', ['project' => $project]);
    }

    /**
     * Update the project details.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return back()->with('status', 'Projeto atualizado com sucesso.');
    }

    /**
     * Delete the project and everything that belongs to it.
     */
    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('status', 'Projeto excluído com sucesso.');
    }
}
