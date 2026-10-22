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
@endphp

<x-layouts.admin title="Tarefas">
    <x-ui.page-header
        category="Produtividade"
        title="Tarefas"
        description="Organize listas, planeje o dia e acompanhe suas etapas."
        icon="check-circle"
    >
        <x-ui.action
            :href="route('admin.tasks.export', $exportQuery)"
            variant="secondary"
            icon="arrow-down-tray"
            data-export-link
            data-export-filename="tarefas.xlsx"
        >
            Exportar Excel
        </x-ui.action>

        <x-ui.action
            type="button"
            variant="secondary"
            icon="plus"
            data-modal-open="task-list-modal"
        >
            Nova lista
        </x-ui.action>

        @if ($lists->isNotEmpty())
            <x-ui.action type="button" icon="plus" data-modal-open="task-create-modal">
                Nova tarefa
            </x-ui.action>
        @endif
    </x-ui.page-header>

    <div class="mt-7 grid gap-6 lg:grid-cols-[18rem_minmax(0,1fr)] lg:items-start">
        <x-tasks.sidebar
            :counts="$counts"
            :view="$view"
            :lists="$lists"
            :active-list="$activeList"
            :list-counts="$listCounts"
            :list-payload-for="$listPayloadFor"
        />

        <section class="min-w-0 space-y-4">
            <x-tasks.view-card
                :view-meta="$viewMeta"
                :task-count="$tasks->count()"
                :active-list="$activeList"
                :sort="$sort"
                :sort-options="$sortOptions"
                :search="$search"
                :scope-hidden="$scopeHidden"
                :list-payload-for="$listPayloadFor"
            />

            @if ($lists->isEmpty())
                <div class="rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
                    <x-ui.empty-state
                        icon="clipboard-document-list"
                        title="Crie a sua primeira lista"
                        description="As listas agrupam tarefas como Trabalho, Casa ou Compras."
                        tone="indigo"
                        shape="square"
                    >
                        <x-ui.action type="button" icon="plus" class="mt-5" data-modal-open="task-list-modal">
                            Nova lista
                        </x-ui.action>
                    </x-ui.empty-state>
                </div>
            @else
                @if ($tasks->isEmpty())
                    <div class="rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
                        <x-ui.empty-state
                            :icon="$viewMeta['icon']"
                            :title="$emptyMessage"
                            description="Use o botão “Nova tarefa” para começar."
                        />
                    </div>
                @else
                    <x-tasks.task-list :tasks="$tasks" :active-list="$activeList" :sort="$sort" />
                @endif

                @if ($completedTasks->isNotEmpty())
                    <x-tasks.completed-list :tasks="$completedTasks" :active-list="$activeList" :sort="$sort" />
                @endif
            @endif
        </section>
    </div>

    <x-tasks.detail-modal :lists="$lists" :agenda-events="$agendaEvents" />
    <x-tasks.create-modal :lists="$lists" :agenda-events="$agendaEvents" :active-list="$activeList" :view="$view" />
    <x-tasks.list-modal />

    @include('admin.tasks.partials.scripts')
</x-layouts.admin>
