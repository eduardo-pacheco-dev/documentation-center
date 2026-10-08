<x-layouts.admin title="Projetos">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="briefcase" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Projetos
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Planeje prazos, recursos e custos das suas entregas.</p>
        </div>

        <a
            href="{{ route('admin.projects.create') }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo projeto
        </a>
    </div>

    <form method="GET" action="{{ route('admin.projects.index') }}" class="mt-6 flex flex-wrap items-center gap-2">
        <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
            <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Buscar projeto pelo nome..."
                class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
        </div>

        <button
            type="submit"
            class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            Buscar
        </button>
    </form>

    <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
            <thead class="rounded-t-xl bg-gray-50 dark:bg-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3 font-medium">Projeto</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Início</th>
                    <th class="px-5 py-3 font-medium">Término</th>
                    <th class="px-5 py-3 font-medium">Progresso</th>
                    <th class="px-5 py-3 font-medium">Tarefas</th>
                    <th class="px-5 py-3 font-medium">Responsável</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($projects as $project)
                    <tr>
                        <td class="px-5 py-3">
                            <a
                                href="{{ route('admin.projects.show', $project) }}"
                                class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                            >
                                <x-icon name="briefcase" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                {{ $project->name }}
                            </a>
                            @if ($project->description)
                                <p class="mt-0.5 max-w-[24rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $project->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <x-project-status :project="$project" />
                        </td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $project->start_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $project->finish_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div class="h-full rounded-full bg-green-500" style="width: {{ min(100, (float) $project->percent_complete) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format((float) $project->percent_complete, 0) }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $project->tasks_count }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $project->user->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="7">
                            <span class="inline-flex items-center gap-2">
                                <x-icon name="briefcase" class="h-4 w-4" />
                                @if ($search !== '')
                                    Nenhum projeto encontrado para "{{ $search }}".
                                @else
                                    Nenhum projeto criado até o momento.
                                @endif
                            </span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $projects->links() }}
        </div>
    </div>
</x-layouts.admin>
