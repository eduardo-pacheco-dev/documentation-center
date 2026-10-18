<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TodoRepeat;
use App\Exports\TodoTasksExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTodoTaskRequest;
use App\Http\Requests\Admin\UpdateTodoTaskRequest;
use App\Models\TodoList;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TodoTaskController extends Controller
{
    /**
     * The supported smart views.
     *
     * @var list<string>
     */
    private const VIEWS = ['my_day', 'important', 'planned', 'all', 'completed', 'today', 'week', 'list'];

    /**
     * The supported sort options.
     *
     * @var list<string>
     */
    private const SORTS = ['manual', 'due', 'important', 'title', 'created'];

    /**
     * Display the task workspace.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TodoTask::class);

        $user = $request->user();

        $lists = $user->todoLists()->ordered()->get();

        [$view, $activeList] = $this->resolveView($request, $user);

        $search = trim((string) $request->query('search', ''));

        $sort = $this->resolveSort($request);

        $tasks = $this->taskQuery($user, $view, $activeList, $search, $sort, $view === 'completed')->limit(150)->get();

        $completedTasks = $view === 'completed'
            ? collect()
            : $this->taskQuery($user, $view, $activeList, $search, $sort, true)->limit(50)->get();

        $base = TodoTask::query()->where('user_id', $user->getKey())->incomplete();

        $counts = [
            'my_day' => (clone $base)->whereDate('my_day_on', today())->count(),
            'important' => (clone $base)->where('is_important', true)->count(),
            'planned' => (clone $base)->whereNotNull('due_at')->count(),
            'today' => (clone $base)->whereDate('due_at', today())->count(),
            'week' => (clone $base)->whereBetween('due_at', [today(), today()->addDays(7)])->count(),
            'all' => (clone $base)->count(),
            'overdue' => (clone $base)->whereNotNull('due_at')->where('due_at', '<', today())->count(),
            'completed' => TodoTask::query()->where('user_id', $user->getKey())->completed()->count(),
        ];

        $listCounts = TodoTask::query()
            ->where('user_id', $user->getKey())
            ->incomplete()
            ->selectRaw('todo_list_id, count(*) as aggregate')
            ->groupBy('todo_list_id')
            ->pluck('aggregate', 'todo_list_id');

        return view('admin.tasks.index', [
            'lists' => $lists,
            'activeList' => $activeList,
            'view' => $view,
            'sort' => $sort,
            'search' => $search,
            'tasks' => $tasks,
            'completedTasks' => $completedTasks,
            'counts' => $counts,
            'listCounts' => $listCounts,
        ]);
    }

    /**
     * Download the tasks of the current view, honoring its filters, as a spreadsheet.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', TodoTask::class);

        $user = $request->user();

        [$view, $activeList] = $this->resolveView($request, $user);

        $search = trim((string) $request->query('search', ''));

        $sort = $this->resolveSort($request);

        $query = $this->taskQuery($user, $view, $activeList, $search, $sort, $view === 'completed' ? true : null);

        return Excel::download(
            new TodoTasksExport($query),
            'tarefas.xlsx',
        );
    }

    /**
     * Resolve the smart view and the active list of the request.
     *
     * @return array{0: string, 1: TodoList|null}
     */
    protected function resolveView(Request $request, User $user): array
    {
        $view = (string) $request->query('view', 'my_day');
        $activeList = null;

        if ($request->filled('list')) {
            $activeList = $user->todoLists()->find((int) $request->query('list'));

            abort_if($activeList === null, 404);

            $view = 'list';
        }

        if (! in_array($view, self::VIEWS, true) || $view === '') {
            $view = 'my_day';
        }

        if ($view === 'list' && $activeList === null) {
            $view = 'all';
        }

        return [$view, $activeList];
    }

    /**
     * Resolve the sort option of the request.
     */
    protected function resolveSort(Request $request): string
    {
        return in_array($request->query('sort'), self::SORTS, true)
            ? (string) $request->query('sort')
            : 'manual';
    }

    /**
     * Create a task inside one of the user's lists.
     */
    public function store(StoreTodoTaskRequest $request): RedirectResponse
    {
        $user = $request->user();

        $list = $user->todoLists()->findOrFail($request->validated('todo_list_id'));

        $list->tasks()->create([
            'user_id' => $user->getKey(),
            'title' => $request->validated('title'),
            'notes' => $request->validated('notes'),
            'due_at' => $request->validated('due_at'),
            'remind_at' => $request->validated('remind_at'),
            'repeat_type' => $request->validated('repeat_type') ?: TodoRepeat::None,
            'is_important' => $request->boolean('is_important'),
            'my_day_on' => $request->boolean('my_day') ? today() : null,
            'position' => (int) $list->tasks()->max('position') + 1,
        ]);

        return back()->with('status', 'Tarefa criada.');
    }

    /**
     * Update the task details.
     */
    public function update(UpdateTodoTaskRequest $request, TodoTask $todoTask): RedirectResponse
    {
        $listId = (int) $request->validated('todo_list_id');

        $attributes = [
            'title' => $request->validated('title'),
            'todo_list_id' => $listId,
            'notes' => $request->validated('notes'),
            'due_at' => $request->validated('due_at'),
            'remind_at' => $request->validated('remind_at'),
            'repeat_type' => $request->validated('repeat_type') ?: TodoRepeat::None,
            'is_important' => $request->boolean('is_important'),
            'my_day_on' => $request->boolean('my_day')
                ? ($todoTask->my_day_on?->toDateString() ?? today()->toDateString())
                : null,
        ];

        if ($listId !== (int) $todoTask->todo_list_id) {
            $target = $request->user()->todoLists()->findOrFail($listId);

            $attributes['position'] = (int) $target->tasks()->max('position') + 1;
        }

        $todoTask->update($attributes);

        return back()->with('status', 'Tarefa atualizada.');
    }

    /**
     * Complete or reopen the task.
     */
    public function toggle(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorize('update', $todoTask);

        if ($todoTask->isCompleted()) {
            $todoTask->update(['completed_at' => null]);

            return back()->with('status', 'Tarefa reaberta.');
        }

        $todoTask->update(['completed_at' => now()]);

        if ($todoTask->repeat_type !== TodoRepeat::None) {
            $this->createNextOccurrence($todoTask);
        }

        return back()->with('status', 'Tarefa concluída.');
    }

    /**
     * Toggle the important flag of the task.
     */
    public function toggleImportant(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorize('update', $todoTask);

        $todoTask->update(['is_important' => ! $todoTask->is_important]);

        return back()->with(
            'status',
            $todoTask->is_important ? 'Tarefa marcada como importante.' : 'Tarefa removida dos importantes.',
        );
    }

    /**
     * Add or remove the task from My Day.
     */
    public function toggleMyDay(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorize('update', $todoTask);

        $inMyDay = $todoTask->isMyDay();

        $todoTask->update(['my_day_on' => $inMyDay ? null : today()]);

        return back()->with(
            'status',
            $inMyDay ? 'Tarefa removida do Meu Dia.' : 'Tarefa adicionada ao Meu Dia.',
        );
    }

    /**
     * Move the task to another list or reorder it among its siblings.
     */
    public function move(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorize('update', $todoTask);

        $targetListId = $request->integer('todo_list_id');

        if ($targetListId !== 0 && $targetListId !== (int) $todoTask->todo_list_id) {
            $target = $request->user()->todoLists()->findOrFail($targetListId);

            $todoTask->update([
                'todo_list_id' => $target->getKey(),
                'position' => (int) $target->tasks()->max('position') + 1,
            ]);

            return back()->with('status', 'Tarefa movida para "'.$target->name.'".');
        }

        $direction = (string) $request->input('direction');

        if (in_array($direction, ['up', 'down'], true)) {
            $this->reorder($todoTask, $direction);
        }

        return back();
    }

    /**
     * Delete the task.
     */
    public function destroy(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorize('delete', $todoTask);

        $todoTask->delete();

        return back()->with('status', 'Tarefa excluída.');
    }

    /**
     * Build the query for the tasks visible in the current view.
     *
     * When `$completed` is null the completion state is not filtered.
     */
    protected function taskQuery(User $user, string $view, ?TodoList $activeList, string $search, string $sort, ?bool $completed = null): Builder
    {
        $query = TodoTask::query()
            ->where('user_id', $user->getKey())
            ->with(['todoList', 'steps']);

        if ($completed !== null) {
            $completed ? $query->completed() : $query->incomplete();
        }

        match ($view) {
            'list' => $query->where('todo_list_id', $activeList?->getKey()),
            'my_day' => $query->whereDate('my_day_on', today()),
            'important' => $query->where('is_important', true),
            'planned' => $query->whereNotNull('due_at'),
            'today' => $query->whereDate('due_at', today()),
            'week' => $query->whereBetween('due_at', [today(), today()->addDays(7)]),
            default => null,
        };

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('notes', 'like', '%'.$search.'%');
            });
        }

        match ($sort) {
            'due' => $query->orderByRaw('due_at is null')->orderBy('due_at')->orderBy('position')->orderBy('id'),
            'important' => $query->orderByDesc('is_important')->orderBy('position')->orderBy('id'),
            'title' => $query->orderBy('title'),
            'created' => $query->orderByDesc('created_at'),
            default => $query->orderBy('position')->orderBy('id'),
        };

        return $query;
    }

    /**
     * Reorder the task one position up or down among the incomplete tasks of its list.
     */
    protected function reorder(TodoTask $task, string $direction): void
    {
        $tasks = TodoTask::query()
            ->where('user_id', $task->user_id)
            ->where('todo_list_id', $task->todo_list_id)
            ->whereNull('completed_at')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->values();

        $index = $tasks->search(fn (TodoTask $candidate): bool => $candidate->is($task));

        if ($index === false) {
            return;
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        $neighbor = $tasks->get($swapIndex);

        if ($neighbor === null) {
            return;
        }

        $tasks->each(function (TodoTask $candidate, int $position): void {
            if ((int) $candidate->position !== $position) {
                $candidate->update(['position' => $position]);
            }
        });

        $task->update(['position' => $swapIndex]);
        $neighbor->update(['position' => $index]);
    }

    /**
     * Create the next occurrence of a repeating task.
     */
    protected function createNextOccurrence(TodoTask $task): TodoTask
    {
        $nextDue = $task->nextOccurrence() ?? today();

        $occurrence = $task->replicate(['completed_at', 'my_day_on', 'position']);

        $occurrence->due_at = $nextDue->toDateString();
        $occurrence->completed_at = null;
        $occurrence->my_day_on = null;
        $occurrence->remind_at = $task->remind_at !== null
            ? Carbon::parse($nextDue->toDateString().' '.$task->remind_at->format('H:i:s'))->toDateTimeString()
            : null;
        $occurrence->position = (int) $task->todoList()->max('position') + 1;
        $occurrence->save();

        foreach ($task->steps as $step) {
            $occurrence->steps()->create([
                'title' => $step->title,
                'position' => $step->position,
            ]);
        }

        return $occurrence;
    }
}
