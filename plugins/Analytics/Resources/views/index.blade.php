@php
    $cardClass = 'min-w-0 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04)] dark:border-white/10 dark:bg-[#171716]';
    $labelClass = 'text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500';
    $toolbarButton = 'inline-flex h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-medium transition';

    $cards = [
        ['label' => 'Projetos ativos', 'value' => $summary['projects']['active'], 'hint' => $summary['projects']['total'].' no total', 'icon' => 'briefcase', 'tone' => 'indigo'],
        ['label' => 'OS em aberto', 'value' => $summary['work_orders']['open'], 'hint' => $summary['work_orders']['total'].' no total', 'icon' => 'clipboard-document-list', 'tone' => 'sky'],
        ['label' => 'Tarefas pendentes', 'value' => $summary['tasks']['pending'], 'hint' => $summary['tasks']['completed'].' concluídas', 'icon' => 'check-circle', 'tone' => 'emerald'],
        ['label' => 'Documentos no período', 'value' => $summary['documents']['in_period'], 'hint' => \Illuminate\Support\Number::fileSize($summary['documents']['size']).' armazenados', 'icon' => 'document-text', 'tone' => 'violet'],
        ['label' => 'Formulários pendentes', 'value' => $summary['forms']['pending'], 'hint' => $summary['forms']['total'].' no total', 'icon' => 'document-check', 'tone' => 'amber'],
        ['label' => 'Eventos no período', 'value' => $summary['agenda']['in_period'], 'hint' => $summary['agenda']['total'].' na agenda', 'icon' => 'calendar-days', 'tone' => 'rose'],
    ];

    if (is_array($recordsSummary)) {
        $cards[] = ['label' => 'Registros FAMs', 'value' => $recordsSummary['total'], 'hint' => $recordsSummary['versions'].' versões no histórico', 'icon' => 'table-cells', 'tone' => 'indigo'];
    }

    if (is_array($aflSummary)) {
        $cards[] = ['label' => 'Registros AFL', 'value' => $aflSummary['total'], 'hint' => $aflSummary['versions'].' versões no histórico', 'icon' => 'flag', 'tone' => 'rose'];
    }

    $tones = [
        'indigo' => 'from-indigo-500 to-violet-500',
        'sky' => 'from-sky-500 to-cyan-500',
        'emerald' => 'from-emerald-500 to-teal-500',
        'violet' => 'from-violet-500 to-fuchsia-500',
        'amber' => 'from-amber-500 to-orange-500',
        'rose' => 'from-rose-500 to-pink-500',
    ];

    $maxProjects = max(1, ...($topProjects['values'] ?: [1]));
    $hasSnapshotSeries = is_array($snapshotSeries) && count($snapshotSeries['labels']) >= 2;
    $scopeQuery = $scope === 'global' ? 'global' : null;
    $tabs = [
        'overview' => ['label' => 'Visão geral', 'count' => null],
        'fams' => ['label' => 'FAMs', 'count' => $recordsSummary['total'] ?? null],
        'afl' => ['label' => 'AFL', 'count' => $aflSummary['total'] ?? null],
        'snapshots' => ['label' => 'Snapshots', 'count' => $snapshots === [] ? null : count($snapshots)],
    ];
@endphp

