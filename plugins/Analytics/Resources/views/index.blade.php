@php
    $cardClass = 'min-w-0 overflow-hidden rounded-2xl border border-gray-200/70 bg-white dark:border-white/10 dark:bg-[#171716]';
    $labelClass = 'text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500';

    $cards = [
        ['label' => 'Projetos ativos', 'value' => $summary['projects']['active'], 'hint' => $summary['projects']['total'].' no total', 'icon' => 'briefcase', 'tone' => 'indigo'],
        ['label' => 'OS em aberto', 'value' => $summary['work_orders']['open'], 'hint' => $summary['work_orders']['total'].' no total', 'icon' => 'clipboard-document-list', 'tone' => 'sky'],
        ['label' => 'Tarefas pendentes', 'value' => $summary['tasks']['pending'], 'hint' => $summary['tasks']['completed'].' concluídas', 'icon' => 'check-circle', 'tone' => 'emerald'],
        ['label' => 'Documentos no período', 'value' => $summary['documents']['in_period'], 'hint' => $summary['documents']['total'].' no total', 'icon' => 'document-text', 'tone' => 'violet'],
        ['label' => 'Armazenamento', 'value' => \Illuminate\Support\Number::fileSize($summary['documents']['size']), 'hint' => 'Documentos e anexos', 'icon' => 'rectangle-stack', 'tone' => 'slate', 'compact' => true],
        ['label' => 'Formulários pendentes', 'value' => $summary['forms']['pending'], 'hint' => $summary['forms']['total'].' no total', 'icon' => 'document-check', 'tone' => 'amber'],
        ['label' => 'Eventos no período', 'value' => $summary['agenda']['in_period'], 'hint' => $summary['agenda']['total'].' na agenda', 'icon' => 'calendar-days', 'tone' => 'rose'],
    ];

    if (is_array($summary['stations'])) {
        $cards[] = ['label' => 'Stations ativas', 'value' => $summary['stations']['active'], 'hint' => $summary['stations']['total'].' no total', 'icon' => 'signal', 'tone' => 'teal'];
    }

    $toneClasses = [
        'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        'sky' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
        'teal' => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300',
    ];

    $maxProjects = max(1, ...($topProjects['values'] ?: [1]));
    $hasSnapshotSeries = is_array($snapshotSeries) && count($snapshotSeries['labels']) >= 2;
@endphp

