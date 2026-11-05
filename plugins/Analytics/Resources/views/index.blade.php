@php
    $statusTone = function (?string $status): string {
        $value = mb_strtolower((string) $status);

        return match (true) {
            str_contains($value, 'aceit'), str_contains($value, 'conclu'), str_contains($value, 'entreg'), str_contains($value, 'finaliz') => 'green',
            str_contains($value, 'aguardando'), str_contains($value, 'pend'), str_contains($value, 'impeditiv'), str_contains($value, 'corre') => 'amber',
            str_contains($value, 'cancel') => 'red',
            default => 'gray',
        };
    };

    $hasFilters = collect($filters)->filter(fn ($value): bool => filled($value))->isNotEmpty();

    $kpis = [
        [
            'label' => 'Total de OS',
            'value' => $stats['total'],
            'description' => 'Registros no período filtrado',
            'icon' => 'document-text',
            'tile' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        ],
        [
            'label' => 'Aceitas pela TIM',
            'value' => $stats['accepted'],
            'description' => 'Concluídas ou aceitas',
            'icon' => 'check',
            'tile' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        ],
        [
            'label' => 'Pendentes / Impeditivas',
            'value' => $stats['pending'],
            'description' => 'Aguardando tratativa',
            'icon' => 'exclamation-triangle',
            'tile' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        ],
    ];
@endphp

<x-layouts.admin title="Analytics">
    <x-ui.page-header
        category="Operações"
        title="Analytics & Field Operations"
        description="Acompanhe ordens de serviço, fornecedores, etapas de processo e status de implantação."
        icon="chart-bar"
    >
        <x-ui.action :href="route('admin.analytics.import')" icon="arrow-up-tray" variant="secondary">
            Importar planilha
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ($kpis as $kpi)
            <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
                <div class="flex items-start justify-between gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $kpi['tile'] }}">
                        <x-icon :name="$kpi['icon']" class="h-5 w-5" />
                    </span>
                </div>

                <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    {{ number_format((int) $kpi['value'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $kpi['label'] }}</p>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $kpi['description'] }}</p>
            </section>
        @endforeach
    </div>

    <section class="mt-4 rounded-2xl border border-gray-200/80 bg-white p-4 dark:border-white/10 dark:bg-[#171716]">
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="grid gap-2 lg:grid-cols-12">
            <div class="relative lg:col-span-3">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />

                <label for="analytics-search" class="sr-only">Buscar registros</label>
                <input
                    id="analytics-search"
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Buscar OC, OS, node, localização..."
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="analytics-start" class="sr-only">Data inicial</label>
                <input
                    id="analytics-start"
                    type="date"
                    name="start_date"
                    value="{{ $filters['start_date'] ?? '' }}"
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                    title="Data inicial"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="analytics-end" class="sr-only">Data final</label>
                <input
                    id="analytics-end"
                    type="date"
                    name="end_date"
                    value="{{ $filters['end_date'] ?? '' }}"
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                    title="Data final"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="analytics-supplier" class="sr-only">Fornecedor</label>
                <select
                    id="analytics-supplier"
                    name="fornecedor"
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    <option value="">Todos os fornecedores</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier }}" @selected(($filters['fornecedor'] ?? '') === $supplier)>{{ $supplier }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="analytics-status" class="sr-only">Status</label>
                <select
                    id="analytics-status"
                    name="status"
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected(($filters['status'] ?? '') === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="analytics-node" class="sr-only">Node name</label>
                <select
                    id="analytics-node"
                    name="node_name"
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    <option value="">Todos os nodes</option>
                    @foreach ($nodeNames as $nodeName)
                        <option value="{{ $nodeName }}" @selected(($filters['node_name'] ?? '') === $nodeName)>{{ $nodeName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 lg:col-span-9">
                <x-ui.action type="submit" icon="magnifying-glass">
                    Filtrar
                </x-ui.action>

                @if ($hasFilters)
                    <a href="{{ route('admin.analytics.index') }}" class="text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Limpar filtros
                    </a>
                @endif
            </div>
        </form>
    </section>

    <section class="mt-4 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
        @if ($records->isEmpty())
            <x-ui.empty-state
                icon="chart-bar"
                title="Nenhum registro encontrado"
                :description="$hasFilters
                    ? 'Ajuste os filtros ou limpe a busca para ver mais resultados.'
                    : 'Importe uma planilha para começar a acompanhar as ordens de serviço.'"
                tone="indigo"
                shape="square"
            >
                @unless ($hasFilters)
                    <x-ui.action :href="route('admin.analytics.import')" icon="arrow-up-tray" size="sm" class="mt-4">
                        Importar planilha
                    </x-ui.action>
                @endunless
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:border-white/10 dark:bg-white/[0.02] dark:text-gray-500">
                            <th scope="col" class="px-5 py-3">OC / OS</th>
                            <th scope="col" class="px-3 py-3">Data</th>
                            <th scope="col" class="px-3 py-3">Fornecedor</th>
                            <th scope="col" class="px-3 py-3">Etapa</th>
                            <th scope="col" class="px-3 py-3">Node</th>
                            <th scope="col" class="hidden px-3 py-3 lg:table-cell">Localização</th>
                            <th scope="col" class="hidden px-3 py-3 md:table-cell">Cidade / UF</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($records as $record)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                <td class="px-5 py-3.5">
                                    <p class="font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ $record->os }}</p>
                                    <p class="mt-0.5 font-mono text-[11px] text-gray-400 dark:text-gray-500">{{ $record->oc }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $record->data_criacao?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-3 py-3.5 text-sm text-gray-700 dark:text-gray-200">{{ $record->fornecedor }}</td>
                                <td class="px-3 py-3.5 text-xs text-gray-500 dark:text-gray-400">{{ $record->etapa_processo ?: '—' }}</td>
                                <td class="whitespace-nowrap px-3 py-3.5 font-mono text-xs font-medium text-gray-700 dark:text-gray-200">{{ $record->node_name }}</td>
                                <td class="hidden whitespace-nowrap px-3 py-3.5 font-mono text-xs text-gray-500 lg:table-cell dark:text-gray-400">{{ $record->localizacao ?: '—' }}</td>
                                <td class="hidden whitespace-nowrap px-3 py-3.5 text-xs text-gray-500 md:table-cell dark:text-gray-400">
                                    {{ collect([$record->cidade, $record->estado])->filter()->implode('/') ?: '—' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <x-ui.badge :tone="$statusTone($record->status)">{{ $record->status }}</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 dark:border-white/10">
                {{ $records->links() }}
            </div>
        @endif
    </section>
</x-layouts.admin>
