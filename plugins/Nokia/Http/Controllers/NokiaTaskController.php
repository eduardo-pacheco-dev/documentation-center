<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
     * Move the given task one position up or down.
     */
    public function move(Request $request, NokiaTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', 'string', Rule::in(['up', 'down'])],
        ]);

        $tasks = NokiaTask::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->values();

        $index = $tasks->search(fn (NokiaTask $item): bool => $item->is($task));

        abort_if($index === false, 404);

        $target = $validated['direction'] === 'up' ? $index - 1 : $index + 1;

        if (! $tasks->has($target)) {
            return redirect()->route('admin.nokia.settings.index');
        }

        $ordered = $tasks->all();
        [$ordered[$index], $ordered[$target]] = [$ordered[$target], $ordered[$index]];

        DB::transaction(function () use ($ordered): void {
            foreach ($ordered as $position => $item) {
                $item->update(['position' => $position + 1]);
            }
        });

        return redirect()
            ->route('admin.nokia.settings.index')
            ->with('status', 'Ordem das tarefas atualizada.');
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
