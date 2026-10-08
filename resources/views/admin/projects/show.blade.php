<x-layouts.admin :title="$project->name">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="briefcase" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $project->name }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                <x-project-status :project="$project" />
                <span>Início: {{ $project->start_date?->format('d/m/Y') ?? '—' }}</span>
                <span>Orçamento: {{ number_format((float) $project->budget, 2, ',', '.') }} {{ $project->currency }}</span>
            </div>
            @if ($project->description)
                <p class="mt-2 max-w-2xl text-sm text-gray-600 dark:text-gray-300">{{ $project->description }}</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.projects.index') }}"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
            >
                Voltar
            </a>

            @if ($canPlan)
                <a
                    href="{{ route('admin.projects.edit', $project) }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    Editar
                </a>
            @endif
        </div>
    </div>

    <div class="mt-6">
        @include('admin.projects.partials.kpis')
    </div>

    @include('admin.projects.partials.gantt')
    @include('admin.projects.partials.tasks')

    @if ($canPlan)
        @include('admin.projects.partials.task-modal')
    @endif

    @include('admin.projects.partials.dependencies')
    @include('admin.projects.partials.resources')
    @include('admin.projects.partials.assignments')
    @include('admin.projects.partials.members')
    @include('admin.projects.partials.baselines')
    @include('admin.projects.partials.evm')

    @vite(['resources/js/gantt.js'])

    <script>
        document.addEventListener('submit', (event) => {
            if (!event.target.matches('[data-confirm-delete]')) {
                return;
            }

            if (!window.confirm('Tem certeza que deseja excluir este item?')) {
                event.preventDefault();
            }
        });
    </script>
</x-layouts.admin>
