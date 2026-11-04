<x-layouts.admin title="Equipes">
    @php
        $hasFilters = $search !== '' || $status !== '';
    @endphp

    <x-ui.page-header category="Acompanhamento" title="Equipes" description="Gerencie equipes, membros e registros de acompanhamento." icon="users">
        <x-ui.action :href="route('admin.teams.create')" icon="plus">Nova equipe</x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-gray-200/80 bg-white px-4 py-2.5 text-xs text-gray-500 dark:border-white/10 dark:bg-[#171716] dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5"><span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</span> equipes</span>
        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>
        <span class="inline-flex items-center gap-1.5"><span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $stats['active'] }}</span> ativas</span>
        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>
        <span class="inline-flex items-center gap-1.5"><span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $stats['members'] }}</span> pessoas</span>
        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>
        <span class="inline-flex items-center gap-1.5"><span class="font-semibold text-red-600 dark:text-red-400">{{ $stats['attention'] }}</span> em atenção</span>
    </div>

    <section class="mt-4 rounded-2xl border border-gray-200/80 bg-white p-4 dark:border-white/10 dark:bg-[#171716]">
        <form method="GET" action="{{ route('admin.teams.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input type="search" name="search" value="{{ $search }}" placeholder="Buscar por nome, área ou descrição..." class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select id="status-filter" name="status" onchange="this.form.submit()" class="h-10 rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">Todos os status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <x-ui.action type="submit" variant="secondary" icon="magnifying-glass" size="sm">Buscar</x-ui.action>

            @if ($hasFilters)
                <a href="{{ route('admin.teams.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Limpar</a>
            @endif
        </form>
    </section>

    @if ($teams->isEmpty())
        <div class="mt-4 rounded-3xl border border-dashed border-gray-200 bg-white dark:border-white/10 dark:bg-[#171716]">
            <x-ui.empty-state icon="users" title="{{ $hasFilters ? 'Nenhuma equipe encontrada' : 'Nenhuma equipe cadastrada' }}" description="{{ $hasFilters ? 'Ajuste a busca ou os filtros para ver mais resultados.' : 'Cadastre a primeira equipe para começar.' }}" tone="indigo" shape="square">
                @unless ($hasFilters)
                    <x-ui.action :href="route('admin.teams.create')" size="sm" icon="plus" class="mt-4">Nova equipe</x-ui.action>
                @endunless
            </x-ui.empty-state>
        </div>
    @else
        <div class="mt-4 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5">Equipe</th>
                            <th class="px-5 py-3.5">Líder</th>
                            <th class="px-5 py-3.5">Membros</th>
                            <th class="px-5 py-3.5">Último acompanhamento</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($teams as $team)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.teams.show', $team) }}" class="font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">{{ $team->name }}</a>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $team->area ?: 'Área não informada' }}</p>
                                </td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">{{ $team->leader?->full_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">{{ $team->members_count }}</td>
                                <td class="px-5 py-4">
                                    @if ($team->latestCheckIn)
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <x-ui.badge :tone="$team->latestCheckIn->status->tone()">{{ $team->latestCheckIn->status->label() }}</x-ui.badge>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $team->latestCheckIn->reference_date->format('d/m/Y') }}</span>
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $team->latestCheckIn->health_score }}/5</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Sem registros</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4"><x-ui.badge :tone="$team->status->tone()">{{ $team->status->label() }}</x-ui.badge></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.teams.show', $team) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Ver {{ $team->name }}"><x-icon name="eye" class="h-4 w-4" /></a>
                                        <a href="{{ route('admin.teams.edit', $team) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Editar {{ $team->name }}"><x-icon name="pencil-square" class="h-4 w-4" /></a>
                                        <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" data-confirm-delete="Excluir a equipe {{ $team->name }}? Membros e acompanhamentos serão removidos.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-500/15" aria-label="Excluir {{ $team->name }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4 dark:border-white/10"><x-pagination :paginator="$teams" /></div>
        </div>
    @endif
</x-layouts.admin>
