<section id="assignments" class="mt-8">
    <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
        <x-icon name="users" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
        Alocações de recursos
    </h2>

    @if ($canPlan && $resources->isNotEmpty() && $tasks->isNotEmpty())
        <form method="POST" action="{{ route('admin.projects.assignments.store', $project) }}" class="mt-3 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @csrf

            <div>
                <label for="assign-task" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tarefa</label>
                <select name="task_id" id="assign-task" required class="mt-1 block w-56 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach ($tasks as $task)
                        <option value="{{ $task->getKey() }}" @selected(old('task_id') == $task->getKey())>{{ $task->wbs }} · {{ $task->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="assign-resource" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Recurso</label>
                <select name="resource_id" id="assign-resource" required class="mt-1 block w-56 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach ($resources as $resource)
                        <option value="{{ $resource->getKey() }}" @selected(old('resource_id') == $resource->getKey())>{{ $resource->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="assign-units" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Unidades (%)</label>
                <input type="number" name="units" id="assign-units" min="0" max="10000" step="1" value="{{ old('units', 100) }}" class="mt-1 block w-24 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Alocar
            </button>
        </form>
    @endif

    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Tarefa</th>
                    <th class="px-4 py-3 font-medium">Recurso</th>
                    <th class="px-4 py-3 font-medium">Unidades</th>
                    <th class="px-4 py-3 font-medium">Trabalho</th>
                    <th class="px-4 py-3 font-medium">Custo</th>
                    @if ($canPlan)
                        <th class="px-4 py-3 text-right font-medium">Ações</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($assignments as $assignment)
                    <tr>
                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $assignment->task?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $assignment->resource?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ number_format((float) $assignment->units, 0) }}%</td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                            {{ $assignment->work_minutes !== null ? number_format($assignment->work_minutes / 60, 1, ',', '.') . ' h' : '—' }}
                        </td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ number_format((float) $assignment->cost, 2, ',', '.') }}</td>
                        @if ($canPlan)
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.projects.assignments.destroy', [$project, $assignment]) }}" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400" aria-label="Remover alocação">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-500 dark:text-gray-400" colspan="6">Nenhum recurso alocado ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
