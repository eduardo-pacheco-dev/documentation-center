@php
    $statusItems = collect($statusDistribution)->filter(fn (array $item): bool => $item['count'] > 0)->values();
    $gradientStops = [];
    $cursor = 0;

    foreach ($statusItems as $index => $item) {
        $end = $index === $statusItems->count() - 1 ? 100 : min(100, $cursor + $item['percent']);
        $gradientStops[] = $item['color'].' '.$cursor.'% '.$end.'%';
        $cursor = $end;
    }

    $donutBackground = $gradientStops === []
        ? 'conic-gradient(#e5e7eb 0% 100%)'
        : 'conic-gradient('.implode(', ', $gradientStops).')';

    $maxMonthly = max(1, collect($monthly)->flatMap(fn (array $month): array => [$month['created'], $month['completed']])->max());
@endphp

<section class="flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="chart-bar" class="h-4 w-4" />
            </span>

            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Painel de follow-ups</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Distribuição por status, prioridade e evolução mensal.</p>
            </div>
        </div>

        <a href="{{ route('admin.follow-ups.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
            Ver follow-ups
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        </a>
    </div>

    <div class="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $totals['all'] }}</p>
        </div>

        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 dark:border-indigo-500/20 dark:bg-indigo-500/10">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-500 dark:text-indigo-300">Em aberto</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-indigo-700 dark:text-indigo-200">{{ $totals['open'] }}</p>
        </div>

        <div class="rounded-2xl border border-red-100 bg-red-50/60 px-4 py-3 dark:border-red-500/20 dark:bg-red-500/10">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-red-500 dark:text-red-300">Atrasados</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-red-700 dark:text-red-200">{{ $totals['overdue'] }}</p>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-500 dark:text-emerald-300">Concluídos</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-emerald-700 dark:text-emerald-200">{{ $totals['completed'] }}</p>
        </div>
    </div>

    @if ($totals['all'] === 0)
        <div class="flex flex-1 items-center justify-center px-5 pb-8 text-center">
            <div>
                <span class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 dark:bg-white/5 dark:text-gray-500">
                    <x-icon name="chart-bar" class="h-5 w-5" />
                </span>
                <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Ainda não há dados para os gráficos</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cadastre follow-ups para acompanhar a evolução por aqui.</p>
            </div>
        </div>
    @else
        <div class="grid gap-4 px-5 pb-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 p-4 dark:border-white/10">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Por status</h3>

                <div class="mt-4 flex items-center gap-5">
                    <div class="relative h-28 w-28 shrink-0 rounded-full" style="background: {{ $donutBackground }};">
                        <div class="absolute inset-[22%] flex flex-col items-center justify-center rounded-full bg-white text-center dark:bg-[#1c1c1b]">
                            <span class="text-xl font-semibold leading-none text-gray-900 dark:text-white">{{ $totals['all'] }}</span>
                            <span class="mt-1 text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500">Total</span>
                        </div>
                    </div>

                    <ul class="min-w-0 flex-1 space-y-2">
                        @foreach ($statusDistribution as $item)
                            <li class="flex items-center gap-2 text-xs">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $item['color'] }};"></span>
                                <span class="min-w-0 flex-1 truncate text-gray-600 dark:text-gray-300">{{ $item['label'] }}</span>
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $item['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 p-4 dark:border-white/10">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Por prioridade</h3>

                <ul class="mt-4 space-y-3">
                    @foreach ($priorityDistribution as $item)
                        <li>
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $item['label'] }}</span>
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $item['count'] }}</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-full rounded-full" style="width: {{ max($item['percent'], $item['count'] > 0 ? 2 : 0) }}%; background-color: {{ $item['color'] }};"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-gray-100 p-4 dark:border-white/10">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Últimos 6 meses</h3>

                    <div class="flex items-center gap-3 text-[10px] text-gray-400 dark:text-gray-500">
                        <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-indigo-500"></span>Criados</span>
                        <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Concluídos</span>
                    </div>
                </div>

                <div class="mt-4 flex h-32 items-end gap-2">
                    @foreach ($monthly as $month)
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-1">
                            <div class="flex h-full w-full items-end justify-center gap-0.5">
                                <div class="w-1/2 rounded-t bg-indigo-500/85" style="height: {{ $month['created'] > 0 ? max(4, round(($month['created'] / $maxMonthly) * 100)) : 2 }}%;" title="{{ $month['created'] }} criado(s)"></div>
                                <div class="w-1/2 rounded-t bg-emerald-500/85" style="height: {{ $month['completed'] > 0 ? max(4, round(($month['completed'] / $maxMonthly) * 100)) : 2 }}%;" title="{{ $month['completed'] }} concluído(s)"></div>
                            </div>
                            <span class="truncate text-[10px] font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
