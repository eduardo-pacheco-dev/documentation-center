@php
    $leaves = $tasks->filter(fn ($task) => $task->children->isEmpty());
    $total = $leaves->count();
    $done = $leaves->where('percent_complete', '>=', 100)->count();
    $inProgress = $leaves->where('percent_complete', '>', 0)->where('percent_complete', '<', 100)->count();
    $critical = $leaves->where('critical', true);
    $criticalCount = $critical->count();
    $criticalDone = $critical->where('percent_complete', '>=', 100)->count();
    $actualCost = $leaves->sum('actual_cost');
    $budgetCost = (float) $project->budget;
    $workHours = $leaves->sum('work_minutes') / 60;
    $percent = min(100, max(0, (float) $project->percent_complete));
    $start = $tasks->whereNotNull('start_at')->min('start_at') ?? $project->start_date;
    $finish = $tasks->whereNotNull('finish_at')->max('finish_at') ?? $project->finish_date;
    $durationDays = $start && $finish ? max(1, (int) $start->diffInDays($finish) + 1) : null;
    $tasksDonePercent = $total > 0 ? min(100, round(100 * $done / $total)) : 0;
    $criticalPercent = $criticalCount > 0 ? min(100, round(100 * $criticalDone / $criticalCount)) : 0;
    $costPercent = $budgetCost > 0 ? min(100, round(100 * $actualCost / $budgetCost)) : 0;
    $overBudget = $budgetCost > 0 && $actualCost > $budgetCost;
@endphp

<div class="flex flex-wrap items-baseline gap-2">
    <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
        <x-icon name="squares-2x2" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
        Indicadores
    </h2>
    <span class="text-sm text-gray-500 dark:text-gray-400">Visão geral do projeto</span>
</div>

<div class="mt-3 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="calendar-days" class="h-4 w-4" />
            Início planejado
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $start?->format('d/m/Y') ?? '—' }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="flag" class="h-4 w-4" />
            Término planejado
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $finish?->format('d/m/Y') ?? '—' }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="chart-bar" class="h-4 w-4" />
            Progresso
        </p>
        <div class="mt-2 flex items-center gap-2">
            <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div class="h-full rounded-full bg-green-500" style="width: {{ $percent }}%"></div>
            </div>
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($percent, 0) }}%</span>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="rectangle-stack" class="h-4 w-4" />
            Tarefas concluídas
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $total > 0 ? "{$done} de {$total}" : '—' }}
            @if ($inProgress > 0)
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ $inProgress }} em andamento</span>
            @endif
        </p>
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
            <div class="h-full rounded-full bg-green-500" style="width: {{ $tasksDonePercent }}%"></div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="exclamation-triangle" class="h-4 w-4" />
            Caminho crítico
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ "{$criticalDone} de {$criticalCount}" }}
        </p>
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
            <div class="h-full rounded-full bg-amber-500" style="width: {{ $criticalPercent }}%"></div>
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">tarefas críticas concluídas</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="document" class="h-4 w-4" />
            Orçamento
        </p>
        <p class="mt-2 text-lg font-semibold {{ $overBudget ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
            {{ number_format($actualCost, 2, ',', '.') }} {{ $project->currency }}
        </p>
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
            <div class="h-full rounded-full {{ $overBudget ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $costPercent }}%"></div>
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ $budgetCost > 0 ? 'de ' . number_format($budgetCost, 2, ',', '.') . ' ' . $project->currency : 'sem orçamento definido' }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="clock" class="h-4 w-4" />
            Duração
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $durationDays ? $durationDays . ' dia' . ($durationDays > 1 ? 's' : '') : '—' }}
        </p>
        @if ($start && $finish)
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $start?->format('d/m') }} → {{ $finish?->format('d/m') }}
            </p>
        @endif
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="users" class="h-4 w-4" />
            Trabalho
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ number_format($workHours, 0, ',', '.') }} h
        </p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">horas de trabalho planejadas</p>
    </div>
</div>