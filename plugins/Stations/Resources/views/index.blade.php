<x-layouts.admin title="Estações">
    @php
        $hasFilters = $search !== '' || $status !== '' || $tecnologia !== '';

        $fieldGroups = config('stations.fields', []);

        $firstErrorStep = function () use ($fieldGroups, $errors): int {
            foreach ($fieldGroups as $index => $group) {
                if ($errors->hasAny(array_column($group['fields'], 'name'))) {
                    return $index;
                }
            }

            return 0;
        };

        $createHasErrors = old('modal') === 'create' && $errors->any();
        $editHasErrors = old('modal') === 'edit' && $errors->any();

        $createInitialStep = $createHasErrors ? $firstErrorStep() : 0;
        $editInitialStep = $editHasErrors ? $firstErrorStep() : 0;

        $statusTone = fn (?string $value): string => match (mb_strtolower((string) $value)) {
            'aquisitado', 'ativo', 'ativa' => 'green',
            'em análise', 'em analise', 'pendente' => 'amber',
            'cancelado', 'cancelada' => 'red',
            default => 'gray',
        };
    @endphp

    <x-ui.page-header
        category="Inventário"
        title="Estações"
        description="Cadastre e consulte os elementos de rede, a infraestrutura e o endereço de cada estação."
        icon="signal"
    >
        <x-ui.action
            :href="route('admin.stations.create')"
            icon="plus"
            x-on:click.prevent="$dispatch('open-station-modal')"
        >
            Nova estação
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-gray-200/80 bg-white px-4 py-2.5 text-xs text-gray-500 dark:border-white/10 dark:bg-[#171716] dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
            estações
        </span>

        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $stats['sites'] }}</span>
            sites distintos
        </span>

        <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

        <span class="inline-flex items-center gap-1.5">
            <span class="font-semibold text-violet-600 dark:text-violet-400">{{ $stats['municipios'] }}</span>
            municípios
        </span>
    </div>

    <section class="mt-4 rounded-2xl border border-gray-200/80 bg-white p-4 dark:border-white/10 dark:bg-[#171716]">
        <form method="GET" action="{{ route('admin.stations.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por ID do site, ID da estação, endereço..."
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
            </div>

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select
                id="status-filter"
                name="status"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
            >
                <option value="">Todos os status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                @endforeach
            </select>

            <label for="tecnologia-filter" class="sr-only">Filtrar por tecnologia</label>
            <select
                id="tecnologia-filter"
                name="tecnologia"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
            >
                <option value="">Todas as tecnologias</option>
                @foreach ($tecnologias as $option)
                    <option value="{{ $option }}" @selected($tecnologia === $option)>{{ $option }}</option>
                @endforeach
            </select>

            <x-ui.action type="submit" variant="secondary" icon="magnifying-glass" size="sm">Buscar</x-ui.action>

            @if ($hasFilters)
                <a
                    href="{{ route('admin.stations.index') }}"
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >
                    Limpar
                </a>
            @endif
        </form>
    </section>

    @if ($stations->isEmpty())
        <div class="mt-4 rounded-3xl border border-dashed border-gray-200 bg-white dark:border-white/10 dark:bg-[#171716]">
            <x-ui.empty-state
                icon="signal"
                title="{{ $hasFilters ? 'Nenhuma estação encontrada' : 'Nenhuma estação cadastrada' }}"
                description="{{ $hasFilters ? 'Ajuste a busca ou os filtros para ver mais resultados.' : 'Cadastre a primeira estação para começar.' }}"
                tone="indigo"
                shape="square"
            >
                @unless ($hasFilters)
                    <x-ui.action
                        :href="route('admin.stations.create')"
                        size="sm"
                        icon="plus"
                        class="mt-4"
                        x-on:click.prevent="$dispatch('open-station-modal')"
                    >
                        Nova estação
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
                            <th class="px-5 py-3.5">Estação</th>
                            <th class="px-5 py-3.5">Elemento</th>
                            <th class="px-5 py-3.5">Endereço</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($stations as $station)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route('admin.stations.show', $station) }}"
                                        class="font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"
                                    >
                                        {{ $station->station_id ?: $station->site_id }}
                                    </a>
                                    <p class="mt-0.5 font-mono text-[11px] text-gray-400 dark:text-gray-500">
                                        SITE {{ $station->site_id }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-gray-700 dark:text-gray-200">{{ $station->tipo_de_elemento ?: '—' }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $station->tecnologia ?: 'Sem tecnologia' }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="max-w-xs truncate text-gray-700 dark:text-gray-200">{{ $station->address() ?: '—' }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $station->regional ?: 'Sem regional' }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.badge :tone="$statusTone($station->status)">
                                        {{ $station->status ?: 'Sem status' }}
                                    </x-ui.badge>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <x-stations::partials.actions-menu :station="$station" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 dark:border-white/10">
                <x-pagination :paginator="$stations" />
            </div>
        </div>
    @endif

    <x-stations::partials.station-modal
        mode="create"
        :has-errors="$createHasErrors"
        :initial-step="$createInitialStep"
    />

    <x-stations::partials.station-modal
        mode="edit"
        :station="$editStation ?? null"
        :has-errors="$editHasErrors"
        :initial-step="$editInitialStep"
    />
</x-layouts.admin>
