@php($minutesPerDay = max(1, $project->calendars()->orderByDesc('is_default')->orderBy('id')->value('minutes_per_day') ?? 480))

<section id="tasks" class="mt-8 hidden" data-project-panel>
    <div
        x-data="{
            open: false,
            action: '',
            method: 'POST',
            updateTemplate: $el.dataset.updateTemplate,
            form: {},
            blank () {
                return {
                    id: '',
                    name: '',
                    parent_id: '',
                    task_type: '',
                    scheduling_mode: '',
                    is_milestone: false,
                    start_date: '',
                    duration_days: '',
                    priority: 500,
                    percent_complete: 0,
                    budget_cost: '',
                    notes: '',
                };
            },
            create () {
                this.form = this.blank();
                this.action = $el.dataset.storeUrl;
                this.method = 'POST';
                this.open = true;
            },
            edit (task) {
                this.form = Object.assign(this.blank(), {
                    id: task.id,
                    name: task.name,
                    parent_id: task.parent_id ?? '',
                    task_type: task.task_type ?? '',
                    scheduling_mode: task.scheduling_mode ?? '',
                    is_milestone: !!task.is_milestone,
                    start_date: task.constraint_date ? task.constraint_date.slice(0, 10) : (task.start_at ? task.start_at.slice(0, 10) : ''),
                    duration_days: task.duration_days ?? '',
                    priority: task.priority ?? 500,
                    percent_complete: task.percent_complete ?? 0,
                    budget_cost: task.budget_cost ?? '',
                    notes: task.notes ?? '',
                });
                this.action = this.updateTemplate.replace('__TASK__', task.id);
                this.method = 'PUT';
                this.open = true;
            },
        }"
        data-store-url="{{ route('admin.projects.tasks.store', $project) }}"
        data-update-template="{{ route('admin.projects.tasks.update', [$project, '__TASK__']) }}"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            <x-icon name="rectangle-stack" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Estrutura de trabalho (WBS)
        </h2>

        @if ($canPlan)
            <button
                type="button"
                @click="create()"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-1.5 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Nova tarefa
            </button>
        @endif
    </div>

    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3 font-medium">WBS</th>
                    <th class="px-4 py-3 font-medium">Nome</th>
                    <th class="px-4 py-3 font-medium">Duração</th>
                    <th class="px-4 py-3 font-medium">Início</th>
                    <th class="px-4 py-3 font-medium">Término</th>
                    <th class="px-4 py-3 font-medium">Folga</th>
                    <th class="px-4 py-3 font-medium">%</th>
                    <th class="px-4 py-3 font-medium">Recursos</th>
                    @if ($canPlan)
                        <th class="px-4 py-3 text-right font-medium">Ações</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($tasks as $task)
                    <tr @class(['bg-amber-50/40 dark:bg-amber-500/5' => $task->critical])>
                        <td class="whitespace-nowrap px-4 py-2 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $task->wbs }}</td>
                        <td class="px-4 py-2">
                            <span
                                class="inline-flex items-center gap-1.5 text-gray-900 dark:text-gray-100"
                                style="padding-left: {{ max(0, ($task->outline_level - 1) * 16) }}px"
                            >
                                @if ($task->is_milestone)
                                    <span class="inline-block h-2.5 w-2.5 rotate-45 bg-indigo-500" title="Marco"></span>
                                @elseif ($task->isSummary())
                                    <x-icon name="rectangle-stack" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                                @endif
                                {{ $task->name }}
                                @if ($task->critical)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-700 dark:bg-red-500/15 dark:text-red-300">Crítica</span>
                                @endif
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-500 dark:text-gray-400">
                            @if ($task->is_milestone)
                                —
                            @elseif ($task->duration_minutes !== null)
                                {{ number_format($task->duration_minutes / $minutesPerDay, 1, ',', '.') }} d
                            @else
                                —
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-500 dark:text-gray-400">{{ $task->start_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-500 dark:text-gray-400">{{ $task->finish_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-500 dark:text-gray-400">
                            {{ number_format(((int) $task->total_slack_minutes) / $minutesPerDay, 1, ',', '.') }} d
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-500 dark:text-gray-400">{{ number_format((float) $task->percent_complete, 0) }}%</td>
                        <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">{{ $task->resources->pluck('name')->implode(', ') ?: '—' }}</td>
                        @if ($canPlan)
                            <td class="whitespace-nowrap px-4 py-2 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @php($taskEditPayload = fn ($task) => $task->only([
    'id', 'name', 'parent_id', 'task_type', 'scheduling_mode', 'is_milestone',
    'constraint_type', 'priority', 'percent_complete', 'budget_cost', 'notes', 'start_at',
]) + ['duration_days' => $task->duration_minutes !== null ? round($task->duration_minutes / $minutesPerDay, 2) : null])

