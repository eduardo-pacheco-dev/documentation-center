<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="calendar-days" class="h-4 w-4" />
            Início planejado
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $tasks->whereNotNull('start_at')->min('start_at')?->format('d/m/Y') ?? $project->start_date?->format('d/m/Y') ?? '—' }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="flag" class="h-4 w-4" />
            Término planejado
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $tasks->whereNotNull('finish_at')->max('finish_at')?->format('d/m/Y') ?? '—' }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="clock" class="h-4 w-4" />
            Progresso
        </p>
        <div class="mt-2 flex items-center gap-2">
            <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div class="h-full rounded-full bg-green-500" style="width: {{ min(100, (float) $project->percent_complete) }}%"></div>
            </div>
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $project->percent_complete, 0) }}%</span>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="flex items-center gap-1.5 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
            <x-icon name="exclamation-triangle" class="h-4 w-4" />
            Caminho crítico
        </p>
        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $tasks->where('critical', true)->count() }} tarefa(s)
            <span class="text-xs font-normal text-gray-500 dark:text-gray-400">de {{ $tasks->count() }}</span>
        </p>
    </div>
</div>
