<x-layouts.admin title="Follow-ups">
    @php
        $hasFilters = $search !== '' || $status !== '' || $priority !== '' || $assigned !== '';
    @endphp

    <x-ui.page-header
        category="Acompanhamento"
        title="Follow-ups"
        description="Acompanhe pendências, prazos e responsáveis em um só lugar."
        icon="clipboard-document-list"
    >
        <x-ui.action
            :href="route('admin.follow-ups.export', request()->only(['search', 'status', 'priority', 'assigned']))"
            icon="arrow-down-tray"
            variant="secondary"
        >
            Exportar
        </x-ui.action>

        <x-ui.action :href="route('admin.follow-ups.create')" icon="plus">
            Novo follow-up
        </x-ui.action>
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
            @foreach (['sort', 'direction'] as $param)
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
                    placeholder="Buscar por título ou descrição..."
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
                @foreach ($users as $user)
                    <option value="{{ $user->getKey() }}" @selected($assigned === (string) $user->getKey())>{{ $user->full_name }}</option>
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

    @if ($followUps->isEmpty())
        <div class="mt-4 rounded-3xl border border-dashed border-gray-200 bg-white dark:border-white/10 dark:bg-[#171716]">
            <x-ui.empty-state
                icon="clipboard-document-list"
                title="{{ $hasFilters ? 'Nenhum follow-up encontrado' : 'Nenhum follow-up cadastrado' }}"
                description="{{ $hasFilters ? 'Ajuste a busca ou os filtros para ver mais resultados.' : 'Crie o primeiro acompanhamento para começar.' }}"
                tone="indigo"
                shape="square"
            >
                @unless ($hasFilters)
                    <x-ui.action :href="route('admin.follow-ups.create')" size="sm" icon="plus" class="mt-4">
                        Novo follow-up
                    </x-ui.action>
                @endunless
            </x-ui.empty-state>
        </div>
    @else
        <div class="mt-4 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                        <tr>
                            <x-sort-header column="title" label="Follow-up" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="status" label="Status" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="priority" label="Prioridade" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="assignee" label="Responsável" default="due_date" default-direction="asc" class="px-5 py-3.5" />
                            <x-sort-header column="due_date" label="Prazo" default="due_date" default-direction="asc" class="px-5 py-3.5" />
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
    @endif
</x-layouts.admin>
