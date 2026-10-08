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

    <div class="mt-6 grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside>
            <x-project-sections-nav />
        </aside>

        <div class="min-w-0">
            <div id="kpis" data-project-panel>
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
        </div>
    </div>

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

        const sectionLinks = [...document.querySelectorAll('[data-project-section]')];
        const panels = [...document.querySelectorAll('[data-project-panel]')];

        const setActiveLink = (id) => {
            sectionLinks.forEach((link) => {
                const isActive = id !== null && link.dataset.projectSection === id;
                link.classList.toggle('bg-gray-100', isActive);
                link.classList.toggle('text-gray-900', isActive);
                link.classList.toggle('dark:bg-gray-800', isActive);
                link.classList.toggle('dark:text-gray-100', isActive);

                if (isActive) {
                    link.setAttribute('aria-current', 'location');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };

        sectionLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();

                const id = link.dataset.projectSection;

                panels.forEach((panel) => panel.classList.toggle('hidden', panel.id !== id));

                if (id === 'gantt') {
                    document.dispatchEvent(new CustomEvent('gantt:resize'));
                }

                setActiveLink(id);
                document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        const showAllButton = document.querySelector('[data-project-sections-showall]');

        if (showAllButton) {
            showAllButton.addEventListener('click', (event) => {
                event.preventDefault();
                panels.forEach((panel) => panel.classList.remove('hidden'));
                setActiveLink(null);
                document.dispatchEvent(new CustomEvent('gantt:resize'));
            });
        }

        if ('IntersectionObserver' in window && panels.length > 0) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setActiveLink(entry.target.id);
                    }
                });
            }, { rootMargin: '-96px 0px -70% 0px' });

            panels.forEach((panel) => observer.observe(panel));
        }
    </script>
</x-layouts.admin>
