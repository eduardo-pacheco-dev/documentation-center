@php
    $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');

    $stats = [
        ['module' => 'clients', 'label' => 'Clientes', 'value' => $counts['clients'], 'icon' => 'building-office', 'url' => route('admin.clients.index'), 'caption' => 'Cadastrados na base'],
        ['module' => 'work-orders', 'label' => 'OS abertas', 'value' => $counts['openWorkOrders'], 'icon' => 'clipboard-document-list', 'url' => route('admin.work-orders.index', ['status' => 'open']), 'caption' => 'Aguardando atendimento'],
        ['module' => 'projects', 'label' => 'Projetos ativos', 'value' => $counts['projects'], 'icon' => 'briefcase', 'url' => route('admin.projects.index'), 'caption' => 'Em execução'],
        ['module' => 'files', 'label' => 'Arquivos', 'value' => $counts['documents'], 'icon' => 'document-text', 'url' => route('admin.files.index'), 'caption' => 'Documentos armazenados'],
    ];

    $enabledModules = auth()->user()->enabledModules();
    $stats = array_values(array_filter($stats, fn (array $stat): bool => in_array($stat['module'], $enabledModules, true)));

    $statusBreakdown = [
        ['key' => 'open', 'label' => 'Aberta', 'count' => $statusCounts['open'], 'dot' => 'bg-blue-500', 'stroke' => 'stroke-blue-500'],
        ['key' => 'in_progress', 'label' => 'Em andamento', 'count' => $statusCounts['in_progress'], 'dot' => 'bg-amber-500', 'stroke' => 'stroke-amber-500'],
        ['key' => 'on_hold', 'label' => 'Em espera', 'count' => $statusCounts['on_hold'], 'dot' => 'bg-violet-500', 'stroke' => 'stroke-violet-500'],
        ['key' => 'completed', 'label' => 'Concluída', 'count' => $statusCounts['completed'], 'dot' => 'bg-emerald-500', 'stroke' => 'stroke-emerald-500'],
        ['key' => 'cancelled', 'label' => 'Cancelada', 'count' => $statusCounts['cancelled'], 'dot' => 'bg-gray-400', 'stroke' => 'stroke-gray-400'],
    ];

    $completionRate = $workOrdersTotal > 0
        ? (int) round($statusCounts['completed'] / $workOrdersTotal * 100)
        : 0;

    $circumference = 2 * M_PI * 40;
    $donutSegments = [];
    $donutOffset = 0.0;

    foreach ($statusBreakdown as $status) {
        if ($status['count'] <= 0) {
            continue;
        }

        $length = $status['count'] / max($workOrdersTotal, 1) * $circumference;

        $donutSegments[] = [
            'label' => $status['label'],
            'count' => $status['count'],
            'stroke' => $status['stroke'],
            'length' => round($length, 2),
            'offset' => round(-$donutOffset, 2),
        ];

        $donutOffset += $length;
    }

    $chartPeak = max(1, collect($monthlyWorkOrders)->max('count'));
    $axisStep = max(1, (int) ceil($chartPeak / 4));
    $axisMax = $axisStep * 4;
    $chartTotal = collect($monthlyWorkOrders)->sum('count');

    $quickActions = [
        ['module' => 'work-orders', 'label' => 'Nova ordem de serviço', 'icon' => 'clipboard-document-list', 'url' => route('admin.work-orders.create')],
        ['module' => 'clients', 'label' => 'Novo cliente', 'icon' => 'building-office', 'url' => route('admin.clients.create')],
        ['module' => 'projects', 'label' => 'Novo projeto', 'icon' => 'briefcase', 'url' => route('admin.projects.create')],
        ['module' => 'files', 'label' => 'Enviar arquivo', 'icon' => 'arrow-up-tray', 'url' => route('admin.files.index')],
    ];

    $quickActions = array_values(array_filter($quickActions, fn (array $action): bool => in_array($action['module'], $enabledModules, true)));
@endphp

