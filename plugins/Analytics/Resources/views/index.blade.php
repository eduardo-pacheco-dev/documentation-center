@php
    $stats = [];

    if (is_array($recordsSummary)) {
        $stats[] = [
            'label' => 'Registros FAMs',
            'value' => $recordsSummary['total'],
            'icon' => 'table-cells',
            'color' => 'text-indigo-600 dark:text-indigo-400',
        ];
    }

    if (is_array($aflSummary)) {
        $stats[] = [
            'label' => 'Registros AFL',
            'value' => $aflSummary['total'],
            'icon' => 'flag',
            'color' => 'text-rose-600 dark:text-rose-400',
        ];
    }

    $hasSnapshotSeries = is_array($snapshotSeries) && count($snapshotSeries['labels']) >= 2;
    $scopeQuery = $scope === 'global' ? 'global' : null;

    $tabs = [
        'overview' => ['label' => 'Visão geral', 'count' => null],
        'fams' => ['label' => 'FAMs', 'count' => $recordsSummary['total'] ?? null],
        'afl' => ['label' => 'AFL', 'count' => $aflSummary['total'] ?? null],
        'snapshots' => ['label' => 'Snapshots', 'count' => $snapshots === [] ? null : count($snapshots)],
    ];
@endphp

<x-layouts.admin title="Analytics">
    <x-ui.page-header
        category="Inteligência"
        title="Analytics"
        :description="'Indicadores de '.$from->format('d/m/Y').' a '.$to->format('d/m/Y').' · '.($isGlobal ? 'visão global' : 'seus dados')"
        icon="chart-bar"
    >
        @if ($hasPendingImports)
            <button
                type="button"
                data-import-header-indicator
                data-modal-open="analytics-import-modal"
                class="inline-flex h-10 items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
            >
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-500 opacity-60"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-indigo-600"></span>
                </span>
                Importando…
            </button>
        @endif

        @if (is_array($recordsSummary) || is_array($aflSummary))
            <x-ui.action type="button" variant="secondary" icon="arrow-up-tray" data-modal-open="analytics-import-modal">
                Importar
            </x-ui.action>
        @endif

        <x-ui.action :href="route('analytics.export', array_filter(['period' => $days, 'scope' => $scopeQuery]))" variant="secondary" icon="arrow-down-tray">
            Exportar CSV
        </x-ui.action>

        <form method="POST" action="{{ route('analytics.snapshot') }}">
            @csrf
            <input type="hidden" name="period" value="{{ $days }}">
            <input type="hidden" name="scope" value="{{ $scope }}">

            <x-ui.action type="submit" icon="camera">
                Snapshot de hoje
            </x-ui.action>
        </form>
    </x-ui.page-header>

    @if ($stats !== [])
        <x-analytics::stat-strip :items="$stats" class="mt-4" />
    @endif

    <x-analytics::toolbar
        :periods="$periods"
        :days="$days"
        :scope="$scope"
        :scope-query="$scopeQuery"
        :is-admin="auth()->user()?->is_admin"
    />

    <x-analytics::tabs :tabs="$tabs" class="mt-6" />

    <div class="mt-4">
        @include('analytics::partials.overview')
        @include('analytics::partials.fams')
        @include('analytics::partials.afl')
        @include('analytics::partials.snapshots')
    </div>

    @if (is_array($recordsSummary) || is_array($aflSummary))
        @include('analytics::partials.import-modal')
    @endif

    @if (is_array($recordsSummary) || is_array($aflSummary))
        <div
            data-import-toast-root
            role="status"
            aria-live="polite"
            class="fixed bottom-4 right-4 z-50 w-[24rem] max-w-[calc(100vw-2rem)] {{ $hasPendingImports ? '' : 'hidden' }}"
        >
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 bg-gray-50/80 px-4 py-2.5 dark:border-white/10 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-500 opacity-60"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-indigo-600"></span>
                        </span>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Importações</p>
                    </div>

                    <button
                        type="button"
                        data-import-toast-toggle
                        class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"
                        aria-label="Minimizar importações"
                    >
                        <x-icon name="chevron-down" data-import-toast-chevron class="h-4 w-4 transition-transform" />
                    </button>
                </div>

                <div data-import-toast-body class="max-h-[70vh] space-y-3 overflow-y-auto p-4">
                    @include('analytics::partials.import-progress', ['imports' => $recentImports, 'progress' => $importProgress ?? []])
                </div>
            </div>
        </div>
    @endif

    @include('analytics::partials.scripts')
</x-layouts.admin>
