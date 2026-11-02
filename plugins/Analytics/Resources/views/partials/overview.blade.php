{{-- Visão geral --}}
<div id="panel-overview" data-panel="overview" role="tabpanel" aria-labelledby="tab-overview" class="space-y-4">
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-analytics::panel
            class="xl:col-span-2"
            title="Criações no período"
            :description="'Projetos, ordens de serviço e documentos ('.($days <= 31 ? 'por dia' : 'por mês').').'"
        >
            <x-slot:actions>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $summary['projects']['created'] }} projetos · {{ $summary['work_orders']['created'] }} OS
                </span>
            </x-slot:actions>

            <x-analytics::apex-chart id="analytics-series" class="mt-4" />
        </x-analytics::panel>

        <x-analytics::panel title="Ordens por status" :description="$summary['work_orders']['total'].' ordens no total'">
            <x-analytics::chart-canvas id="analytics-work-orders" height="h-52" class="mt-3" />
            <x-analytics::status-list :items="$workOrderStatuses" :columns="2" dot class="mt-3" />
        </x-analytics::panel>

        <x-analytics::panel
            title="Tarefas por lista"
            :description="($taskSplit['values'][0] ?? 0).' pendentes · '.($taskSplit['values'][1] ?? 0).' concluídas'"
        >
            <x-analytics::chart-canvas id="analytics-tasks-by-list" class="mt-4" />
        </x-analytics::panel>

        <x-analytics::panel title="Formulários por status" :description="$summary['forms']['total'].' formulários no total'">
            <x-analytics::chart-canvas id="analytics-forms" class="mt-4" />
        </x-analytics::panel>

        <x-analytics::panel title="Agenda e projetos">
            <div class="mt-4 space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Eventos no período</p>

                    <x-analytics::status-list
                        :items="$agendaStatuses"
                        dot
                        empty="Nenhum evento no período."
                        class="mt-2"
                    />
                </div>

                <div class="border-t border-gray-100 pt-4 dark:border-white/10">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Projetos por status</p>

                    <x-analytics::status-list
                        :items="$projectStatuses"
                        dot
                        empty="Nenhum projeto cadastrado."
                        class="mt-2"
                    />
                </div>
            </div>
        </x-analytics::panel>
    </div>

    <x-analytics::panel title="Projetos com mais tarefas">
        <x-slot:actions>
            <span class="text-xs text-gray-400 dark:text-gray-500">
                {{ $topProjects['labels'] === [] ? 'Sem dados' : count($topProjects['labels']).' projetos' }}
            </span>
        </x-slot:actions>

        <x-analytics::progress-list
            :labels="$topProjects['labels']"
            :values="$topProjects['values']"
            empty="Nenhum projeto cadastrado."
            class="mt-4"
        />
    </x-analytics::panel>
</div>