<x-layouts.admin title="Analytics">
    <x-ui.page-header
        category="Inteligência"
        title="Analytics"
        :description="'Indicadores de '.$from->format('d/m/Y').' a '.$to->format('d/m/Y').' · '.($isGlobal ? 'visão global' : 'seus dados')"
        icon="chart-bar"
    >
        <x-ui.action :href="route('analytics.export', array_filter(['period' => $days, 'scope' => $scope === 'global' ? 'global' : null]))" variant="secondary" icon="arrow-down-tray">
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

    <section class="{{ $cardClass }} mt-6 flex flex-wrap items-center gap-3 p-4">
        <form method="GET" action="{{ route('analytics.index') }}" class="flex flex-wrap items-center gap-2">
            <div>
                <label for="period" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Período</label>
                <select id="period" name="period" class="mt-1 h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300" onchange="this.form.submit()">
                    @foreach ($periods as $option)
                        <option value="{{ $option }}" @selected(! request('from') && $days === (int) $option)>Últimos {{ $option }} dias</option>
                    @endforeach
                </select>
            </div>

            @if ($scope === 'global')
                <input type="hidden" name="scope" value="global">
            @endif

            <button type="submit" class="mt-5 inline-flex h-10 items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300">
                <x-icon name="arrow-path" class="h-4 w-4" />
                Aplicar
            </button>
        </form>

        @if (auth()->user()?->is_admin)
            <div class="mt-5 inline-flex rounded-xl border border-gray-200 p-1 dark:border-white/10" role="group" aria-label="Escopo">
                <a
                    href="{{ route('analytics.index', ['period' => $days]) }}"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $scope === 'user' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                >
                    Meus dados
                </a>
                <a
                    href="{{ route('analytics.index', ['period' => $days, 'scope' => 'global']) }}"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $scope === 'global' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                >
                    Global
                </a>
            </div>
        @endif
    </section>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="{{ $cardClass }} p-5">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl {{ $toneClasses[$card['tone']] ?? $toneClasses['indigo'] }}">
                    <x-icon :name="$card['icon']" class="h-5 w-5" />
                </span>
                <p class="mt-4 truncate text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $card['value'] }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <section class="{{ $cardClass }} p-5 xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="{{ $labelClass }}">Criações no período</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Projetos, ordens de serviço e documentos criados ({{ $days <= 31 ? 'por dia' : 'por mês' }}).</p>
                </div>

                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $summary['projects']['created'] }} projetos · {{ $summary['work_orders']['created'] }} OS</span>
            </div>

            <div id="analytics-series" class="mt-4 h-72 w-full"></div>
        </section>

        <section class="{{ $cardClass }} p-5">
            <h2 class="{{ $labelClass }}">Ordens por status</h2>

            <div class="relative mt-4 h-56 w-full">
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
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $summary['tasks']['pending'] }} pendentes · {{ $summary['tasks']['completed'] }} concluídas</p>

            <div class="relative mt-4 h-64 w-full">
                <canvas id="analytics-tasks-by-list"></canvas>
            </div>
        </section>

        <section class="{{ $cardClass }} p-5">
            <h2 class="{{ $labelClass }}">Formulários por status</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $summary['forms']['total'] }} formulários no total</p>

            <div class="relative mt-4 h-64 w-full">
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

        <section class="{{ $cardClass }} p-5 xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="{{ $labelClass }}">Histórico de snapshots</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Evolução dos totais registrados dia a dia.</p>
                </div>

                @if ($hasSnapshotSeries)
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ count($snapshotSeries['labels']) }} snapshots</span>
                @endif
            </div>

            @if ($hasSnapshotSeries)
                <div id="analytics-snapshots" class="mt-4 h-64 w-full"></div>
            @else
                <div class="mt-4 rounded-xl border border-dashed border-gray-300 p-5 text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                    Ainda não há histórico suficiente para o gráfico. Use o botão <strong>Snapshot de hoje</strong> diariamente
                    ou agende <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-white/[0.06]">php artisan analytics:snapshot</code>
                    no cron para acompanhar a evolução.
                </div>
            @endif
        </section>

        <section class="{{ $cardClass }} p-5">
            <h2 class="{{ $labelClass }}">Projetos com mais tarefas</h2>

            @if ($topProjects['labels'] === [])
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum projeto cadastrado.</p>
            @else
                <ul class="mt-4 space-y-3">
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

    @if ($snapshots !== [])
        <section class="{{ $cardClass }} mt-4">
            <div class="p-5">
                <h2 class="{{ $labelClass }}">Últimos snapshots</h2>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
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
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
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

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@4/dist/apexcharts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    <script>
        (function () {
            const series = @json($series);
            const workOrderStatuses = @json($workOrderStatuses);
            const tasksByList = @json($tasksByList);
            const formStatuses = @json($formStatuses);
            const snapshotSeries = @json($snapshotSeries);

            const isDark = document.documentElement.classList.contains('dark');
            const labelColor = isDark ? '#d1d5db' : '#6b7280';
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';

            const baseOptions = {
                chart: {
                    type: 'area',
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

            const init = () => {
                try {
                    renderApex();
                } catch (error) {
                    console.error('Analytics: falha ao renderizar os gráficos ApexCharts', error);
                }

                try {
                    renderChartJs();
                } catch (error) {
                    console.error('Analytics: falha ao renderizar os gráficos Chart.js', error);
                }
            };

            const renderApex = () => {
                if (typeof ApexCharts === 'undefined') {
                    return;
                }

                const creationChart = document.getElementById('analytics-series');

                if (creationChart) {
                    new ApexCharts(creationChart, {
                        ...baseOptions,
                        series: [
                            { name: 'Projetos', data: series.projects },
                            { name: 'Ordens de serviço', data: series.work_orders },
                            { name: 'Documentos', data: series.documents },
                        ],
                        xaxis: {
                            categories: series.labels,
                            tickAmount: 6,
                            labels: { rotate: 0, hideOverlappingLabels: true },
                        },
                        stroke: { curve: 'smooth', width: 2 },
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.05 } },
                        colors: ['#6366f1', '#0ea5e9', '#10b981'],
                        legend: { position: 'bottom', horizontalAlign: 'left' },
                    }).render();
                }

                const snapshotChart = document.getElementById('analytics-snapshots');

                if (snapshotChart && snapshotSeries && snapshotSeries.labels.length > 1) {
                    new ApexCharts(snapshotChart, {
                        ...baseOptions,
                        chart: { ...baseOptions.chart, type: 'line' },
                        series: [
                            { name: 'Projetos', data: snapshotSeries.projects },
                            { name: 'Ordens de serviço', data: snapshotSeries.work_orders },
                        ],
                        xaxis: { categories: snapshotSeries.labels },
                        stroke: { curve: 'smooth', width: 2 },
                        colors: ['#6366f1', '#0ea5e9'],
                        legend: { position: 'bottom' },
                    }).render();
                }
            };

            const renderChartJs = () => {
                if (typeof Chart === 'undefined') {
                    return;
                }

                Chart.defaults.color = labelColor;
                Chart.defaults.borderColor = gridColor;
                Chart.defaults.font.family = 'inherit';

                const workOrders = document.getElementById('analytics-work-orders');

                if (workOrders) {
                    new Chart(workOrders, {
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
                            cutout: '62%',
                            plugins: { legend: { display: false } },
                        },
                    });
                }

                const tasks = document.getElementById('analytics-tasks-by-list');

                if (tasks) {
                    new Chart(tasks, {
                        type: 'bar',
                        data: {
                            labels: tasksByList.labels,
                            datasets: [{ data: tasksByList.values, backgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 36 }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: { padding: { top: 8 } },
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                            },
                        },
                    });
                }

                const forms = document.getElementById('analytics-forms');

                if (forms) {
                    new Chart(forms, {
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
                            cutout: '62%',
                            plugins: { legend: { position: 'bottom' } },
                        },
                    });
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
</x-layouts.admin>
