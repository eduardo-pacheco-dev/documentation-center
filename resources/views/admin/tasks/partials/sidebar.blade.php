@php
    $smartViews = [
        ['key' => 'my_day', 'label' => 'Meu Dia', 'icon' => 'sun', 'count' => $counts['my_day']],
        ['key' => 'important', 'label' => 'Importante', 'icon' => 'star', 'count' => $counts['important']],
        ['key' => 'planned', 'label' => 'Planejado', 'icon' => 'calendar-days', 'count' => $counts['planned']],
        ['key' => 'today', 'label' => 'Hoje', 'icon' => 'clock', 'count' => $counts['today']],
        ['key' => 'week', 'label' => 'Próximos 7 dias', 'icon' => 'chart-bar', 'count' => $counts['week']],
        ['key' => 'all', 'label' => 'Todas as tarefas', 'icon' => 'clipboard-document', 'count' => $counts['all']],
        ['key' => 'completed', 'label' => 'Concluídas', 'icon' => 'check-circle', 'count' => $counts['completed']],
    ];

    $activeItem = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300';
    $idleItem = 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800/70';
@endphp

<aside class="space-y-4">
    <div class="rounded-2xl border border-gray-200/70 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <nav class="space-y-0.5">
            @foreach ($smartViews as $item)
                <a
                    href="{{ route('admin.tasks.index', ['view' => $item['key']]) }}"
                    class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium transition {{ $view === $item['key'] && $activeList === null ? $activeItem : $idleItem }}"
                >
                    <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0 {{ $item['key'] === 'my_day' ? 'text-amber-500' : ($item['key'] === 'important' ? 'text-amber-400' : 'text-gray-400') }}" />
                    <span class="flex-1 truncate">{{ $item['label'] }}</span>

                    @if (in_array($item['key'], ['today', 'planned', 'week'], true) && $counts['overdue'] > 0)
                        <span
                            class="rounded-full bg-red-100 px-1.5 py-0.5 text-[11px] font-semibold text-red-600 dark:bg-red-500/15 dark:text-red-400"
                            title="{{ $counts['overdue'] }} tarefa(s) atrasada(s)"
                        >
                            {{ $counts['overdue'] }}
                        </span>
                    @endif

                    @if ($item['count'] > 0)
                        <span class="text-xs font-medium text-gray-400">{{ $item['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <div class="rounded-2xl border border-gray-200/70 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between px-2 py-1">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Minhas listas</h2>
            <button
                type="button"
                data-modal-open="task-list-modal"
                class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-gray-800"
                aria-label="Nova lista"
                title="Nova lista"
            >
                <x-icon name="plus" class="h-4 w-4" />
            </button>
        </div>

        @if ($lists->isEmpty())
            <p class="px-2 py-3 text-xs text-gray-500 dark:text-gray-400">Nenhuma lista ainda. Crie a primeira para começar.</p>
        @else
            <nav class="mt-1 space-y-0.5">
                @foreach ($lists as $list)
                    <div x-data="{ open: false }" class="group relative">
                        <a
                            href="{{ route('admin.tasks.index', ['view' => 'list', 'list' => $list->getKey()]) }}"
                            class="flex items-center gap-2.5 rounded-xl px-3 py-2 pr-9 text-sm font-medium transition {{ $activeList !== null && $activeList->is($list) ? $activeItem : $idleItem }}"
                        >
                            <x-icon :name="$list->icon->value" class="h-4 w-4 shrink-0 {{ $list->color->textClasses() }}" />
                            <span class="flex-1 truncate">{{ $list->name }}</span>
                            @if ($listCounts->get($list->getKey(), 0) > 0)
                                <span class="text-xs text-gray-400">{{ $listCounts->get($list->getKey()) }}</span>
                            @endif
                        </a>

                        <div class="absolute right-1 top-1/2 -translate-y-1/2">
                            <button
                                type="button"
                                @click="open = !open"
                                class="rounded-lg p-1 text-gray-400 opacity-0 transition hover:bg-gray-100 hover:text-gray-600 focus:opacity-100 group-hover:opacity-100 dark:hover:bg-gray-800"
                                aria-label="Ações da lista"
                            >
                                <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                @click.outside="open = false"
                                class="absolute right-0 z-20 mt-1 w-40 rounded-xl border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            >
                                <button
                                    type="button"
                                    data-modal-open="task-list-modal"
                                    data-list='@json($listPayloadFor($list))'
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    <x-icon name="pencil-square" class="h-4 w-4" />
                                    Renomear
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('admin.todo-lists.destroy', $list) }}"
                                    data-confirm-delete="Excluir a lista e todas as suas tarefas?"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                                    >
                                        <x-icon name="trash" class="h-4 w-4" />
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </nav>
        @endif
    </div>
</aside>
