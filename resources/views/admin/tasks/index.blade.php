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

    $panel = 'rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900';
    $input = 'w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    $primaryButton = 'inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500';
    $secondaryButton = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
    $chip = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-indigo-500/40';
@endphp

<x-layouts.admin title="Tarefas">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Produtividade</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="check-circle" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Tarefas
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Organize listas, planeje o dia e acompanhe suas etapas.</p>
        </div>

        <button
            type="button"
            data-modal-open="task-list-modal"
            class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Nova lista
        </button>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[17rem_1fr]">
        @include('admin.tasks.partials.sidebar', ['listPayloadFor' => $listPayloadFor])

        <section class="space-y-4">
            <div class="{{ $panel }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $activeList !== null ? $activeList->color->softClasses() : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' }}">
                            <x-icon :name="$viewMeta['icon']" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ $viewMeta['title'] }}
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $tasks->count() }}
                                </span>
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $viewMeta['description'] }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
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
                            <select
                                id="task-sort"
                                name="sort"
                                data-auto-submit
                                class="rounded-xl border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                            >
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.tasks.index') }}" class="flex items-center gap-2 px-5 py-3">
                    @foreach ($scopeHidden as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    @if ($sort !== 'manual')
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <x-icon name="magnifying-glass" class="h-4 w-4" />
                        </span>
                        <input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Pesquisar tarefas..."
                            class="w-full rounded-xl border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        >
                    </div>
                    <button type="submit" class="{{ $secondaryButton }}">Pesquisar</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.tasks.index', $scopeHidden) }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400">Limpar</a>
                    @endif
                </form>
            </div>

            @if ($lists->isEmpty())
                <div class="{{ $panel }} px-6 py-12 text-center">
                    <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="clipboard-document-list" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">Crie a sua primeira lista</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">As listas agrupam tarefas como Trabalho, Casa ou Compras.</p>
                    <button type="button" data-modal-open="task-list-modal" class="mt-4 inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        <x-icon name="plus" class="h-4 w-4" />
                        Nova lista
                    </button>
                </div>
            @else
                @if ($view !== 'completed')
                    <form
                        method="POST"
                        action="{{ route('admin.tasks.store') }}"
                        class="{{ $panel }} flex flex-wrap items-center gap-2 px-4 py-3"
                    >
                        @csrf
                        @if ($activeList !== null)
                            <input type="hidden" name="todo_list_id" value="{{ $activeList->getKey() }}">
                        @else
                            <label for="task-compose-list" class="sr-only">Lista</label>
                            <select id="task-compose-list" name="todo_list_id" class="w-40 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                @foreach ($lists as $list)
                                    <option value="{{ $list->getKey() }}" @selected($activeList?->is($list))>{{ $list->name }}</option>
                                @endforeach
                            </select>
                        @endif

                        @if ($view === 'my_day')
                            <input type="hidden" name="my_day" value="1">
                        @endif
                        @if ($view === 'important')
                            <input type="hidden" name="is_important" value="1">
                        @endif

                        <input
                            type="text"
                            name="title"
                            required
                            maxlength="255"
                            placeholder="Adicionar uma tarefa"
                            class="min-w-0 flex-1 rounded-xl border border-transparent bg-gray-50 px-3 py-2 text-sm text-gray-700 transition placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:bg-gray-800/60 dark:text-gray-100"
                        >

                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            <x-icon name="plus" class="h-4 w-4" />
                            Adicionar
                        </button>
                    </form>
                @endif

                @if ($tasks->isEmpty())
                    <div class="{{ $panel }} px-6 py-12 text-center">
                        <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                            <x-icon :name="$viewMeta['icon']" class="h-6 w-6" />
                        </span>
                        <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $emptyMessage }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Adicione uma tarefa acima para começar.</p>
                    </div>
                @else
                    <div class="{{ $panel }} overflow-hidden">
                        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
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
                            class="flex w-full items-center justify-between gap-3 px-5 py-3 text-left transition hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <span class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <x-icon name="check-circle" class="h-4 w-4 text-gray-400" />
                                Concluídas
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $completedTasks->count() }}</span>
                            </span>
                            <span class="text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''">
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </span>
                        </button>

                        <ul x-show="open" x-cloak class="divide-y divide-gray-100 border-t border-gray-100 dark:divide-gray-800 dark:border-gray-800">
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
    @include('admin.tasks.partials.list-modal')
    @include('admin.tasks.partials.scripts')
</x-layouts.admin>
