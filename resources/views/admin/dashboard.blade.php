@php
    $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');

    $quickActions = [
        ['label' => 'Nova OS', 'url' => route('admin.work-orders.create'), 'icon' => 'clipboard-document-list'],
        ['label' => 'Novo cliente', 'url' => route('admin.clients.create'), 'icon' => 'building-office'],
        ['label' => 'Novo projeto', 'url' => route('admin.projects.create'), 'icon' => 'briefcase'],
        ['label' => 'Enviar arquivo', 'url' => route('admin.files.index'), 'icon' => 'arrow-up-tray'],
        ['label' => 'Novo link', 'url' => route('admin.links.index'), 'icon' => 'link'],
    ];

    $kpis = [
        ['label' => 'Clientes', 'value' => $totalClients, 'icon' => 'building-office', 'url' => route('admin.clients.index'), 'tone' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400'],
        ['label' => 'OS abertas', 'value' => $openWorkOrders, 'icon' => 'clipboard-document-list', 'url' => route('admin.work-orders.index'), 'tone' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'],
        ['label' => 'Projetos ativos', 'value' => $activeProjectsCount, 'icon' => 'briefcase', 'url' => route('admin.projects.index'), 'tone' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'],
        ['label' => 'Arquivos', 'value' => $totalDocuments, 'icon' => 'document-text', 'url' => route('admin.files.index'), 'tone' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400'],
    ];

    $statusBreakdown = [
        ['key' => 'open', 'label' => 'Aberta', 'count' => $statusCounts['open'], 'bar' => 'bg-blue-500'],
        ['key' => 'in_progress', 'label' => 'Em andamento', 'count' => $statusCounts['in_progress'], 'bar' => 'bg-amber-500'],
        ['key' => 'completed', 'label' => 'Concluída', 'count' => $statusCounts['completed'], 'bar' => 'bg-green-500'],
        ['key' => 'cancelled', 'label' => 'Cancelada', 'count' => $statusCounts['cancelled'], 'bar' => 'bg-gray-400'],
    ];
@endphp

<x-layouts.admin title="Dashboard">
    <div>
        <h1 class="text-xl font-semibold">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $greeting }} Aqui está o resumo das suas atividades.</p>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($quickActions as $action)
            <a
                href="{{ $action['url'] }}"
                class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium shadow-sm transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700 dark:hover:bg-gray-800"
            >
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600 transition group-hover:bg-gray-900 group-hover:text-white dark:bg-gray-800 dark:text-gray-300 dark:group-hover:bg-white dark:group-hover:text-gray-900">
                    <x-icon :name="$action['icon']" class="h-4 w-4" />
                </span>
                {{ $action['label'] }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($kpis as $kpi)
            <a href="{{ $kpi['url'] }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $kpi['label'] }}</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $kpi['tone'] }}">
                        <x-icon :name="$kpi['icon']" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-2 text-2xl font-semibold">{{ $kpi['value'] }}</p>
            </a>
        @endforeach
    </div>

    <section class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 class="text-sm font-semibold">Ordens de serviço</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Distribuição por status · {{ $workOrdersTotal }} no total</p>
            </div>
            <a href="{{ route('admin.work-orders.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Ver todas</a>
        </div>

        <div class="grid gap-5 px-5 py-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statusBreakdown as $status)
                <a href="{{ route('admin.work-orders.index', ['status' => $status['key']]) }}">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $status['label'] }}</span>
                        <span class="text-sm font-semibold">{{ $status['count'] }}</span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full rounded-full {{ $status['bar'] }}" style="width: {{ $workOrdersTotal > 0 ? round($status['count'] / $workOrdersTotal * 100) : 0 }}%"></div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-sm font-semibold">OS recentes</h2>
                <a href="{{ route('admin.work-orders.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Ver todas</a>
            </div>

            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Ordem</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($recentWorkOrders as $workOrder)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="block">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->number }}</p>
                                    <p class="max-w-[16rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $workOrder->title }}</p>
                                    <p class="max-w-[16rem] truncate text-xs text-gray-400 dark:text-gray-500">{{ $workOrder->client?->name ?? 'Sem cliente' }}</p>
                                </a>
                            </td>
                            <td class="px-5 py-3"><x-work-order-status :work-order="$workOrder" /></td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600 dark:text-gray-300">{{ $money((float) $workOrder->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-6 text-center text-gray-500 dark:text-gray-400" colspan="3">Nenhuma ordem de serviço cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Projetos em andamento</h2>
                <a href="{{ route('admin.projects.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Ver todos</a>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($activeProjects as $project)
                    <a href="{{ route('admin.projects.show', $project) }}" class="block px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->name }}</p>
                            <span class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400">{{ number_format((float) $project->percent_complete, 0) }}%</span>
                        </div>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-green-500" style="width: {{ min(100, (float) $project->percent_complete) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $project->tasks_count }} {{ $project->tasks_count === 1 ? 'tarefa' : 'tarefas' }}
                            · Término {{ $project->finish_date?->format('d/m/Y') ?? '—' }}
                        </p>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum projeto em andamento.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Arquivos recentes</h2>
                <a href="{{ route('admin.files.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Ver todos</a>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($recentDocuments as $document)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <x-file-type-icon :name="$document->original_name" size="xs" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $document->original_name }}">{{ $document->original_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Number::fileSize($document->size) }}</p>
                        </div>
                        <x-relative-time :value="$document->created_at" class="text-xs text-gray-400 dark:text-gray-500" />
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum arquivo enviado.</p>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Links recentes</h2>
                <a href="{{ route('admin.links.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Ver todos</a>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($recentLinks as $shortLink)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $shortLink->title ?: $shortLink->code }}</p>
                            <p class="mt-1 flex flex-wrap items-center gap-1.5">
                                <x-short-link-type :type="$shortLink->type" />
                                <x-short-link-status :short-link="$shortLink" />
                            </p>
                        </div>
                        <x-relative-time :value="$shortLink->created_at" class="text-xs text-gray-400 dark:text-gray-500" />
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum link criado.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.admin>
