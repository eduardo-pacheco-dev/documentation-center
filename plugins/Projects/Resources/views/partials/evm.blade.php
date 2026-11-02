@php($currency = $project->currency ?? 'BRL')

<section id="evm" class="mt-8 hidden" data-project-panel>
    <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
        <x-icon name="chart-bar" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
        Desempenho (EVM)
        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">em {{ \Illuminate\Support\Carbon::parse($evm['status_date'])->format('d/m/Y') }}</span>
    </h2>

    <div class="mt-3 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            'BAC' => ['Orçamento total', $evm['bac']],
            'PV' => ['Valor planejado', $evm['pv']],
            'EV' => ['Valor agregado', $evm['ev']],
            'AC' => ['Custo real', $evm['ac']],
            'CV' => ['Variação de custo', $evm['cv']],
            'SV' => ['Variação de prazo', $evm['sv']],
            'EAC' => ['Estimativa final', $evm['eac']],
            'VAC' => ['Variação final', $evm['vac']],
        ] as $metric => [$label, $value])
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    {{ $metric }} · {{ $label }}
                </p>
                <p class="mt-2 text-lg font-semibold @if (($value <=> 0) < 0 && in_array($metric, ['CV', 'SV', 'VAC'], true)) text-red-600 dark:text-red-400 @else text-gray-900 dark:text-gray-100 @endif">
                    @if ($value === null)
                        —
                    @else
                        {{ number_format((float) $value, 2, ',', '.') }} {{ $currency }}
                    @endif
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">CPI (custo)</p>
            <p class="mt-2 text-lg font-semibold @if ($evm['cpi'] !== null && $evm['cpi'] < 1) text-red-600 dark:text-red-400 @else text-green-600 dark:text-green-400 @endif">
                {{ $evm['cpi'] === null ? '—' : number_format($evm['cpi'], 2, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">SPI (prazo)</p>
            <p class="mt-2 text-lg font-semibold @if ($evm['spi'] !== null && $evm['spi'] < 1) text-red-600 dark:text-red-400 @else text-green-600 dark:text-green-400 @endif">
                {{ $evm['spi'] === null ? '—' : number_format($evm['spi'], 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Curva S</p>

        @php($series = collect($evm['series']))
        @php($peak = max(1, (float) $series->max(fn ($point) => max($point['pv'], $point['ev'], $point['ac']))))

        <div class="mt-3 flex h-40 items-end gap-0.5">
            @foreach ($series as $point)
                @php($dayMax = max($point['pv'], $point['ev'], $point['ac']))
                <div class="group relative flex-1" title="{{ \Illuminate\Support\Carbon::parse($point['date'])->format('d/m/Y') }} — PV {{ number_format($point['pv'], 0, ',', '.') }} · EV {{ number_format($point['ev'], 0, ',', '.') }} · AC {{ number_format($point['ac'], 0, ',', '.') }}">
                    <div class="flex h-40 items-end gap-px">
                        <div class="w-full bg-indigo-300 dark:bg-indigo-500/60" style="height: {{ $dayMax > 0 ? ($point['pv'] / $peak) * 100 : 0 }}%"></div>
                        <div class="w-full bg-green-400 dark:bg-green-500/60" style="height: {{ $dayMax > 0 ? ($point['ev'] / $peak) * 100 : 0 }}%"></div>
                        <div class="w-full bg-amber-400 dark:bg-amber-500/60" style="height: {{ $dayMax > 0 ? ($point['ac'] / $peak) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-indigo-300 dark:bg-indigo-500/60"></span> PV</span>
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-green-400 dark:bg-green-500/60"></span> EV</span>
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-amber-400 dark:bg-amber-500/60"></span> AC</span>
        </div>
    </div>
</section>
