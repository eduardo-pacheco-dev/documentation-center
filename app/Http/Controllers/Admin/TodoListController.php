<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TodoColor;
use App\Enums\TodoIcon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTodoListRequest;
use App\Http\Requests\Admin\UpdateTodoListRequest;
use App\Models\TodoList;
use Illuminate\Http\RedirectResponse;

class TodoListController extends Controller
{
    /**
     * Create a task list owned by the authenticated user.
     */
    public function store(StoreTodoListRequest $request): RedirectResponse
    {
        $user = $request->user();

        $list = $user->todoLists()->create([
            'name' => $request->validated('name'),
            'color' => $request->validated('color') ?: TodoColor::Indigo->value,
            'icon' => $request->validated('icon') ?: TodoIcon::CheckCircle->value,
            'position' => (int) $user->todoLists()->max('position') + 1,
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
