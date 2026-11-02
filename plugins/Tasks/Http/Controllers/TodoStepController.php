<?php

namespace Plugins\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Tasks\Http\Requests\StoreTodoStepRequest;
use Plugins\Tasks\Http\Requests\UpdateTodoStepRequest;
use Plugins\Tasks\Models\TodoStep;
use Plugins\Tasks\Models\TodoTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TodoStepController extends Controller
{
    /**
     * Add a step to the task checklist.
     */
    public function store(StoreTodoStepRequest $request, TodoTask $todoTask): RedirectResponse
    {
        $todoTask->steps()->create([
            'title' => $request->validated('title'),
            'position' => (int) $todoTask->steps()->max('position') + 1,
        ]);

        return back()->with('status', 'Etapa adicionada.');
    }

    /**
     * Update the step title or completion state.
     */
    public function update(UpdateTodoStepRequest $request, TodoTask $todoTask, TodoStep $step): RedirectResponse
    {
        $attributes = [];

        if ($request->has('title')) {
            $attributes['title'] = $request->validated('title');
        }

        if ($request->has('completed')) {
            $attributes['completed_at'] = $request->boolean('completed') ? now() : null;
        }

        if ($attributes !== []) {
            $step->update($attributes);
        }

        return back();
    }

    /**
     * Remove the step from the task checklist.
     */
    public function destroy(Request $request, TodoTask $todoTask, TodoStep $step): RedirectResponse
    {
        $this->authorize('update', $todoTask);

        abort_unless($step->todo_task_id === $todoTask->getKey(), 404);

        $step->delete();

        return back()->with('status', 'Etapa removida.');
    }
}
