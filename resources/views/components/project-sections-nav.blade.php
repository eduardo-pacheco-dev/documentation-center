@props(['default' => 'kpis'])

<nav aria-label="Seções do projeto" class="sticky top-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Seções</p>

    <ul class="mt-3 space-y-1" data-project-sections-nav>
        @foreach ([
            'kpis' => ['Indicadores', 'squares-2x2'],
            'gantt' => ['Cronograma', 'calendar-days'],
            'tasks' => ['Estrutura de trabalho (WBS)', 'rectangle-stack'],
            'dependencies' => ['Dependências', 'link'],
            'resources' => ['Recursos', 'adjustments-horizontal'],
            'assignments' => ['Alocações de recursos', 'users'],
            'members' => ['Membros', 'user-plus'],
            'baselines' => ['Linhas de base', 'flag'],
            'evm' => ['Desempenho (EVM)', 'chart-bar'],
        ] as $id => [$label, $icon])
            @php($isActive = $id === $default)
            <li>
                <a
                    href="#{{ $id }}"
                    data-project-section="{{ $id }}"
                    @if ($isActive) aria-current="location" @endif
                    class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-100' }}"
                >
                    <x-icon :name="$icon" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>

    <p class="mt-3 border-t border-gray-100 pt-3 dark:border-gray-800">
        <a
            href="#kpis"
            data-project-sections-showall
            class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
        >
            <x-icon name="squares-2x2" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
            Ver todas as seções
        </a>
    </p>
</nav>