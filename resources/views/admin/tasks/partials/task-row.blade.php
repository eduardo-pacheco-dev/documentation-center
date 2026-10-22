@php
    $isCompleted = $task->isCompleted();

    $dueLabel = match (true) {
        $task->due_at === null => null,
        $task->due_at->isToday() => 'Hoje',
        $task->due_at->isTomorrow() => 'Amanhã',
        $task->due_at->isYesterday() => 'Ontem',
        default => $task->due_at->format('d/m/Y'),
    };

    $stepsDone = $task->steps->filter(fn ($step): bool => $step->isCompleted())->count();
    $stepsTotal = $task->steps->count();

    $payload = [
        'title' => $task->title,
        'list_id' => $task->todo_list_id,
        'due_at' => $task->due_at?->toDateString(),
        'remind_at' => $task->remind_at?->format('Y-m-d\TH:i'),
        'repeat_type' => $task->repeat_type->value,
        'is_important' => (bool) $task->is_important,
        'my_day' => $task->isMyDay(),
        'notes' => $task->notes,
        'update_url' => route('admin.tasks.update', $task),
        'delete_url' => route('admin.tasks.destroy', $task),
        'step_store_url' => route('admin.tasks.steps.store', $task),
        'steps' => $task->steps->map(fn ($step): array => [
            'title' => $step->title,
            'completed' => $step->isCompleted(),
            'update_url' => route('admin.tasks.steps.update', [$task, $step]),
            'destroy_url' => route('admin.tasks.steps.destroy', [$task, $step]),
        ])->values()->all(),
    ];
@endphp

<li
    data-task='@json($payload)'
    class="group flex items-start gap-3 px-5 py-4 transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]"
>
    <form method="POST" action="{{ route('admin.tasks.toggle', $task) }}" class="mt-0.5 shrink-0">
        @csrf
        @method('PATCH')
        <button
            type="submit"
            class="flex h-5 w-5 items-center justify-center rounded-full border-2 transition {{ $isCompleted ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 text-transparent hover:border-indigo-500 hover:text-indigo-500 dark:border-gray-600' }}"
            aria-label="{{ $isCompleted ? 'Reabrir tarefa' : 'Concluir tarefa' }}"
            title="{{ $isCompleted ? 'Reabrir' : 'Concluir' }}"
        >
            <x-icon name="check" class="h-3 w-3" />
        </button>
    </form>

    <div class="min-w-0 flex-1">
        <div class="flex items-start gap-2">
            <button
                type="button"
                data-modal-open="task-detail-modal"
                class="min-w-0 flex-1 text-left text-[15px] font-medium {{ $isCompleted ? 'text-gray-400 line-through dark:text-gray-500' : 'text-gray-800 dark:text-gray-100' }}"
            >
                {{ $task->title }}
            </button>
        </div>

        @if (filled($task->notes))
            <p class="mt-0.5 line-clamp-1 text-xs text-gray-400 dark:text-gray-500">{{ $task->notes }}</p>
        @endif

        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
            @if ($dueLabel !== null)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 {{ $task->isOverdue() ? 'bg-red-50 font-medium text-red-600 dark:bg-red-500/10 dark:text-red-400' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400' }}">
                    <x-icon name="calendar-days" class="h-3.5 w-3.5" />
                    {{ $dueLabel }}
                </span>
            @endif

            @if ($task->remind_at !== null)
                <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                    <x-icon name="bell" class="h-3.5 w-3.5" />
                    {{ $task->remind_at->format('d/m H:i') }}
                </span>
            @endif

            @if ($task->repeat_type->value !== 'none')
                <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                    <x-icon name="arrow-path" class="h-3.5 w-3.5" />
                    {{ $task->repeat_type->label() }}
                </span>
            @endif

            @if ($stepsTotal > 0)
                <span class="inline-flex items-center gap-1 {{ $stepsDone === $stepsTotal ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                    {{ $stepsDone }}/{{ $stepsTotal }}
                </span>
            @endif

            @if (($activeList ?? null) === null && $task->todoList !== null)
                <span class="inline-flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                    <span class="h-2 w-2 rounded-full {{ $task->todoList->color->dotClasses() }}"></span>
                    {{ $task->todoList->name }}
                </span>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-0.5 opacity-100 transition focus-within:opacity-100 lg:opacity-0 lg:group-hover:opacity-100">
        <form method="POST" action="{{ route('admin.tasks.my-day', $task) }}">
            @csrf
            @method('PATCH')
            <button
                type="submit"
                class="rounded-lg p-1.5 transition {{ $task->isMyDay() ? 'text-amber-500' : 'text-gray-400 hover:text-amber-500' }}"
                aria-label="Meu Dia"
                title="Meu Dia"
            >
                <x-icon name="sun" class="h-4 w-4" />
            </button>
        </form>

        <form method="POST" action="{{ route('admin.tasks.important', $task) }}">
            @csrf
            @method('PATCH')
            <button
                type="submit"
                class="rounded-lg p-1.5 transition {{ $task->is_important ? 'text-amber-500' : 'text-gray-400 hover:text-amber-500' }}"
                aria-label="Importante"
                title="Importante"
            >
                <x-icon name="star" class="h-4 w-4" />
            </button>
        </form>

        <div x-data="{ open: false }" class="relative">
            <button
                type="button"
                @click="open = !open"
                class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200"
                aria-label="Mais ações"
            >
                <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
            </button>

            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                class="absolute right-0 z-20 mt-1 w-44 rounded-xl border border-gray-200 bg-white py-1 dark:border-white/10 dark:bg-[#1c1c1b]"
            >
                @if (($sort ?? 'manual') === 'manual' && ! $isCompleted)
                    <form method="POST" action="{{ route('admin.tasks.move', $task) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="direction" value="up">
                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                            <x-icon name="chevron-up" class="h-4 w-4" />
                            Mover para cima
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.tasks.move', $task) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="direction" value="down">
                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                            <x-icon name="chevron-down" class="h-4 w-4" />
                            Mover para baixo
                        </button>
                    </form>
                @endif

                <button
                    type="button"
                    data-modal-open="task-detail-modal"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    Detalhes
                </button>

                <form
                    method="POST"
                    action="{{ route('admin.tasks.destroy', $task) }}"
                    data-confirm-delete="Remover esta tarefa?"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                        <x-icon name="trash" class="h-4 w-4" />
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</li>