<button
                                        type="button"
                                        @click="edit(@js($taskEditPayload($task)))"
                                        class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                                        aria-label="Editar tarefa"
                                    >
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </button>

                                    <form method="POST" action="{{ route('admin.projects.tasks.destroy', [$project, $task]) }}" data-confirm-delete>
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400"
                                            aria-label="Excluir tarefa"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-500 dark:text-gray-400" colspan="9">
                            <span class="inline-flex items-center gap-2">
                                <x-icon name="rectangle-stack" class="h-4 w-4" />
                                Nenhuma tarefa no plano ainda.
                            </span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($canPlan)
        <div
            x-show="open"
            x-transition
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4"
            role="dialog"
            aria-modal="true"
            @click.self="open = false"
            @keydown.escape.window="open = false"
        >
            <form method="POST" :action="action" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                @csrf
                <input type="hidden" name="_method" :value="method">

                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="form.id ? 'Editar tarefa' : 'Nova tarefa'"></h3>
                    <button type="button" @click="open = false" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Fechar">
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="task-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
                        <input type="text" name="name" id="task-name" x-model="form.name" required class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="task-parent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tarefa pai</label>
                        <select name="parent_id" id="task-parent" x-model="form.parent_id" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                            <option value="">— Raiz —</option>
                            @foreach ($tasks as $candidate)
                                <option value="{{ $candidate->getKey() }}" x-bind:selected="String(form.parent_id) === '{{ $candidate->getKey() }}'">{{ $candidate->wbs }} · {{ $candidate->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="task-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de duração</label>
                        <select name="task_type" id="task-type" x-model="form.task_type" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                            <option value="">Padrão</option>
                            <option value="fixed_duration">Duração fixa</option>
                            <option value="fixed_work">Trabalho fixo</option>
                            <option value="fixed_units">Unidades fixas</option>
                        </select>
                    </div>

                    <div>
                        <label for="task-start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Início não antes de</label>
                        <input type="date" name="start_date" id="task-start" x-model="form.start_date" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="task-duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duração (dias úteis)</label>
                        <input type="number" name="duration_days" id="task-duration" min="0" step="0.25" x-model="form.duration_days" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="task-priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridade</label>
                        <input type="number" name="priority" id="task-priority" min="0" max="1000" x-model="form.priority" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="task-percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Progresso (%)</label>
                        <input type="number" name="percent_complete" id="task-percent" min="0" max="100" step="1" x-model="form.percent_complete" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="task-budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Orçamento ({{ $project->currency }})</label>
                        <input type="number" name="budget_cost" id="task-budget" min="0" step="0.01" x-model="form.budget_cost" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center gap-2 sm:col-span-2">
                        <input type="checkbox" name="is_milestone" id="task-milestone" value="1" x-model="form.is_milestone" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="task-milestone" class="text-sm text-gray-700 dark:text-gray-300">Marco (entrega sem duração)</label>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="task-notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
                        <textarea name="notes" id="task-notes" rows="3" x-model="form.notes" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200">
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    @endif
    </div>
</section>
