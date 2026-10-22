@php
    $viewMeta = match ($view) {
        'my_day' => ['title' => 'Meu Dia', 'icon' => 'sun', 'description' => 'As tarefas que você planejou para hoje.'],
        'important' => ['title' => 'Importante', 'icon' => 'star', 'description' => 'Tudo o que você marcou como importante.'],
        'planned' => ['title' => 'Planejado', 'icon' => 'calendar-days', 'description' => 'Tarefas com data de vencimento.'],
        'today' => ['title' => 'Hoje', 'icon' => 'clock', 'description' => 'Tarefas que vencem hoje.'],
        'week' => ['title' => 'Próximos 7 dias', 'icon' => 'chart-bar', 'description' => 'Tarefas que vencem nos próximos sete dias.'],
        'completed' => ['title' => 'Concluídas', 'icon' => 'check-circle', 'description' => 'Tudo o que você já concluiu.'],
        'list' => ['title' => $activeList->name, 'icon' => $activeList->icon->value, 'description' => 'Tarefas da lista '.$activeList->name.'.'],
        default => ['title' => 'Todas as tarefas', 'icon' => 'clipboard-document', 'description' => 'Todas as suas tarefas em aberto.'],
    };

    $emptyMessages = [
        'my_day' => 'Nada planejado para hoje.',
        'important' => 'Nenhuma tarefa importante.',
        'planned' => 'Nenhuma tarefa com data marcada.',
        'today' => 'Nada vence hoje.',
        'week' => 'Nada vence nos próximos 7 dias.',
        'completed' => 'Nenhuma tarefa concluída ainda.',
        'list' => 'Esta lista está vazia.',
        'all' => 'Nenhuma tarefa em aberto.',
    ];

    $emptyMessage = $emptyMessages[$view] ?? 'Nenhuma tarefa por aqui.';

    $listPayloadFor = fn ($list): array => [
        'name' => $list->name,
        'color' => $list->color->value,
        'icon' => $list->icon->value,
        'update_url' => route('admin.todo-lists.update', $list),
    ];

    $scopeHidden = $activeList !== null
        ? ['list' => $activeList->getKey()]
        : ['view' => $view];

    $sortOptions = [
        'manual' => 'Ordem manual',
        'due' => 'Data de vencimento',
        'important' => 'Importância',
        'title' => 'Nome',
        'created' => 'Data de criação',
    ];

    $exportQuery = array_filter([
        'view' => $view,
        'list' => $activeList?->getKey(),
        'search' => $search,
        'sort' => $sort === 'manual' ? null : $sort,
    ]);

    $panel = 'rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]';
    $primaryButton = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2 dark:focus:ring-offset-[#171716]';
    $secondaryButton = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300';
@endphp

<x-layouts.admin title="Tarefas">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex items-start gap-4">
            <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 sm:inline-flex dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="check-circle" class="h-6 w-6" />
            </span>

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">Produtividade</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">Tarefas</h1>
                <p class="mt-1.5 max-w-xl text-sm text-gray-500 dark:text-gray-400">Organize listas, planeje o dia e acompanhe suas etapas.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.tasks.export', $exportQuery) }}"
                data-export-link
                data-export-filename="tarefas.xlsx"
                class="{{ $secondaryButton }}"
            >
                <x-icon name="arrow-down-tray" class="h-4 w-4" />
                Exportar Excel
            </a>

            <button
                type="button"
                data-modal-open="task-list-modal"
                class="{{ $secondaryButton }}"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Nova lista
            </button>

            @if ($lists->isNotEmpty())
                <button
                    type="button"
                    data-modal-open="task-create-modal"
                    class="{{ $primaryButton }}"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Nova tarefa
                </button>
            @endif
        </div>
    </div>

    <div class="mt-7 grid gap-6 lg:grid-cols-[18rem_minmax(0,1fr)] lg:items-start">
        @include('admin.tasks.partials.sidebar', ['listPayloadFor' => $listPayloadFor])

        <section class="min-w-0 space-y-4">
            <div class="{{ $panel }} overflow-hidden">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $activeList !== null ? $activeList->color->softClasses() : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' }}">
                            <x-icon :name="$viewMeta['icon']" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $viewMeta['title'] }}</h2>
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                    {{ $tasks->count() }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $viewMeta['description'] }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
                        @if ($activeList !== null)
                            <button
                                type="button"
                                data-modal-open="task-list-modal"
                                data-list='@json($listPayloadFor($activeList))'
                                class="{{ $secondaryButton }}"
                            >
                                <x-icon name="pencil-square" class="h-4 w-4" />
                                Editar lista
                            </button>
                        @endif

                        <form method="GET" action="{{ route('admin.tasks.index') }}" class="flex items-center gap-2">
                            @foreach ($scopeHidden as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            @if ($search !== '')
                                <input type="hidden" name="search" value="{{ $search }}">
                            @endif

                            <label for="task-sort" class="sr-only">Ordenar por</label>
                            <div class="relative">
                                <select
                                    id="task-sort"
                                    name="sort"
                                    data-auto-submit
                                    class="h-10 appearance-none rounded-xl border border-gray-200 bg-gray-50 pl-3 pr-9 text-sm font-medium text-gray-600 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300"
                                >
                                    @foreach ($sortOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                    <x-icon name="chevron-down" class="h-4 w-4" />
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.tasks.index') }}" class="flex flex-wrap items-center gap-2 border-t border-gray-100 px-5 py-3 sm:px-6 dark:border-white/10">
                    @foreach ($scopeHidden as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    @if ($sort !== 'manual')
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif

                    <div class="relative min-w-0 flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                            <x-icon name="magnifying-glass" class="h-4 w-4" />
                        </span>
                        <input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Pesquisar tarefas..."
                            class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:bg-white/5"
                        >
                    </div>

                    <button type="submit" class="{{ $secondaryButton }}">Pesquisar</button>

                    @if ($search !== '')
                        <a href="{{ route('admin.tasks.index', $scopeHidden) }}" class="px-2 text-sm font-medium text-gray-500 transition hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300">Limpar</a>
                    @endif
                </form>
            </div>

            @if ($lists->isEmpty())
                <div class="{{ $panel }} px-6 py-16 text-center sm:py-20">
                    <span class="relative mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="clipboard-document-list" class="h-7 w-7" />
                    </span>
                    <h3 class="mt-5 text-base font-semibold text-gray-900 dark:text-white">Crie a sua primeira lista</h3>
                    <p class="mx-auto mt-1.5 max-w-sm text-sm text-gray-500 dark:text-gray-400">As listas agrupam tarefas como Trabalho, Casa ou Compras.</p>
                    <button type="button" data-modal-open="task-list-modal" class="{{ $primaryButton }} mt-5">
                        <x-icon name="plus" class="h-4 w-4" />
                        Nova lista
                    </button>
                </div>
            @else
                @if ($view !== 'completed')
                    <form
                        method="POST"
                        action="{{ route('admin.tasks.store') }}"
                        class="{{ $panel }} flex flex-col gap-2 p-3 sm:flex-row sm:items-center"
                    >
                        @csrf
                        @if ($activeList !== null)
                            <input type="hidden" name="todo_list_id" value="{{ $activeList->getKey() }}">
                        @endif

                        @if ($view === 'my_day')
                            <input type="hidden" name="my_day" value="1">
                        @endif
                        @if ($view === 'important')
                            <input type="hidden" name="is_important" value="1">
                        @endif

                        <span class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 sm:inline-flex dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon name="plus" class="h-4 w-4" />
                        </span>

                        <input
                            type="text"
                            name="title"
                            required
                            maxlength="255"
                            placeholder="Adicionar uma tarefa"
                            class="h-10 min-w-0 flex-1 rounded-xl border border-transparent bg-gray-50 px-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:bg-white/[0.04] dark:text-gray-100 dark:placeholder:text-gray-500"
                        >

                        @if ($activeList === null)
                            <label for="task-compose-list" class="sr-only">Lista</label>
                            <select
                                id="task-compose-list"
                                name="todo_list_id"
                                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-600 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 sm:w-44 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300"
                            >
                                @foreach ($lists as $list)
                                    <option value="{{ $list->getKey() }}" @selected($activeList?->is($list))>{{ $list->name }}</option>
                                @endforeach
                            </select>
                        @endif

                        <button type="submit" class="{{ $primaryButton }}">
                            <x-icon name="plus" class="h-4 w-4" />
                            Adicionar
                        </button>
                    </form>
                @endif

                @if ($tasks->isEmpty())
                    <div class="{{ $panel }} px-6 py-16 text-center sm:py-20">
                        <span class="relative mx-auto inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-white/[0.04] dark:text-gray-500">
                            <span class="absolute inset-0 rounded-full border border-dashed border-gray-200 dark:border-white/10"></span>
                            <x-icon :name="$viewMeta['icon']" class="h-7 w-7" />
                        </span>
                        <h3 class="mt-5 text-base font-semibold text-gray-900 dark:text-white">{{ $emptyMessage }}</h3>
                        <p class="mx-auto mt-1.5 max-w-sm text-sm text-gray-500 dark:text-gray-400">Adicione uma tarefa acima para começar.</p>
                    </div>
                @else
                    <div class="{{ $panel }} overflow-hidden">
                        <ul class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($tasks as $task)
                                @include('admin.tasks.partials.task-row', ['task' => $task])
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($completedTasks->isNotEmpty())
                    <div class="{{ $panel }} overflow-hidden" x-data="{ open: false }">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]"
                        >
                            <span class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <x-icon name="check-circle" class="h-4 w-4 text-gray-400" />
                                Concluídas
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-white/10 dark:text-gray-300">{{ $completedTasks->count() }}</span>
                            </span>
                            <span class="text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''">
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </span>
                        </button>

                        <ul x-show="open" x-cloak class="divide-y divide-gray-100 border-t border-gray-100 dark:divide-white/10 dark:border-white/10">
                            @foreach ($completedTasks as $task)
                                @include('admin.tasks.partials.task-row', ['task' => $task])
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </section>
    </div>

    @include('admin.tasks.partials.task-modal', ['lists' => $lists])
    @include('admin.tasks.partials.task-create-modal', ['lists' => $lists, 'activeList' => $activeList, 'view' => $view])
    @include('admin.tasks.partials.list-modal')
    @include('admin.tasks.partials.scripts')
</x-layouts.admin>
