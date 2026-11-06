<x-layouts.admin title="Projetos Nokia">
    @php
        $hasFilters = $search !== '';
        $checklists = config('nokia.checklists', []);
        $createHasErrors = $createHasErrors ?? false;
        $editHasErrors = $editHasErrors ?? false;
    @endphp
    <x-ui.page-header
        category="Nokia"
        title="Sites"
        description="Cadastre os sites e acompanhe o checklist de atividades de entrega e instalação."
        icon="signal"
    >
        <x-ui.action
            :href="route('admin.nokia.sites.create')"
            icon="plus"
            x-on:click.prevent="$dispatch('open-site-modal')"
        >
            Novo site
        </x-ui.action>

        <div class="relative" x-data="{ actionsOpen: false }" x-on:keydown.escape.window="actionsOpen = false">
            <x-ui.icon-button
                icon="ellipsis-vertical"
                label="Ações dos sites Nokia"
                x-on:click="actionsOpen = ! actionsOpen"
                x-bind:aria-expanded="actionsOpen ? 'true' : 'false'"
                aria-haspopup="menu"
            />

            <x-ui.dropdown
                show="actionsOpen"
                close="actionsOpen = false"
                origin="top.right"
                class="right-0 top-full mt-2 w-52 p-1"
                role="menu"
                aria-label="Ações dos sites Nokia"
            >
                @if (auth()->user()?->is_admin)
                    <a
                        href="{{ route('admin.nokia.settings.index') }}"
                        role="menuitem"
                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                    >
                        <x-icon name="cog-6-tooth" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        Configurações
                    </a>
                @endif

                <a
                    href="{{ route('admin.nokia.sites.export', array_filter(['search' => $search])) }}"
                    role="menuitem"
                    class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                >
                    <x-icon name="arrow-down-tray" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    Exportar
                </a>

                <a
                    href="{{ route('admin.nokia.sites.import') }}"
                    role="menuitem"
                    class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                >
                    <x-icon name="arrow-up-tray" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    Importar
                </a>
            </x-ui.dropdown>
        </div>
    </x-ui.page-header>

    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-gray-200/80 bg-white px-4 py-2.5 text-xs text-gray-500 dark:border-white/10 dark:bg-[#171716] dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
            sites
        </span>

        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $stats['completed'] }}</span>
            concluídos
        </span>

        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $stats['pending'] }}</span>
            pendentes
        </span>
    </div>

    <section class="mt-4 rounded-2xl border border-gray-200/80 bg-white p-4 dark:border-white/10 dark:bg-[#171716]">
        <form method="GET" action="{{ route('admin.nokia.sites.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por site, end, OC ou OS..."
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
            </div>

            <x-ui.action type="submit" variant="secondary" icon="magnifying-glass" size="sm">Buscar</x-ui.action>

            @if ($hasFilters)
                <a
                    href="{{ route('admin.nokia.sites.index') }}"
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >
                    Limpar
                </a>
            @endif
        </form>
    </section>

    @if ($sites->isEmpty())
        <div class="mt-4 rounded-3xl border border-dashed border-gray-200 bg-white dark:border-white/10 dark:bg-[#171716]">
            <x-ui.empty-state
                icon="signal"
                title="{{ $hasFilters ? 'Nenhum site encontrado' : 'Nenhum site cadastrado' }}"
                description="{{ $hasFilters ? 'Ajuste a busca para ver mais resultados.' : 'Cadastre o primeiro site para começar.' }}"
                tone="indigo"
                shape="square"
            >
                @unless ($hasFilters)
                    <x-ui.action
                        :href="route('admin.nokia.sites.create')"
                        size="sm"
                        icon="plus"
                        class="mt-4"
                        x-on:click.prevent="$dispatch('open-site-modal')"
                    >
                        Novo site
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
                            <th class="px-5 py-3.5">Site</th>
                            <th class="px-5 py-3.5">End ID</th>
                            <th class="px-5 py-3.5">OC</th>
                            <th class="px-5 py-3.5">ROM</th>
                            <th class="px-5 py-3.5">OS FAM Entrega</th>
                            <th class="px-5 py-3.5">OS FAM Instalação</th>
                            <th class="px-5 py-3.5">Progresso</th>
                            <th class="px-5 py-3.5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($sites as $site)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route('admin.nokia.sites.show', $site) }}"
                                        class="font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"
                                    >
                                        {{ $site->site_id }}
                                    </a>
                                    <p class="mt-0.5 font-mono text-[11px] text-gray-400 dark:text-gray-500">
                                        Atualizado {{ $site->updated_at?->format('d/m/Y H:i') }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 font-mono text-xs text-gray-600 dark:text-gray-300">
                                    {{ $site->end_id ?: '—' }}
                                </td>

                                <td class="px-5 py-4 font-mono text-xs text-gray-600 dark:text-gray-300">
                                    {{ $site->oc ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                    {{ $site->rom?->fullName() ?: '—' }}
                                </td>

                                <td class="px-5 py-4 font-mono text-xs text-gray-600 dark:text-gray-300">
                                    {{ $site->os_fam_entrega ?: '—' }}
                                </td>

                                <td class="px-5 py-4 font-mono text-xs text-gray-600 dark:text-gray-300">
                                    {{ $site->os_fam_instalacao ?: '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    @php
                                        $checklistTotal = count($checklists) + ($tasksTotal ?? 0);
                                        $checklistDone = collect($checklists)->keys()
                                            ->filter(fn (string $field): bool => $site->checklistStatus($field) === 'sent')
                                            ->count() + (int) $site->completed_tasks_count;
                                        $checklistPercent = $checklistTotal > 0 ? (int) round(($checklistDone / $checklistTotal) * 100) : 0;
                                    @endphp

                                    <div class="flex items-center gap-2">
                                        <x-ui.progress-bar :percent="$checklistPercent" height="h-1.5" class="w-24" />

                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                            {{ $checklistDone }}/{{ $checklistTotal }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <x-nokia::partials.actions-menu :site="$site" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 dark:border-white/10">
                <x-pagination :paginator="$sites" />
            </div>
        </div>
    @endif

    <x-nokia::partials.site-modal
        mode="create"
        :roms="$roms"
        :has-errors="$createHasErrors"
    />

    <x-nokia::partials.site-modal
        mode="edit"
        :site="$editSite ?? null"
        :roms="$roms"
        :has-errors="$editHasErrors"
    />
</x-layouts.admin>
