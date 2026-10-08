@php($minutesPerDay = max(1, $project->calendars()->orderByDesc('is_default')->orderBy('id')->value('minutes_per_day') ?? 480))

<section id="tasks" class="mt-8 hidden" data-project-panel>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            <x-icon name="rectangle-stack" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Estrutura de trabalho (WBS)
        </h2>

        @if ($canPlan)
            <button
                type="button"
                @click="$dispatch('task-create')"
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
                                        @click="$dispatch('task-edit', @js($taskEditPayload($task)))"
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
</section>