<x-layouts.admin title="Analytics">
    <x-ui.page-header
        category="Inteligência"
        title="Analytics"
        :description="'Indicadores de '.$from->format('d/m/Y').' a '.$to->format('d/m/Y').' · '.($isGlobal ? 'visão global' : 'seus dados')"
        icon="chart-bar"
    >
        <x-ui.action :href="route('analytics.export', array_filter(['period' => $days, 'scope' => $scopeQuery]))" variant="secondary" icon="arrow-down-tray">
            Exportar CSV
        </x-ui.action>

        <form method="POST" action="{{ route('analytics.snapshot') }}">
            @csrf
            <input type="hidden" name="period" value="{{ $days }}">
            <input type="hidden" name="scope" value="{{ $scope }}">

            <x-ui.action type="submit" icon="camera">
                Snapshot de hoje
            </x-ui.action>
        </form>
    </x-ui.page-header>

    <div class="{{ $cardClass }} mt-6 flex flex-wrap items-center justify-between gap-3 p-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-1 text-xs font-medium text-gray-400 dark:text-gray-500">Período</span>

            <div class="inline-flex rounded-xl border border-gray-200 p-1 dark:border-white/10" role="group" aria-label="Período">
                @foreach ($periods as $option)
                    <a
                        href="{{ route('analytics.index', array_filter(['period' => $option, 'scope' => $scopeQuery])) }}"
                        class="{{ $toolbarButton }} {{ ! request('from') && $days === (int) $option ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                        title="Últimos {{ $option }} dias"
                    >
                        {{ $option }}d
                    </a>
                @endforeach
            </div>

            @if (auth()->user()?->is_admin)
                <div class="inline-flex rounded-xl border border-gray-200 p-1 dark:border-white/10" role="group" aria-label="Escopo">
                    <a
                        href="{{ route('analytics.index', ['period' => $days]) }}"
                        class="{{ $toolbarButton }} {{ $scope === 'user' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                    >
                        Meus dados
                    </a>
                    <a
                        href="{{ route('analytics.index', ['period' => $days, 'scope' => 'global']) }}"
                        class="{{ $toolbarButton }} {{ $scope === 'global' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                    >
                        Global
                    </a>
                </div>
            @endif
        </div>

        <p class="px-1 text-xs text-gray-400 dark:text-gray-500">
            Atualizado em {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>

    @error('import')
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-semibold">Não foi possível importar a planilha.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach (explode("\n", $message) as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        </div>
    @enderror

    @error('file')
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="{{ $cardClass }} group p-5 transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:hover:border-indigo-500/40">
                <div class="flex items-start justify-between gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $tones[$card['tone']] ?? $tones['indigo'] }} text-white shadow-sm">
                        <x-icon :name="$card['icon']" class="h-5 w-5" />
                    </span>

                    <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ $card['hint'] }}</span>
                </div>

                <p class="mt-4 truncate text-xl font-semibold tracking-tight text-gray-900 sm:text-2xl dark:text-white">{{ $card['value'] }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-1 overflow-x-auto border-b border-gray-200 dark:border-white/10" role="tablist" aria-label="Seções do analytics">
        @foreach ($tabs as $key => $tab)
            <button
                type="button"
                role="tab"
                id="tab-{{ $key }}"
                data-tab="{{ $key }}"
                aria-controls="panel-{{ $key }}"
                aria-selected="{{ $key === 'overview' ? 'true' : 'false' }}"
                class="group relative inline-flex shrink-0 items-center gap-2 border-b-2 border-transparent px-3 py-2.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                {{ $tab['label'] }}

                @if ($tab['count'] !== null)
                    <span class="text-[10px] font-medium leading-none text-gray-400 dark:text-gray-500">{{ $tab['count'] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="mt-4">
        {{-- Visão geral --}}
        <div id="panel-overview" data-panel="overview" role="tabpanel" aria-labelledby="tab-overview" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <section class="{{ $cardClass }} p-5 xl:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h2 class="{{ $labelClass }}">Criações no período</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Projetos, ordens de serviço e documentos ({{ $days <= 31 ? 'por dia' : 'por mês' }}).</p>
                        </div>

                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $summary['projects']['created'] }} projetos · {{ $summary['work_orders']['created'] }} OS</span>
                    </div>

                    <div id="analytics-series" class="mt-4 h-72 w-full"></div>
                </section>

                <section class="{{ $cardClass }} p-5">
                    <h2 class="{{ $labelClass }}">Ordens por status</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $summary['work_orders']['total'] }} ordens no total</p>

                    <div class="relative mt-3 h-52 w-full">
                        <canvas id="analytics-work-orders"></canvas>
                    </div>

                    @if ($workOrderStatuses !== [])
                        <ul class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1.5">
                            @foreach ($workOrderStatuses as $row)
                                <li class="flex items-center justify-between gap-2 text-xs">
                                    <span class="flex min-w-0 items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                        <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $row['color'] }}"></span>
                                        <span class="truncate">{{ $row['label'] }}</span>
                                    </span>
                                    <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $row['value'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="{{ $cardClass }} p-5">
                    <h2 class="{{ $labelClass }}">Tarefas por lista</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $taskSplit['values'][0] ?? 0 }} pendentes · {{ $taskSplit['values'][1] ?? 0 }} concluídas</p>

                    <div class="relative mt-4 h-56 w-full">
                        <canvas id="analytics-tasks-by-list"></canvas>
                    </div>
                </section>

                <section class="{{ $cardClass }} p-5">
                    <h2 class="{{ $labelClass }}">Formulários por status</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $summary['forms']['total'] }} formulários no total</p>

                    <div class="relative mt-4 h-56 w-full">
                        <canvas id="analytics-forms"></canvas>
                    </div>
                </section>

                <section class="{{ $cardClass }} p-5">
                    <h2 class="{{ $labelClass }}">Agenda e projetos</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Eventos no período</p>

                            @forelse ($agendaStatuses as $row)
                                <div class="mt-2 flex items-center justify-between gap-2 text-sm">
                                    <span class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $row['color'] }}"></span>
                                        {{ $row['label'] }}
                                    </span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $row['value'] }}</span>
                                </div>
                            @empty
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum evento no período.</p>
                            @endforelse
                        </div>

                        <div class="border-t border-gray-100 pt-4 dark:border-white/10">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Projetos por status</p>

                            @forelse ($projectStatuses as $row)
                                <div class="mt-2 flex items-center justify-between gap-2 text-sm">
                                    <span class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $row['color'] }}"></span>
                                        {{ $row['label'] }}
                                    </span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $row['value'] }}</span>
                                </div>
                            @empty
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum projeto cadastrado.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <section class="{{ $cardClass }} p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="{{ $labelClass }}">Projetos com mais tarefas</h2>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $topProjects['labels'] === [] ? 'Sem dados' : count($topProjects['labels']).' projetos' }}</span>
                </div>

                @if ($topProjects['labels'] === [])
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nenhum projeto cadastrado.</p>
                @else
                    <ul class="mt-4 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                        @foreach ($topProjects['labels'] as $index => $projectLabel)
                            <li>
                                <div class="flex items-center justify-between gap-2 text-sm">
                                    <span class="min-w-0 truncate text-gray-600 dark:text-gray-300">{{ $projectLabel }}</span>
                                    <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $topProjects['values'][$index] }}</span>
                                </div>

                                <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ max(4, round(($topProjects['values'][$index] / $maxProjects) * 100)) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- FAMs --}}
        <div id="panel-fams" data-panel="fams" role="tabpanel" aria-labelledby="tab-fams" class="hidden space-y-4">
            @if (! is_array($recordsSummary))
                <div class="{{ $cardClass }} p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    A tabela de registros FAMs ainda não foi migrada.
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                    <section class="{{ $cardClass }} p-5">
                        <h2 class="{{ $labelClass }}">Importar dados FAMs</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">OC, OS, fornecedor, etapa, elemento, endereço, node name, localização e status. Reimportar cria uma nova versão quando houver alteração.</p>

                        <a href="{{ route('analytics.records.template') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            <x-icon name="arrow-down-tray" class="h-4 w-4" />
                            Baixar modelo (.xlsx)
                        </a>

                        <form method="POST" action="{{ route('analytics.records.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="period" value="{{ $days }}">
                            <input type="hidden" name="scope" value="{{ $scope }}">

                            <label for="records-file" class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-gray-50/60 px-4 py-6 text-center transition hover:border-indigo-300 hover:bg-indigo-50/40 dark:border-white/15 dark:bg-white/[0.02] dark:hover:border-indigo-500/40">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-indigo-600 shadow-sm dark:bg-white/10 dark:text-indigo-300">
                                    <x-icon name="arrow-up-tray" class="h-5 w-5" />
                                </span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Selecionar planilha</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">.xlsx, .xls ou .csv (até 10 MB)</span>
                                <input id="records-file" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only">
                            </label>

                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500">
                                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                                Importar FAMs
                            </button>
                        </form>

                        <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <span>{{ $recordsSummary['total'] }} registros</span>
                            <span>·</span>
                            <span>{{ $recordsSummary['versions'] }} versões</span>
                            <span>·</span>
                            <span>{{ $recordsSummary['in_period'] }} no período</span>
                        </div>
                    </section>

                    <section class="{{ $cardClass }} p-5">
                        <h2 class="{{ $labelClass }}">Etapas do processo</h2>

                        @if ($recordsByStage['labels'] === [])
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum registro FAMs importado.</p>
                        @else
                            <div class="relative mt-4 h-64 w-full">
                                <canvas id="analytics-records-stages"></canvas>
                            </div>
                        @endif
                    </section>

                    <section class="{{ $cardClass }} p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="{{ $labelClass }}">Status FAMs</h2>
                            <x-ui.badge tone="indigo">{{ $recordsSummary['total'] }}</x-ui.badge>
                        </div>

                        @if ($recordsByStatus['labels'] === [])
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum status registrado.</p>
                        @else
                            <ul class="mt-4 space-y-2">
                                @foreach ($recordsByStatus['labels'] as $index => $label)
                                    <li class="flex items-center justify-between gap-2 text-sm">
                                        <span class="min-w-0 truncate text-gray-600 dark:text-gray-300">{{ $label }}</span>
                                        <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $recordsByStatus['values'][$index] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($recordsBySupplier['labels'] !== [])
                            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-white/10">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Fornecedores</p>

                                <ul class="mt-2 space-y-1.5">
                                    @foreach ($recordsBySupplier['labels'] as $index => $label)
                                        <li class="flex items-center justify-between gap-2 text-sm">
                                            <span class="min-w-0 truncate text-gray-600 dark:text-gray-300">{{ $label }}</span>
                                            <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $recordsBySupplier['values'][$index] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </section>
                </div>

                @if ($recentRecords !== [])
                    <section class="{{ $cardClass }}">
                        <div class="flex flex-wrap items-center justify-between gap-2 p-5">
                            <h2 class="{{ $labelClass }}">Últimos registros FAMs</h2>
                            <span class="text-xs text-gray-400 dark:text-gray-500">versão mais recente de cada OC + OS</span>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                                <thead class="bg-gray-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">OC</th>
                                        <th class="px-5 py-3 font-semibold">OS</th>
                                        <th class="px-5 py-3 font-semibold">Criação</th>
                                        <th class="px-5 py-3 font-semibold">Fornecedor</th>
                                        <th class="px-5 py-3 font-semibold">Etapa</th>
                                        <th class="px-5 py-3 font-semibold">Local</th>
                                        <th class="px-5 py-3 font-semibold">Node</th>
                                        <th class="px-5 py-3 font-semibold">Status</th>
                                        <th class="px-5 py-3 font-semibold">Versões</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                    @foreach ($recentRecords as $record)
                                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $record['oc'] }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $record['os'] }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['created_on'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['supplier'] ?: '—' }}</td>
                                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['process_stage'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['city'] ?: '—' }}{{ $record['state'] ? ' / '.$record['state'] : '' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $record['node_name'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['status'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3">
                                                <x-ui.badge :tone="$record['versions'] > 1 ? 'indigo' : 'gray'">{{ $record['versions'] }}</x-ui.badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($recordChanges !== [])
                    <section class="{{ $cardClass }}">
                        <div class="p-5">
                            <h2 class="{{ $labelClass }}">Histórico de alterações FAMs</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Novas versões gravadas nas reimportações.</p>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                                <thead class="bg-gray-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Importado em</th>
                                        <th class="px-5 py-3 font-semibold">OC</th>
                                        <th class="px-5 py-3 font-semibold">OS</th>
                                        <th class="px-5 py-3 font-semibold">Etapa</th>
                                        <th class="px-5 py-3 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                    @foreach ($recordChanges as $change)
                                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['imported_at'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $change['oc'] }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $change['os'] }}</td>
                                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['process_stage'] ?: '—' }}</td>
                                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['status'] ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            @endif
        </div>

        {{-- AFL --}}
        <div id="panel-afl" data-panel="afl" role="tabpanel" aria-labelledby="tab-afl" class="hidden space-y-4">
            @if (! is_array($aflSummary))
                <div class="{{ $cardClass }} p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    A tabela de registros AFL ainda não foi migrada.
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                    <section class="{{ $cardClass }} p-5">
                        <h2 class="{{ $labelClass }}">Importar dados AFL</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Layout AFL completo (108 colunas): acesso, atividade, ativação, documentação, aceitação e faturamento. Reimportar cria uma nova versão quando houver alteração.</p>

                        <a href="{{ route('analytics.afl.template') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-rose-600 hover:text-rose-500 dark:text-rose-400">
                            <x-icon name="arrow-down-tray" class="h-4 w-4" />
                            Baixar modelo (.xlsx)
                        </a>

                        <form method="POST" action="{{ route('analytics.afl.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="period" value="{{ $days }}">
                            <input type="hidden" name="scope" value="{{ $scope }}">

                            <label for="afl-file" class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-gray-50/60 px-4 py-6 text-center transition hover:border-rose-300 hover:bg-rose-50/40 dark:border-white/15 dark:bg-white/[0.02] dark:hover:border-rose-500/40">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-rose-600 shadow-sm dark:bg-white/10 dark:text-rose-300">
                                    <x-icon name="arrow-up-tray" class="h-5 w-5" />
                                </span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Selecionar planilha</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">.xlsx, .xls ou .csv (até 20 MB)</span>
                                <input id="afl-file" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only">
                            </label>

                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white transition hover:bg-rose-500">
                                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                                Importar AFL
                            </button>
                        </form>

                        <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <span>{{ $aflSummary['total'] }} registros</span>
                            <span>·</span>
                            <span>{{ $aflSummary['versions'] }} versões</span>
                            <span>·</span>
                            <span>{{ $aflSummary['in_period'] }} no período</span>
                        </div>
                    </section>

                    <section class="{{ $cardClass }} p-5">
                        <h2 class="{{ $labelClass }}">Status geral AFL</h2>

                        @if ($aflByStatus['labels'] === [])
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum registro AFL importado.</p>
                        @else
                            <div class="relative mt-4 h-64 w-full">
                                <canvas id="analytics-afl-status"></canvas>
                            </div>
                        @endif
                    </section>

                    <section class="{{ $cardClass }} p-5">
                        <h2 class="{{ $labelClass }}">Faturamento AFL</h2>

                        @if ($aflByBillingStatus['labels'] === [])
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum status de faturamento registrado.</p>
                        @else
                            <ul class="mt-4 space-y-2">
                                @foreach ($aflByBillingStatus['labels'] as $index => $label)
                                    <li class="flex items-center justify-between gap-2 text-sm">
                                        <span class="min-w-0 truncate text-gray-600 dark:text-gray-300">{{ $label }}</span>
                                        <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $aflByBillingStatus['values'][$index] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($aflByProject['labels'] !== [])
                            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-white/10">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Projetos</p>

                                <ul class="mt-2 space-y-1.5">
                                    @foreach ($aflByProject['labels'] as $index => $label)
                                        <li class="flex items-center justify-between gap-2 text-sm">
                                            <span class="min-w-0 truncate text-gray-600 dark:text-gray-300">{{ $label }}</span>
                                            <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">{{ $aflByProject['values'][$index] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </section>
                </div>

                @if ($recentAflRecords !== [])
                    <section class="{{ $cardClass }}">
                        <div class="flex flex-wrap items-center justify-between gap-2 p-5">
                            <h2 class="{{ $labelClass }}">Últimos registros AFL</h2>
                            <span class="text-xs text-gray-400 dark:text-gray-500">versão mais recente de cada Cód_AFL</span>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                                <thead class="bg-gray-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Código</th>
                                        <th class="px-5 py-3 font-semibold">Projeto</th>
                                        <th class="px-5 py-3 font-semibold">Site A / B</th>
                                        <th class="px-5 py-3 font-semibold">Supervisor</th>
                                        <th class="px-5 py-3 font-semibold">Status geral</th>
                                        <th class="px-5 py-3 font-semibold">Faturamento</th>
                                        <th class="px-5 py-3 font-semibold">Versões</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                    @foreach ($recentAflRecords as $record)
                                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $record['code'] }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['project'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $record['site_a'] ?: '—' }} / {{ $record['site_b'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['supervisor'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['general_status'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $record['billing_status'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3">
                                                <x-ui.badge :tone="$record['versions'] > 1 ? 'indigo' : 'gray'">{{ $record['versions'] }}</x-ui.badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($aflChanges !== [])
                    <section class="{{ $cardClass }}">
                        <div class="p-5">
                            <h2 class="{{ $labelClass }}">Histórico de alterações AFL</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Novas versões gravadas nas reimportações.</p>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                                <thead class="bg-gray-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Importado em</th>
                                        <th class="px-5 py-3 font-semibold">Código</th>
                                        <th class="px-5 py-3 font-semibold">Status geral</th>
                                        <th class="px-5 py-3 font-semibold">Faturamento</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                    @foreach ($aflChanges as $change)
                                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                            <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['imported_at'] ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $change['code'] }}</td>
                                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['general_status'] ?: '—' }}</td>
                                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $change['billing_status'] ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            @endif
        </div>

        {{-- Snapshots --}}
        <div id="panel-snapshots" data-panel="snapshots" role="tabpanel" aria-labelledby="tab-snapshots" class="hidden space-y-4">
            <section class="{{ $cardClass }} p-5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h2 class="{{ $labelClass }}">Histórico de snapshots</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Evolução dos totais registrados dia a dia.</p>
                    </div>

                    @if ($hasSnapshotSeries)
                        <x-ui.badge tone="gray">{{ count($snapshotSeries['labels']) }} dias</x-ui.badge>
                    @endif
                </div>

                @if ($hasSnapshotSeries)
                    <div id="analytics-snapshots" class="mt-4 h-72 w-full"></div>
                @else
                    <div class="mt-4 flex flex-col items-start gap-3 rounded-xl border border-dashed border-gray-300 p-5 text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                        <p>Ainda não há histórico suficiente para o gráfico. Gere o snapshot diário para acompanhar a evolução.</p>

                        <form method="POST" action="{{ route('analytics.snapshot') }}">
                            @csrf
                            <input type="hidden" name="period" value="{{ $days }}">
                            <input type="hidden" name="scope" value="{{ $scope }}">

                            <x-ui.action type="submit" icon="camera">Gerar snapshot de hoje</x-ui.action>
                        </form>

                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Ou agende <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-white/[0.06]">php artisan analytics:snapshot</code> no cron.
                        </p>
                    </div>
                @endif
            </section>

            @if ($snapshots !== [])
                <section class="{{ $cardClass }}">
                    <div class="p-5">
                        <h2 class="{{ $labelClass }}">Últimos snapshots</h2>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                            <thead class="bg-gray-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Data</th>
                                    <th class="px-5 py-3 font-semibold">Projetos</th>
                                    <th class="px-5 py-3 font-semibold">OS</th>
                                    <th class="px-5 py-3 font-semibold">Tarefas</th>
                                    <th class="px-5 py-3 font-semibold">Documentos</th>
                                    <th class="px-5 py-3 font-semibold">Formulários</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                @foreach ($snapshots as $snapshot)
                                    <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                        <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $snapshot['captured_on'] }}</td>
                                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['projects']['total'] ?? 0 }}</td>
                                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['work_orders']['total'] ?? 0 }}</td>
                                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['tasks']['total'] ?? 0 }}</td>
                                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['documents']['total'] ?? 0 }}</td>
                                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $snapshot['metrics']['forms']['total'] ?? 0 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@4/dist/apexcharts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    <script>
        (function () {
            const series = @json($series);
            const workOrderStatuses = @json($workOrderStatuses);
            const tasksByList = @json($tasksByList);
            const formStatuses = @json($formStatuses);
            const snapshotSeries = @json($snapshotSeries);
            const recordsByStage = @json($recordsByStage);
            const aflByStatus = @json($aflByStatus);

            const isDark = document.documentElement.classList.contains('dark');
            const labelColor = isDark ? '#d1d5db' : '#6b7280';
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
            const rendered = new Set();

            const baseOptions = {
                chart: {
                    height: '100%',
                    width: '100%',
                    fontFamily: 'inherit',
                    foreColor: labelColor,
                    toolbar: { show: false },
                    animations: { enabled: false },
                },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                dataLabels: { enabled: false },
                tooltip: { theme: isDark ? 'dark' : 'light' },
            };

            const apexArea = (elementId, datasets, categories, colors) => {
                const element = document.getElementById(elementId);

                if (!element) {
                    return;
                }

                new ApexCharts(element, {
                    ...baseOptions,
                    chart: { ...baseOptions.chart, type: 'area' },
                    series: datasets,
                    xaxis: {
                        categories,
                        tickAmount: 6,
                        labels: { rotate: 0, hideOverlappingLabels: true },
                    },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.04 } },
                    colors,
                    legend: { position: 'bottom', horizontalAlign: 'left' },
                }).render();
            };

            const chartJs = (elementId, config) => {
                const element = document.getElementById(elementId);

                if (!element) {
                    return;
                }

                new Chart(element, config);
            };

            const renderers = {
                overview() {
                    if (typeof ApexCharts !== 'undefined') {
                        apexArea('analytics-series', [
                            { name: 'Projetos', data: series.projects },
                            { name: 'Ordens de serviço', data: series.work_orders },
                            { name: 'Documentos', data: series.documents },
                        ], series.labels, ['#6366f1', '#0ea5e9', '#10b981']);
                    }

                    if (typeof Chart === 'undefined') {
                        return;
                    }

                    Chart.defaults.color = labelColor;
                    Chart.defaults.borderColor = gridColor;
                    Chart.defaults.font.family = 'inherit';

                    chartJs('analytics-work-orders', {
                        type: 'doughnut',
                        data: {
                            labels: workOrderStatuses.length ? workOrderStatuses.map((row) => row.label) : ['Sem dados'],
                            datasets: [{
                                data: workOrderStatuses.length ? workOrderStatuses.map((row) => row.value) : [1],
                                backgroundColor: workOrderStatuses.length ? workOrderStatuses.map((row) => row.color) : ['#e5e7eb'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '64%',
                            plugins: { legend: { display: false } },
                        },
                    });

                    chartJs('analytics-tasks-by-list', {
                        type: 'bar',
                        data: {
                            labels: tasksByList.labels,
                            datasets: [{ data: tasksByList.values, backgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 34 }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                            },
                        },
                    });

                    chartJs('analytics-forms', {
                        type: 'doughnut',
                        data: {
                            labels: formStatuses.length ? formStatuses.map((row) => row.label) : ['Sem dados'],
                            datasets: [{
                                data: formStatuses.length ? formStatuses.map((row) => row.value) : [1],
                                backgroundColor: formStatuses.length ? formStatuses.map((row) => row.color) : ['#e5e7eb'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '64%',
                            plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, padding: 12 } } },
                        },
                    });
                },

                fams() {
                    if (typeof Chart === 'undefined') {
                        return;
                    }

                    chartJs('analytics-records-stages', {
                        type: 'bar',
                        data: {
                            labels: recordsByStage.labels,
                            datasets: [{ data: recordsByStage.values, backgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 30 }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0 } },
                                y: {
                                    ticks: {
                                        autoSkip: false,
                                        callback(value) {
                                            const label = this.getLabelForValue(value);
                                            return label.length > 24 ? label.slice(0, 24) + '…' : label;
                                        },
                                    },
                                },
                            },
                        },
                    });
                },

                afl() {
                    if (typeof Chart === 'undefined') {
                        return;
                    }

                    chartJs('analytics-afl-status', {
                        type: 'bar',
                        data: {
                            labels: aflByStatus.labels,
                            datasets: [{ data: aflByStatus.values, backgroundColor: '#f43f5e', borderRadius: 6, maxBarThickness: 30 }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0 } },
                                y: {
                                    ticks: {
                                        autoSkip: false,
                                        callback(value) {
                                            const label = this.getLabelForValue(value);
                                            return label.length > 26 ? label.slice(0, 26) + '…' : label;
                                        },
                                    },
                                },
                            },
                        },
                    });
                },

                snapshots() {
                    if (typeof ApexCharts === 'undefined' || !snapshotSeries || snapshotSeries.labels.length < 2) {
                        return;
                    }

                    apexArea('analytics-snapshots', [
                        { name: 'Projetos', data: snapshotSeries.projects },
                        { name: 'Ordens de serviço', data: snapshotSeries.work_orders },
                    ], snapshotSeries.labels, ['#6366f1', '#0ea5e9']);
                },
            };

            const tabs = [...document.querySelectorAll('[data-tab]')];
            const panels = [...document.querySelectorAll('[data-panel]')];

            const activate = (name, updateHash = true) => {
                tabs.forEach((tab) => {
                    const active = tab.dataset.tab === name;
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                    tab.classList.toggle('border-indigo-600', active);
                    tab.classList.toggle('text-gray-900', active);
                    tab.classList.toggle('dark:text-white', active);
                    tab.classList.toggle('border-transparent', !active);
                    tab.classList.toggle('text-gray-500', !active);
                });

                panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.panel !== name));

                if (updateHash) {
                    history.replaceState(null, '', '#' + name);
                }

                if (!rendered.has(name)) {
                    try {
                        renderers[name]?.();
                    } catch (error) {
                        console.error('Analytics: falha ao renderizar a aba ' + name, error);
                    }

                    rendered.add(name);
                }
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => activate(tab.dataset.tab));

                tab.addEventListener('keydown', (event) => {
                    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                        return;
                    }

                    event.preventDefault();

                    const index = tabs.indexOf(tab);
                    const next = event.key === 'ArrowRight'
                        ? (index + 1) % tabs.length
                        : (index - 1 + tabs.length) % tabs.length;

                    tabs[next].focus();
                    activate(tabs[next].dataset.tab);
                });
            });

            const initial = window.location.hash.replace('#', '');
            const names = panels.map((panel) => panel.dataset.panel);

            activate(names.includes(initial) ? initial : 'overview', false);
        })();
    </script>
</x-layouts.admin>
