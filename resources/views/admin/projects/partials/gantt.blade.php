<section id="gantt" class="mt-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            <x-icon name="calendar-days" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Cronograma
        </h2>

        @if ($canPlan)
            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('admin.projects.schedule.store', $project) }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <x-icon name="play" class="h-4 w-4" />
                        Recalcular caminho crítico
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.projects.level.store', $project) }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <x-icon name="arrows-right-left" class="h-4 w-4" />
                        Nivelar recursos
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div
        id="gantt-chart"
        class="mt-3 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        data-gantt
        data-url="{{ route('admin.projects.gantt.data', $project) }}"
        data-move-url="{{ route('admin.projects.gantt.move', [$project, '__TASK__']) }}"
        data-progress-url="{{ route('admin.projects.tasks.progress', [$project, '__TASK__']) }}"
        data-can-edit="{{ $canPlan ? '1' : '0' }}"
        data-csrf="{{ csrf_token() }}"
    ></div>
</section>
