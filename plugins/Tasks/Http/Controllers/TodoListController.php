<?php

namespace Plugins\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\Tasks\Enums\TodoColor;
use Plugins\Tasks\Enums\TodoIcon;
use Plugins\Tasks\Http\Requests\StoreTodoListRequest;
use Plugins\Tasks\Http\Requests\UpdateTodoListRequest;
use Plugins\Tasks\Models\TodoList;

class TodoListController extends Controller
{
    /**
     * Create a task list owned by the authenticated user.
     */
    public function store(StoreTodoListRequest $request): RedirectResponse
    {
        $user = $request->user();

        $lists = TodoList::query()->ownedBy($user);

        $list = $lists->create([
            'user_id' => $user->getKey(),
            'name' => $request->validated('name'),
            'color' => $request->validated('color') ?: TodoColor::Indigo->value,
            'icon' => $request->validated('icon') ?: TodoIcon::CheckCircle->value,
            'position' => (int) (clone $lists)->max('position') + 1,
        ]);

        return redirect()
            ->route('admin.tasks.index', ['view' => 'list', 'list' => $list->getKey()])
            ->with('status', 'Lista criada.');
    }

    /**
     * Update the task list details.
     */
    public function update(UpdateTodoListRequest $request, TodoList $todoList): RedirectResponse
    {
        $todoList->update($request->validated());

        return redirect()
            ->route('admin.tasks.index', ['view' => 'list', 'list' => $todoList->getKey()])
            ->with('status', 'Lista atualizada.');
    }

    /**
     * Delete the task list together with its tasks.
     */
    public function destroy(TodoList $todoList): RedirectResponse
    {
        $this->authorize('delete', $todoList);

        $todoList->tasks()->delete();
        $todoList->delete();

        return redirect()
            ->route('admin.tasks.index', ['view' => 'my_day'])
            ->with('status', 'Lista excluída.');
    }
}
