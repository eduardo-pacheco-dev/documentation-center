@php
    $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');

    $stats = [
        ['label' => 'Clientes', 'value' => $counts['clients'], 'icon' => 'building-office', 'url' => route('admin.clients.index'), 'tone' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400'],
        ['label' => 'OS abertas', 'value' => $counts['openWorkOrders'], 'icon' => 'clipboard-document-list', 'url' => route('admin.work-orders.index'), 'tone' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'],
        ['label' => 'Projetos ativos', 'value' => $counts['projects'], 'icon' => 'briefcase', 'url' => route('admin.projects.index'), 'tone' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'],
        ['label' => 'Arquivos', 'value' => $counts['documents'], 'icon' => 'document-text', 'url' => route('admin.files.index'), 'tone' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400'],
    ];

    $modules = [
        ['label' => 'ERBs', 'count' => $counts['erbs'], 'icon' => 'signal', 'url' => route('admin.erbs.index'), 'tone' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400'],
        ['label' => 'Radio links', 'count' => $counts['radioLinks'], 'icon' => 'arrows-right-left', 'url' => route('admin.radio-links.index'), 'tone' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400'],
        ['label' => 'Catálogo', 'count' => $counts['catalogItems'], 'icon' => 'cube', 'url' => route('admin.catalog.index'), 'tone' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'],
        ['label' => 'Formulários', 'count' => $counts['forms'], 'icon' => 'document-check', 'url' => route('admin.forms.index'), 'tone' => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400'],
        ['label' => 'Templates', 'count' => $counts['formTemplates'], 'icon' => 'document-duplicate', 'url' => route('admin.form-templates.index'), 'tone' => 'bg-fuchsia-50 text-fuchsia-600 dark:bg-fuchsia-500/10 dark:text-fuchsia-400'],
        ['label' => 'Colaboradores', 'count' => $counts['colaboradores'], 'icon' => 'user', 'url' => route('admin.colaboradores.index'), 'tone' => 'bg-lime-50 text-lime-600 dark:bg-lime-500/10 dark:text-lime-400'],
        ['label' => 'Links', 'count' => $counts['links'], 'icon' => 'link', 'url' => route('admin.links.index'), 'tone' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'],
    ];

    if ($counts['users'] !== null) {
        $modules[] = ['label' => 'Usuários', 'count' => $counts['users'], 'icon' => 'users', 'url' => route('admin.users.index'), 'tone' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300'];
    }

    $statusBreakdown = [
        ['key' => 'open', 'label' => 'Aberta', 'count' => $statusCounts['open'], 'bar' => 'bg-blue-500', 'dot' => 'bg-blue-500', 'stroke' => 'stroke-blue-500'],
        ['key' => 'in_progress', 'label' => 'Em andamento', 'count' => $statusCounts['in_progress'], 'bar' => 'bg-amber-500', 'dot' => 'bg-amber-500', 'stroke' => 'stroke-amber-500'],
        ['key' => 'completed', 'label' => 'Concluída', 'count' => $statusCounts['completed'], 'bar' => 'bg-green-500', 'dot' => 'bg-green-500', 'stroke' => 'stroke-green-500'],
        ['key' => 'cancelled', 'label' => 'Cancelada', 'count' => $statusCounts['cancelled'], 'bar' => 'bg-gray-400', 'dot' => 'bg-gray-400', 'stroke' => 'stroke-gray-400'],
    ];

    $completionRate = $workOrdersTotal > 0
        ? (int) round($statusCounts['completed'] / $workOrdersTotal * 100)
        : 0;

    $circumference = 2 * M_PI * 40;
    $donutSegments = [];
    $donutOffset = 0.0;

    foreach ($statusBreakdown as $status) {
        if ($status['count'] <= 0) {
            continue;
        }

        $length = $status['count'] / max($workOrdersTotal, 1) * $circumference;

        $donutSegments[] = [
            'label' => $status['label'],
            'count' => $status['count'],
            'stroke' => $status['stroke'],
            'length' => round($length, 2),
            'offset' => round(-$donutOffset, 2),
        ];

        $donutOffset += $length;
    }
@endphp

<x-layouts.admin title="Dashboard">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 p-6 text-white shadow-xl shadow-indigo-600/10 sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-violet-400/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur">
                    <x-icon name="calendar-days" class="h-3.5 w-3.5" />
                    {{ now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}
                </span>
                <h1 class="mt-4 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $greeting }}</h1>
                <p class="mt-2 max-w-xl text-sm text-indigo-100/90">
                    Aqui está o panorama do seu dia. Acompanhe ordens de serviço, projetos e documentos em um só lugar.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('admin.work-orders.create') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Nova OS
                </a>
                <a
                    href="{{ route('admin.links.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    <x-icon name="link" class="h-4 w-4" />
                    Novo link
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <a
                href="{{ $stat['url'] }}"
                class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40"
            >
                <div class="flex items-start justify-between">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl {{ $stat['tone'] }}">
                        <x-icon :name="$stat['icon']" class="h-5 w-5" />
                    </span>
                    <x-icon name="arrow-top-right-on-square" class="h-4 w-4 text-gray-300 transition group-hover:text-indigo-500 dark:text-gray-600" />
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stat['value'] }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm lg:col-span-2 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ordens de serviço</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $workOrdersTotal }} no total · distribuição por status</p>
                </div>
                <a href="{{ route('admin.work-orders.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                    Ver todas
                    <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                </a>
            </div>

            <div class="mt-6 grid items-center gap-8 sm:grid-cols-[minmax(0,10rem)_1fr]">
                <div class="relative mx-auto h-40 w-40">
                    <svg viewBox="0 0 100 100" class="h-40 w-40 -rotate-90">
                        <circle cx="50" cy="50" r="40" fill="none" stroke-width="12" class="stroke-gray-100 dark:stroke-gray-800" />
                        @foreach ($donutSegments as $segment)
                            <circle
                                cx="50"
                                cy="50"
                                r="40"
                                fill="none"
                                stroke-width="12"
                                stroke-linecap="butt"
                                class="{{ $segment['stroke'] }}"
                                stroke-dasharray="{{ $segment['length'] }} {{ round($circumference, 2) }}"
                                stroke-dashoffset="{{ $segment['offset'] }}"
                            >
                                <title>{{ $segment['label'] }}: {{ $segment['count'] }}</title>
                            </circle>
                        @endforeach
                    </svg>

                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $workOrdersTotal }}</span>
                        <span class="text-[11px] font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Total</span>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach ($statusBreakdown as $status)
                        <a
                            href="{{ route('admin.work-orders.index', ['status' => $status['key']]) }}"
                            class="flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 transition hover:border-gray-200 hover:bg-gray-50 dark:hover:border-gray-700 dark:hover:bg-gray-800/50"
                        >
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $status['dot'] }}"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $status['label'] }}</span>
                            <span class="ml-auto text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $status['count'] }}</span>
                            <span class="w-12 text-right text-xs text-gray-400 dark:text-gray-500">
                                {{ $workOrdersTotal > 0 ? round($status['count'] / $workOrdersTotal * 100) : 0 }}%
                            </span>
                        </a>
                    @endforeach

                    <div class="!mt-4 flex items-center gap-3 rounded-xl bg-indigo-50/70 px-3 py-2.5 dark:bg-indigo-500/10">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm dark:bg-gray-900 dark:text-indigo-400">
                            <x-icon name="check-circle" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Taxa de conclusão</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $completionRate }}% das OS</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Módulos</h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Acesso rápido aos recursos</p>

            <div class="mt-4 grid grid-cols-2 gap-2">
                @foreach ($modules as $module)
                    <a
                        href="{{ $module['url'] }}"
                        data-module="{{ \Illuminate\Support\Str::slug($module['label']) }}"
                        class="group flex items-center gap-2 rounded-xl border border-gray-100 px-3 py-2.5 transition hover:border-indigo-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-indigo-500/40 dark:hover:bg-gray-800/50"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $module['tone'] }}">
                            <x-icon :name="$module['icon']" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0 flex-1 truncate text-xs font-medium text-gray-600 dark:text-gray-300">{{ $module['label'] }}</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $module['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-dashboard-panel class="lg:col-span-2" title="Ordens de serviço recentes" :url="route('admin.work-orders.index')">
            @forelse ($recentWorkOrders as $workOrder)
                <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="clipboard-document-list" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->number }} · {{ $workOrder->title }}</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $workOrder->client?->name ?? 'Sem cliente' }}</p>
                    </div>
                    <x-work-order-status :work-order="$workOrder" />
                    <span class="hidden shrink-0 text-sm font-medium text-gray-600 sm:inline dark:text-gray-300">{{ $money((float) $workOrder->total) }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma ordem de serviço cadastrada.</p>
            @endforelse
        </x-dashboard-panel>

        <x-dashboard-panel title="Arquivos recentes" :url="route('admin.files.index')">
            @forelse ($recentDocuments as $document)
                <div class="flex items-center gap-3 px-5 py-3">
                    <x-file-type-icon :name="$document->original_name" size="xs" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $document->original_name }}">{{ $document->original_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Number::fileSize($document->size) }}</p>
                    </div>
                    <x-relative-time :value="$document->created_at" class="shrink-0 text-xs text-gray-400 dark:text-gray-500" />
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum arquivo enviado.</p>
            @endforelse
        </x-dashboard-panel>
    </div>
</x-layouts.admin>
