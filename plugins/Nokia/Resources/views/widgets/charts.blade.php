@php
    $statusColors = collect($statuses)->pluck('color', 'key')->all();

    $donutItems = collect($statuses)->filter(fn (array $item): bool => $item['count'] > 0)->values();
    $donutStops = [];
    $cursor = 0;

    foreach ($donutItems as $index => $item) {
        $end = $index === $donutItems->count() - 1 ? 100 : min(100, $cursor + $item['percent']);
        $donutStops[] = $item['color'].' '.$cursor.'% '.$end.'%';
        $cursor = $end;
    }

    $donutBackground = $donutStops === []
        ? 'conic-gradient(#e5e7eb 0% 100%)'
        : 'conic-gradient('.implode(', ', $donutStops).')';

    $kpis = [
        ['key' => 'sites', 'label' => 'Site ID', 'value' => $totals['sites'], 'tone' => 'slate', 'suffix' => ''],
        ['key' => 'accepted', 'label' => 'Aceitos', 'value' => $totals['accepted'], 'tone' => 'emerald', 'suffix' => ''],
        ['key' => 'analysis', 'label' => 'Análise TIM', 'value' => $totals['analysis'], 'tone' => 'amber', 'suffix' => ''],
        ['key' => 'first_send', 'label' => 'Primeiro Envio', 'value' => $totals['first_send'], 'tone' => 'orange', 'suffix' => ''],
        ['key' => 'rejected', 'label' => 'Rejeitado', 'value' => $totals['rejected'], 'tone' => 'red', 'suffix' => ''],
        ['key' => 'no_os', 'label' => 'Sem OS MOS', 'value' => $totals['no_os'], 'tone' => 'gray', 'suffix' => ''],
    ];

    $kpiTones = [
        'slate' => ['bar' => 'bg-slate-400', 'value' => 'text-gray-900 dark:text-white'],
        'emerald' => ['bar' => 'bg-emerald-500', 'value' => 'text-emerald-600 dark:text-emerald-400'],
        'amber' => ['bar' => 'bg-amber-500', 'value' => 'text-amber-600 dark:text-amber-400'],
        'orange' => ['bar' => 'bg-orange-500', 'value' => 'text-orange-600 dark:text-orange-400'],
        'red' => ['bar' => 'bg-red-500', 'value' => 'text-red-600 dark:text-red-400'],
        'gray' => ['bar' => 'bg-gray-400', 'value' => 'text-gray-600 dark:text-gray-300'],
    ];

    $insightTones = [
        'success' => ['icon' => 'check', 'class' => 'text-emerald-600 dark:text-emerald-400'],
        'warning' => ['icon' => 'exclamation-triangle', 'class' => 'text-amber-600 dark:text-amber-400'],
        'danger' => ['icon' => 'x-mark', 'class' => 'text-red-600 dark:text-red-400'],
        'info' => ['icon' => 'exclamation-circle', 'class' => 'text-indigo-600 dark:text-indigo-400'],
    ];

    $maxAging = max(1, collect($aging)->max('count'));

    $gaugeLength = 125.6;
    $acceptanceOffset = $gaugeLength * (1 - min(100, $totals['acceptance']) / 100);
    $projectedOffset = $gaugeLength * (1 - min(100, $totals['projected']) / 100);
@endphp

