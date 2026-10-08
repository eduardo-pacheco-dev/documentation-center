<div
    x-data="{
        open: false,
        action: '',
        method: 'POST',
        updateUrl: $el.dataset.updateTemplate,
        form: {},
        blank () {
            return {
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
            this.action = this.updateUrl.replace('__TASK__', task.id);
            this.method = 'PUT';
            this.open = true;
        },
    }"
    @task-create.window="create()"
    @task-edit.window="edit($event.detail)"
    data-store-url="{{ route('admin.projects.tasks.store', $project) }}"
    data-update-template="{{ route('admin.projects.tasks.update', [$project, '__TASK__']) }}"
>
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
</div>