<x-layouts.dashboard title="Dashboard">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">Visão geral</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $greeting }}</h1>
            <p class="mt-1.5 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                Acompanhe os indicadores, as ordens de serviço e os arquivos recentes do seu dia.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-xs font-medium text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <x-icon name="calendar-days" class="h-4 w-4" />
                {{ now()->locale('pt_BR')->translatedFormat('d \d\e M, Y') }}
            </span>
            @if (auth()->user()->hasModule('work-orders'))
                <a
                    href="{{ route('admin.work-orders.create') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-gray-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Nova OS
                </a>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <a
                href="{{ $stat['url'] }}"
                data-stat="{{ \Illuminate\Support\Str::slug($stat['label']) }}"
                class="group relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04)] transition duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-lg hover:shadow-gray-900/5 dark:border-white/10 dark:bg-[#171716] dark:hover:border-white/20 dark:hover:shadow-black/20"
            >
                <div class="flex items-start justify-between">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300">
                        <x-icon :name="$stat['icon']" class="h-5 w-5" />
                    </span>
                    <x-icon name="arrow-top-right-on-square" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-300" />
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $stat['label'] }}</p>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $stat['caption'] }}</p>
            </a>
        @endforeach
    </div>

    @if (auth()->user()->hasModule('work-orders'))
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            <section class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04)] sm:p-6 lg:col-span-2 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ordens de serviço</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Volume dos últimos 6 meses</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">
                        <x-icon name="chart-bar" class="h-3.5 w-3.5" />
                        {{ number_format($chartTotal, 0, ',', '.') }} no período
                    </span>
                    <a href="{{ route('admin.work-orders.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Ver todas
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <div class="flex w-7 shrink-0 flex-col justify-between pb-6 text-right text-[10px] tabular-nums text-gray-400 dark:text-gray-500">
                    <span>{{ $axisMax }}</span>
                    <span>{{ (int) ($axisMax * 0.75) }}</span>
                    <span>{{ (int) ($axisMax * 0.5) }}</span>
                    <span>{{ (int) ($axisMax * 0.25) }}</span>
                    <span>0</span>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="relative h-48 sm:h-56">
                        <div class="pointer-events-none absolute inset-0 flex flex-col justify-between pb-0">
                            @for ($line = 0; $line < 5; $line++)
                                <div class="border-t border-dashed border-gray-100 dark:border-white/10"></div>
                            @endfor
                        </div>

                        <div class="relative flex h-full items-end gap-2 sm:gap-4">
                            @foreach ($monthlyWorkOrders as $month)
                                @php
                                    $height = $month['count'] > 0
                                        ? max(2, (int) round($month['count'] / $axisMax * 100))
                                        : 0;
                                @endphp
                                <div class="group relative flex h-full flex-1 items-end">
                                    <div
                                        class="w-full rounded-t-md bg-gray-900 transition-colors duration-200 group-hover:bg-gray-600 dark:bg-white dark:group-hover:bg-gray-300"
                                        style="height: {{ $height }}%"
                                    ></div>
                                    <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-white dark:text-gray-900">
                                        {{ $month['count'] }} OS
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-2 flex gap-2 sm:gap-4">
                        @foreach ($monthlyWorkOrders as $month)
                            <span class="flex-1 text-center text-[11px] font-medium capitalize text-gray-400 dark:text-gray-500">{{ $month['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="h-2 w-2 rounded-full bg-gray-900 dark:bg-white"></span>
                    Ordens criadas por mês
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Taxa de conclusão</span>
                    <div class="h-1.5 w-32 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $completionRate }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $completionRate }}%</span>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04)] sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Status das OS</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Distribuição atual</p>
                </div>
                <a href="{{ route('admin.work-orders.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Detalhes</a>
            </div>

            <div class="relative mx-auto mt-5 h-36 w-36">
                <svg viewBox="0 0 100 100" class="h-36 w-36 -rotate-90">
                    <circle cx="50" cy="50" r="40" fill="none" stroke-width="11" class="stroke-gray-100 dark:stroke-white/10" />
                    @foreach ($donutSegments as $segment)
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            fill="none"
                            stroke-width="11"
                            stroke-linecap="butt"
                            class="{{ $segment['stroke'] }}"
                            stroke-dasharray="{{ $segment['length'] }} {{ round($circumference, 2) }}"
                            stroke-dashoffset="{{ $segment['offset'] }}"
                        >
                            <title>{{ $segment['label'] }}: {{ $segment['count'] }}</title>
                        </circle>
                    @endforeach
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($workOrdersTotal, 0, ',', '.') }}</span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total</span>
                </div>
            </div>

            <div class="mt-5 space-y-1">
                @foreach ($statusBreakdown as $status)
                    <a
                        href="{{ route('admin.work-orders.index', ['status' => $status['key']]) }}"
                        class="flex items-center gap-2.5 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
                    >
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $status['dot'] }}"></span>
                        <span class="truncate text-xs text-gray-600 dark:text-gray-300">{{ $status['label'] }}</span>
                        <span class="ml-auto text-xs font-semibold tabular-nums text-gray-900 dark:text-white">{{ $status['count'] }}</span>
                        <span class="w-9 text-right text-[11px] tabular-nums text-gray-400 dark:text-gray-500">
                            {{ $workOrdersTotal > 0 ? round($status['count'] / $workOrdersTotal * 100) : 0 }}%
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
        </div>
    @endif

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        @if (auth()->user()->hasModule('work-orders'))
            <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04)] lg:col-span-2 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ordens de serviço recentes</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Últimos registros criados</p>
                </div>
                <a href="{{ route('admin.work-orders.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Ver todas
                    <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                </a>
            </div>

            @if ($recentWorkOrders->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma ordem de serviço cadastrada.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/60 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:border-white/10 dark:bg-white/[0.02] dark:text-gray-500">
                                <th scope="col" class="px-5 py-2.5 font-semibold">OS</th>
                                <th scope="col" class="px-3 py-2.5 font-semibold">Cliente</th>
                                <th scope="col" class="px-3 py-2.5 font-semibold">Status</th>
                                <th scope="col" class="hidden px-3 py-2.5 text-right font-semibold sm:table-cell">Valor</th>
                                <th scope="col" class="hidden px-5 py-2.5 text-right font-semibold md:table-cell">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($recentWorkOrders as $workOrder)
                                <tr class="group transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="text-sm font-semibold text-gray-900 hover:underline dark:text-white">
                                            {{ $workOrder->number }}
                                        </a>
                                    </td>
                                    <td class="max-w-[12rem] truncate px-3 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $workOrder->client?->name ?? 'Sem cliente' }}</td>
                                    <td class="px-3 py-3"><x-work-order-status :work-order="$workOrder" /></td>
                                    <td class="hidden whitespace-nowrap px-3 py-3 text-right text-sm font-medium tabular-nums text-gray-700 sm:table-cell dark:text-gray-200">{{ $money((float) $workOrder->total) }}</td>
                                    <td class="hidden whitespace-nowrap px-5 py-3 text-right text-xs text-gray-400 md:table-cell dark:text-gray-500">{{ $workOrder->created_at->translatedFormat('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            </section>
        @endif

        <div class="flex flex-col gap-4">
            @if (auth()->user()->hasModule('files'))
                <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04)] dark:border-white/10 dark:bg-[#171716]">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Arquivos recentes</h2>
                        <a href="{{ route('admin.files.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Ver todos</a>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse ($recentDocuments as $document)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <x-file-type-icon :name="$document->original_name" size="xs" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $document->original_name }}">{{ $document->original_name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ Number::fileSize($document->size) }}</p>
                                </div>
                                <x-relative-time :value="$document->created_at" class="shrink-0 text-[11px] text-gray-400 dark:text-gray-500" />
                            </div>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum arquivo enviado.</p>
                        @endforelse
                    </div>
                </section>
            @endif

            @if (count($quickActions) > 0)
                <section class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04)] dark:border-white/10 dark:bg-[#171716]">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ações rápidas</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach ($quickActions as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="flex flex-col gap-2 rounded-xl border border-gray-100 bg-gray-50/60 p-3 text-xs font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white hover:text-gray-900 hover:shadow-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:border-white/20 dark:hover:bg-white/5 dark:hover:text-white"
                        >
                            <x-icon :name="$action['icon']" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