<section class="flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="chart-bar" class="h-4 w-4" />
            </span>

            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Projetos Nokia</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Aceitação, status das FAMs, aging e desempenho operacional.</p>
            </div>
        </div>

        <a href="{{ route('admin.nokia.sites.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
            Ver sites
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        </a>
    </div>

    @if ($totals['sites'] === 0)
        <div class="flex flex-1 items-center justify-center px-5 py-10 text-center">
            <div>
                <span class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 dark:bg-white/5 dark:text-gray-500">
                    <x-icon name="chart-bar" class="h-5 w-5" />
                </span>
                <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Ainda não há dados para os indicadores</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cadastre ou importe sites para montar o painel Nokia.</p>
            </div>
        </div>
    @else
        <div class="space-y-4 p-5">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
                @foreach ($kpis as $kpi)
                    <div class="relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white px-3.5 py-3 dark:border-white/10 dark:bg-white/[0.02]">
                        <span class="absolute inset-x-0 top-0 h-1 {{ $kpiTones[$kpi['tone']]['bar'] }}"></span>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400 dark:text-gray-500">{{ $kpi['label'] }}</p>
                        <p class="mt-1 text-2xl font-semibold tracking-tight tabular-nums {{ $kpiTones[$kpi['tone']]['value'] }}">{{ $kpi['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                {{-- Regional --}}
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Regional</h3>
                        <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">{{ count($regional) }} grupo(s)</span>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1">
                        @foreach ($statuses as $status)
                            @if ($status['count'] > 0)
                                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-gray-500 dark:text-gray-400">
                                    <span class="h-2 w-2 rounded-full" style="background-color: {{ $status['color'] }};"></span>
                                    {{ $status['label'] }}
                                </span>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-3 space-y-3">
                        @foreach ($regional as $item)
                            <div>
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $item['label'] }}</span>
                                    <span class="shrink-0 tabular-nums text-gray-400 dark:text-gray-500">{{ $item['accepted'] }}/{{ $item['total'] }} · {{ number_format($item['percent'], 1, ',', '.') }}%</span>
                                </div>

                                <div class="mt-1 flex h-6 w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-white/10">
                                    @foreach ($item['statuses'] as $status)
                                        @if ($status['count'] > 0)
                                            <div
                                                class="h-full border-r border-white/40 last:border-r-0 dark:border-black/20"
                                                style="width: {{ ($status['count'] / max(1, $item['total'])) * 100 }}%; background-color: {{ $statusColors[$status['key']] }};"
                                                title="{{ $status['label'] }}: {{ $status['count'] }}"
                                            ></div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Status FAM --}}
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Status FAM</h3>

                    <div class="mt-4 flex items-center gap-5">
                        <div class="relative h-28 w-28 shrink-0 rounded-full" style="background: {{ $donutBackground }};">
                            <div class="absolute inset-[24%] flex flex-col items-center justify-center rounded-full bg-white text-center dark:bg-[#1c1c1b]">
                                <span class="text-xl font-semibold leading-none text-gray-900 dark:text-white">{{ $totals['sites'] }}</span>
                                <span class="mt-1 text-[9px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Sites</span>
                            </div>
                        </div>

                        <ul class="min-w-0 flex-1 space-y-1.5">
                            @foreach ($statuses as $status)
                                @if ($status['count'] > 0)
                                    <li class="flex items-center gap-2 text-xs">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $status['color'] }};"></span>
                                        <span class="min-w-0 flex-1 truncate text-gray-600 dark:text-gray-300">{{ $status['label'] }}</span>
                                        <span class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $status['count'] }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Acceptance gauges --}}
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    @foreach ([
                        ['label' => '% Aceitação Atual', 'value' => $totals['acceptance'], 'offset' => $acceptanceOffset, 'color' => '#10b981'],
                        ['label' => '% Aceitação Projetada', 'value' => $totals['projected'], 'offset' => $projectedOffset, 'color' => '#f59e0b'],
                    ] as $gauge)
                        <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $gauge['label'] }}</h3>
                                <span class="text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($gauge['value'], 1, ',', '.') }}%</span>
                            </div>

                            <svg viewBox="0 0 100 56" class="mt-2 h-16 w-full" role="img" aria-label="{{ $gauge['label'] }}: {{ number_format($gauge['value'], 1, ',', '.') }}%">
                                <path d="M10 50 A40 40 0 0 1 90 50" fill="none" stroke="currentColor" stroke-width="9" stroke-linecap="round" class="text-gray-200 dark:text-white/10" />
                                <path
                                    d="M10 50 A40 40 0 0 1 90 50"
                                    fill="none"
                                    stroke="{{ $gauge['color'] }}"
                                    stroke-width="9"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $gaugeLength }}"
                                    stroke-dashoffset="{{ $gauge['offset'] }}"
                                />
                                <text x="50" y="46" text-anchor="middle" font-size="12" font-weight="700" fill="currentColor" class="text-gray-900 dark:text-white">{{ number_format($gauge['value'], 1, ',', '.') }}%</text>
                            </svg>

                            <div class="flex justify-between text-[10px] font-medium text-gray-400 dark:text-gray-500">
                                <span>0,0%</span>
                                <span>100,0%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                {{-- Aging --}}
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Aging Instalação</h3>
                    <p class="mt-0.5 text-[10px] text-gray-400 dark:text-gray-500">Dias desde o MOS real dos sites ainda não aceitos.</p>

                    <div class="mt-3 space-y-2">
                        @foreach ($aging as $bucket)
                            <div class="flex items-center gap-2">
                                <span class="w-14 shrink-0 text-right text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ $bucket['label'] }}</span>
                                <div class="h-4 min-w-0 flex-1 overflow-hidden rounded bg-gray-100 dark:bg-white/10">
                                    <div
                                        class="h-full rounded bg-indigo-500/80"
                                        style="width: {{ $bucket['count'] > 0 ? max(3, round(($bucket['count'] / $maxAging) * 100)) : 0 }}%;"
                                    ></div>
                                </div>
                                <span class="w-6 shrink-0 text-right text-[11px] font-semibold tabular-nums text-gray-700 dark:text-gray-200">{{ $bucket['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Insights --}}
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10 xl:col-span-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon name="star" class="h-3.5 w-3.5" />
                        </span>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Insights Governança</h3>
                    </div>

                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($insights as $insight)
                            @php $tone = $insightTones[$insight['tone']] ?? $insightTones['info']; @endphp
                            <li class="flex items-start gap-2 rounded-xl bg-gray-50/70 px-2.5 py-2 text-xs text-gray-600 dark:bg-white/[0.03] dark:text-gray-300">
                                <x-icon :name="$tone['icon']" class="mt-0.5 h-3.5 w-3.5 shrink-0 {{ $tone['class'] }}" />
                                <span>{{ $insight['text'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                {{-- Subcon --}}
                <div class="overflow-hidden rounded-2xl border border-gray-200/80 dark:border-white/10 xl:col-span-2">
                    <div class="border-b border-gray-100 px-4 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Subcon vs % Aceitação</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50/70 text-[10px] uppercase tracking-wider text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">Subcon</th>
                                    <th class="px-3 py-2 text-right font-semibold">OC</th>
                                    <th class="px-3 py-2 text-right font-semibold">Aceitos</th>
                                    <th class="px-3 py-2 text-right font-semibold">Gap</th>
                                    <th class="px-3 py-2 text-right font-semibold">Aging</th>
                                    <th class="px-3 py-2 text-right font-semibold">% Aceito</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach ($subcons as $subcon)
                                    <tr class="text-gray-600 dark:text-gray-300">
                                        <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $subcon['label'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $subcon['total'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $subcon['accepted'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $subcon['gap'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $subcon['aging'] === null ? '—' : number_format($subcon['aging'], 1, ',', '.').'d' }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <span @class([
                                                'inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold tabular-nums',
                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => $subcon['percent'] >= $totals['target'],
                                                'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' => $subcon['percent'] < $totals['target'],
                                            ])>{{ number_format($subcon['percent'], 1, ',', '.') }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ROM --}}
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">ROM vs % Aceitação</h3>

                    <div class="mt-3 space-y-2.5">
                        @foreach ($roms as $rom)
                            <div>
                                <div class="flex items-center justify-between gap-2 text-[11px]">
                                    <span class="min-w-0 truncate font-medium text-gray-700 dark:text-gray-200">{{ $rom['label'] }}</span>
                                    <span class="shrink-0 tabular-nums text-gray-500 dark:text-gray-400">{{ $rom['accepted'] }}/{{ $rom['total'] }} · {{ number_format($rom['percent'], 1, ',', '.') }}%</span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div
                                        class="h-full rounded-full {{ $rom['percent'] >= $totals['target'] ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                        style="width: {{ $rom['percent'] }}%;"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- OS table --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200/80 dark:border-white/10">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Últimos sites atualizados</h3>
                    <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">{{ count($sites) }} registro(s)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50/70 text-[10px] uppercase tracking-wider text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">OC</th>
                                <th class="px-3 py-2 text-left font-semibold">OS MOS</th>
                                <th class="px-3 py-2 text-left font-semibold">OS INST</th>
                                <th class="px-3 py-2 text-left font-semibold">Site ID</th>
                                <th class="px-3 py-2 text-left font-semibold">ROM</th>
                                <th class="px-3 py-2 text-left font-semibold">MOS</th>
                                <th class="px-3 py-2 text-left font-semibold">Inst.</th>
                                <th class="px-3 py-2 text-left font-semibold">Integração</th>
                                <th class="px-3 py-2 text-left font-semibold">RFA</th>
                                <th class="px-3 py-2 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($sites as $site)
                                <tr class="text-gray-600 dark:text-gray-300">
                                    <td class="whitespace-nowrap px-3 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $site['oc'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['os_entrega'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['os_instalacao'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['site_id'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['rom'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">{{ $site['mos_real_at']?->format('d/m/y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">{{ $site['instalacao_real_at']?->format('d/m/y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">{{ $site['integracao_real_at']?->format('d/m/y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">{{ $site['rfa_real_at']?->format('d/m/y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">
                                        <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold text-white" style="background-color: {{ $site['status_color'] }};">
                                            {{ $site['status_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- All sites --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200/80 dark:border-white/10">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Todos os sites</h3>
                        <p class="mt-0.5 text-[10px] text-gray-400 dark:text-gray-500">Todos os sites cadastrados, incluindo os que ainda não têm integração real.</p>
                    </div>
                    <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">{{ count($allSites) }} site(s)</span>
                </div>

                <div class="max-h-[28rem] overflow-auto">
                    <table class="min-w-full text-xs">
                        <thead class="sticky top-0 z-10 bg-gray-50/95 text-[10px] uppercase tracking-wider text-gray-400 backdrop-blur dark:bg-[#1c1c1b]/95 dark:text-gray-500">
                            <tr>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">Site ID</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">OC</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">Regional</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">Subcon</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">ROM</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">Status</th>
                                <th class="whitespace-nowrap px-3 py-2 text-right font-semibold">Aging</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">MOS</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">Instalação</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">Integração</th>
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold">RFA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse ($allSites as $site)
                                <tr class="text-gray-600 dark:text-gray-300">
                                    <td class="whitespace-nowrap px-3 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $site['site_id'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['oc'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['regional'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['subcon'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">{{ $site['rom'] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">
                                        <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold text-white" style="background-color: {{ $site['status_color'] }};">
                                            {{ $site['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums">{{ $site['aging'] }}d</td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">{{ $site['mos_real_at']?->format('d/m/y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">{{ $site['instalacao_real_at']?->format('d/m/y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">
                                        @if ($site['integrated'])
                                            {{ $site['integracao_real_at']?->format('d/m/y') ?? '—' }}
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400">Não integrado</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 tabular-nums">{{ $site['rfa_real_at']?->format('d/m/y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-3 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Nenhum site cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</section>
