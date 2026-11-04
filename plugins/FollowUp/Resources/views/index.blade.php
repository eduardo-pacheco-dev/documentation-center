<x-layouts.admin title="Follow-ups">
    @php
        $hasFilters = $search !== '' || $status !== '' || $priority !== '' || $assigned !== '';

        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $visibleFollowUps = $view === 'kanban' ? $kanban->flatten() : $followUps;
        $totalFollowUps = $visibleFollowUps->count();

        $openCreateModal = request()->boolean('create') || (old('modal') === 'create' && $errors->any());
    @endphp

    <x-ui.page-header
        category="Acompanhamento"
        title="Follow-ups"
        description="Acompanhe pendências, prazos e responsáveis em um só lugar."
        icon="clipboard-document-list"
    >
        <x-ui.action type="button" icon="plus" x-on:click="$dispatch('open-follow-up-create')">
            Novo follow-up
        </x-ui.action>

        <div
            x-data="{
                menuId: 'follow-up-page-actions',
                open: false,
                top: 0,
                left: 0,
                toggle(button) {
                    this.open = ! this.open;

                    if (! this.open) {
                        return;
                    }

                    const rect = button.getBoundingClientRect();
                    const width = 208;
                    const height = 176;
                    const gap = 6;
                    const below = rect.bottom + gap;

                    this.top = below + height > window.innerHeight - 8
                        ? Math.max(8, rect.top - height - gap)
                        : below;

                    this.left = Math.max(8, Math.min(rect.right - width, window.innerWidth - width - 8));

                    setTimeout(() => {
                        document.getElementById(this.menuId)?.querySelector('[role=menuitem]')?.focus();
                    }, 0);
                },
                close(refocus = false) {
                    this.open = false;

                    if (refocus) {
                        this.$el.querySelector('[aria-haspopup=menu]')?.focus();
                    }
                },
            }"
            x-on:scroll.window="close()"
            x-on:resize.window="close()"
            class="inline-block text-left"
        >
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300"
                aria-haspopup="menu"
                aria-controls="follow-up-page-actions"
                x-bind:aria-expanded="open ? 'true' : 'false'"
                x-on:click="toggle($el)"
            >
                <span class="sr-only">Abrir menu de ações</span>
                <x-icon name="ellipsis-vertical" class="h-5 w-5" />
            </button>

            <template x-teleport="body">
                <div
                    id="follow-up-page-actions"
                    x-cloak
                    x-show="open"
                    x-transition.opacity.duration.100ms
                    x-on:click.outside="close()"
                    x-on:keydown.escape.window="close(true)"
                    x-bind:style="`top: ${top}px; left: ${left}px;`"
                    class="fixed z-[90] w-52 rounded-xl border border-gray-200/80 bg-white p-1 shadow-lg dark:border-white/10 dark:bg-[#1c1c1b]"
                    role="menu"
                    aria-orientation="vertical"
                >
                    <a
                        href="{{ route('admin.follow-ups.export', request()->only(['search', 'status', 'priority', 'assigned'])) }}"
                        role="menuitem"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                    >
                        <x-icon name="arrow-down-tray" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        Exportar
                    </a>

                    <a
                        href="{{ route('admin.follow-ups.import') }}"
                        role="menuitem"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                    >
                        <x-icon name="arrow-up-tray" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        Importar
                    </a>

                    <button
                        type="button"
                        role="menuitem"
                        x-on:click="$dispatch('open-email-composer'); close()"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                    >
                        <x-icon name="paper-airplane" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        Enviar por e-mail
                    </button>

                    @if (auth()->user()?->is_admin)
                        <a
                            href="{{ route('admin.follow-ups.settings') }}"
                            role="menuitem"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                        >
                            <x-icon name="cog-6-tooth" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                            Configuração
                        </a>
                    @endif
                </div>
            </template>
        </div>
    </x-ui.page-header>

    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-gray-200/80 bg-white px-4 py-2.5 text-xs text-gray-500 dark:border-white/10 dark:bg-[#171716] dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['open'] }}</span>
            em aberto
        </span>

        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-red-600 dark:text-red-400">{{ $stats['overdue'] }}</span>
            atrasados
        </span>

        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $stats['completed'] }}</span>
            concluídos
        </span>

        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $stats['mine'] }}</span>
            atribuídos a mim
        </span>
    </div>

    <section class="mt-4 rounded-2xl border border-gray-200/80 bg-white p-4 dark:border-white/10 dark:bg-[#171716]">
        <form method="GET" action="{{ route('admin.follow-ups.index') }}" class="flex flex-wrap items-center gap-2">
            @foreach (['view', 'sort', 'direction'] as $param)
                @if (request($param))
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach

            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por título, código ou descrição..."
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
            </div>

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select id="status-filter" name="status" onchange="this.form.submit()" class="h-10 rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">Todos os status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <label for="priority-filter" class="sr-only">Filtrar por prioridade</label>
            <select id="priority-filter" name="priority" onchange="this.form.submit()" class="h-10 rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">Todas as prioridades</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected($priority === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <label for="assigned-filter" class="sr-only">Filtrar por responsável</label>
            <select id="assigned-filter" name="assigned" onchange="this.form.submit()" class="h-10 rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">Todos os responsáveis</option>
                <option value="me" @selected($assigned === 'me')>Atribuídos a mim</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->getKey() }}" @selected($assigned === (string) $assignee->getKey())>{{ $assignee->name }}</option>
                @endforeach
            </select>

            <x-ui.action type="submit" variant="secondary" icon="magnifying-glass" size="sm">Buscar</x-ui.action>

            @if ($hasFilters)
                <a href="{{ route('admin.follow-ups.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Limpar
                </a>
            @endif
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            @if ($view === 'kanban')
                Exibindo
                <span class="font-medium text-gray-900 dark:text-white">{{ $visibleFollowUps->count() }}</span>
                follow-ups
            @else
                Exibindo
                <span class="font-medium text-gray-900 dark:text-white">{{ $visibleFollowUps->count() }}</span>
                de
                <span class="font-medium text-gray-900 dark:text-white">{{ $visibleFollowUps->total() }}</span>
                follow-ups
            @endif
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-white/10 dark:bg-[#171716]" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cartões', 'compact' => 'Lista', 'kanban' => 'Quadro'] as $mode => $label)
                <a
                    href="{{ route('admin.follow-ups.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    @if ($view === $mode) aria-current="true" @endif
                    class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-sm font-medium transition {{ $view === $mode ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : ($mode === 'compact' ? 'bars-3' : 'view-columns'))" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($view === 'kanban')
        <div class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
            @foreach ($statuses as $value => $label)
                @php
                    $column = $kanban->get($value) ?? collect();

                    $dotClass = match ($value) {
                        'pendente' => 'bg-amber-500',
                        'em_andamento' => 'bg-indigo-500',
                        'concluido' => 'bg-emerald-500',
                        'cancelado' => 'bg-red-500',
                        default => 'bg-gray-400',
                    };
                @endphp

                <section class="flex flex-col rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
                    <header class="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-3 dark:border-white/10">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <span class="h-2 w-2 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
                            {{ $label }}
                        </span>

                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                            {{ $column->count() }}
                        </span>
                    </header>

                    <div class="flex flex-1 flex-col gap-2 p-3">
                        @forelse ($column as $followUp)
                            <article class="rounded-2xl border border-gray-200/80 bg-gray-50/60 p-3 transition hover:border-indigo-200 dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-indigo-500/40">
                                <div class="flex items-start justify-between gap-2">
                                    <a href="{{ route('admin.follow-ups.show', $followUp) }}" class="min-w-0 flex-1 text-sm font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                        {{ $followUp->title }}
                                    </a>

                                    <x-follow-up::partials.actions-menu :follow-up="$followUp" />
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <x-ui.badge :tone="$followUp->priority->tone()">{{ $followUp->priority->label() }}</x-ui.badge>

                                    @if ($followUp->isOverdue())
                                        <x-ui.badge tone="red">Atrasado</x-ui.badge>
                                    @endif
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                    <span class="truncate">{{ $followUp->assignee?->full_name ?? 'Não atribuído' }}</span>

                                    <span @class([
                                        'shrink-0 font-medium',
                                        'text-red-600 dark:text-red-400' => $followUp->isOverdue(),
                                        'text-amber-600 dark:text-amber-400' => ! $followUp->isOverdue() && $followUp->isDueSoon(),
                                        'text-gray-600 dark:text-gray-300' => ! $followUp->isOverdue() && ! $followUp->isDueSoon(),
                                    ])>
                                        {{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <p class="px-2 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Nenhum follow-up</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    @elseif ($visibleFollowUps->isEmpty())
        <div class="mt-4 rounded-3xl border border-dashed border-gray-200 bg-white dark:border-white/10 dark:bg-[#171716]">
            <x-ui.empty-state
                icon="clipboard-document-list"
                title="{{ $hasFilters ? 'Nenhum follow-up encontrado' : 'Nenhum follow-up cadastrado' }}"
                description="{{ $hasFilters ? 'Ajuste a busca ou os filtros para ver mais resultados.' : 'Crie o primeiro acompanhamento para começar.' }}"
                tone="indigo"
                shape="square"
            >
                @unless ($hasFilters)
                    <x-ui.action type="button" size="sm" icon="plus" x-on:click="$dispatch('open-follow-up-create')" class="mt-4">
                        Novo follow-up
                    </x-ui.action>
                @endunless
            </x-ui.empty-state>
        </div>
    @elseif ($view === 'table')
        <div class="mt-4 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                        <tr>
                            <x-sort-header column="title" label="Follow-up" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="code" label="Código" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="status" label="Status" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="priority" label="Prioridade" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="assignee" label="Responsável" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="due_date" label="Prazo" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="completed_at" label="Concluído em" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($followUps as $followUp)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.follow-ups.show', $followUp) }}" class="font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                        {{ $followUp->title }}
                                    </a>
                                    <p class="mt-0.5 max-w-sm truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $followUp->description ? \Illuminate\Support\Str::limit($followUp->description, 90) : 'Sem descrição' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($followUp->code)
                                        <span class="inline-flex rounded-lg bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                            {{ $followUp->code }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.badge :tone="$followUp->status->tone()">{{ $followUp->status->label() }}</x-ui.badge>
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.badge :tone="$followUp->priority->tone()">{{ $followUp->priority->label() }}</x-ui.badge>
                                </td>

                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $followUp->assignee?->full_name ?? 'Não atribuído' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($followUp->due_date)
                                        <span @class([
                                            'font-medium',
                                            'text-red-600 dark:text-red-400' => $followUp->isOverdue(),
                                            'text-amber-600 dark:text-amber-400' => ! $followUp->isOverdue() && $followUp->isDueSoon(),
                                            'text-gray-700 dark:text-gray-200' => ! $followUp->isOverdue() && ! $followUp->isDueSoon(),
                                        ])>
                                            {{ $followUp->due_date->format('d/m/Y') }}
                                        </span>
                                        @if ($followUp->isOverdue())
                                            <p class="mt-0.5 text-[11px] font-medium text-red-500">Atrasado</p>
                                        @endif
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">Sem prazo</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @if ($followUp->completed_at)
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400">
                                            {{ $followUp->completed_at->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end">
                                        <x-follow-up::partials.actions-menu :follow-up="$followUp" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 dark:border-white/10">
                <x-pagination :paginator="$followUps" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($followUps as $followUp)
                <x-follow-up::partials.card :follow-up="$followUp" />
            @endforeach
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$followUps" />
        </div>
    @else
        <ul class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:divide-white/10 dark:border-white/10 dark:bg-[#171716]">
            @foreach ($followUps as $followUp)
                <li class="flex items-center gap-3 px-4 py-3 transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.follow-ups.show', $followUp) }}" class="block truncate text-sm font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                            {{ $followUp->title }}
                        </a>

                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $followUp->assignee?->full_name ?? 'Não atribuído' }}
                            <span aria-hidden="true">·</span>
                            {{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}
                        </p>
                    </div>

                    <div class="hidden shrink-0 items-center gap-1.5 sm:flex">
                        <x-ui.badge :tone="$followUp->status->tone()">{{ $followUp->status->label() }}</x-ui.badge>
                        <x-ui.badge :tone="$followUp->priority->tone()">{{ $followUp->priority->label() }}</x-ui.badge>
                    </div>

                    <x-follow-up::partials.actions-menu :follow-up="$followUp" />
                </li>
            @endforeach
        </ul>

        <div class="mt-4">
            <x-pagination :paginator="$followUps" />
        </div>
    @endif

    <x-follow-up::partials.composer-modal :follow-ups="$emailFollowUps" :schedule="$emailSchedule" />

    <x-follow-up::partials.create-modal
        :assignees="$assignees"
        :statuses="$statuses"
        :priorities="$priorities"
        :open-on-load="$openCreateModal"
    />
</x-layouts.admin>
