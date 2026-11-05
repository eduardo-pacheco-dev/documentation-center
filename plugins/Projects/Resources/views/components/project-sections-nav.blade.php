@php
    $sections = [
        'kpis' => ['label' => 'Indicadores', 'icon' => 'squares-2x2'],
        'gantt' => ['label' => 'Cronograma', 'icon' => 'calendar-days'],
        'tasks' => ['label' => 'Tarefas', 'icon' => 'rectangle-stack'],
        'dependencies' => ['label' => 'Dependências', 'icon' => 'link'],
        'resources' => ['label' => 'Recursos', 'icon' => 'adjustments-horizontal'],
        'assignments' => ['label' => 'Alocações', 'icon' => 'users'],
        'members' => ['label' => 'Membros', 'icon' => 'user-plus'],
        'baselines' => ['label' => 'Linhas de base', 'icon' => 'flag'],
        'evm' => ['label' => 'Desempenho (EVM)', 'icon' => 'chart-bar'],
    ];
@endphp

<nav class="rounded-3xl border border-gray-200/80 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-[#171716]" aria-label="Seções do projeto">
    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Seções</p>

    <ul class="space-y-0.5">
        @foreach ($sections as $id => $section)
            <li>
                <a
                    href="#{{ $id }}"
                    data-project-section="{{ $id }}"
                    class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                >
                    <x-icon :name="$section['icon']" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    {{ $section['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <button
        type="button"
        data-project-sections-showall
        class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5"
    >
        Mostrar todas as seções
    </button>
</nav>
