<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\Nokia\Http\Requests\StoreNokiaTaskRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaTaskRequest;
use Plugins\Nokia\Models\NokiaTask;

class NokiaTaskController extends Controller
{
    /**
     * Store a newly created task.
     */
    public function store(StoreNokiaTaskRequest $request): RedirectResponse
    {
        NokiaTask::query()->create([
            'name' => $request->validated('name'),
            'position' => (int) NokiaTask::query()->max('position') + 1,
        ]);

        return redirect()
            ->route('admin.nokia.settings.index')
            ->with('status', 'Tarefa cadastrada com sucesso.');
    }

    /**
     * Update the given task.
     */
    public function update(UpdateNokiaTaskRequest $request, NokiaTask $task): RedirectResponse
    {
        $task->update([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('admin.nokia.settings.index')
            ->with('status', 'Tarefa atualizada com sucesso.');
    }

    /**
     * Remove the given task.
     */
    public function destroy(NokiaTask $task): RedirectResponse
    {
        $task->delete();

        return redirect()
            ->route('admin.nokia.settings.index')
            ->with('status', 'Tarefa excluída com sucesso.');
    }
}
