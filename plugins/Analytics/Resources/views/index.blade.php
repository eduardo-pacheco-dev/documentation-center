@php
    $cards = [
        ['label' => 'Projetos ativos', 'value' => $summary['projects']['active'], 'hint' => $summary['projects']['total'].' no total', 'icon' => 'briefcase', 'tone' => 'indigo'],
        ['label' => 'OS em aberto', 'value' => $summary['work_orders']['open'], 'hint' => $summary['work_orders']['total'].' no total', 'icon' => 'clipboard-document-list', 'tone' => 'sky'],
        ['label' => 'Tarefas pendentes', 'value' => $summary['tasks']['pending'], 'hint' => $summary['tasks']['completed'].' concluídas', 'icon' => 'check-circle', 'tone' => 'emerald'],
        ['label' => 'Documentos no período', 'value' => $summary['documents']['in_period'], 'hint' => \Illuminate\Support\Number::fileSize($summary['documents']['size']).' armazenados', 'icon' => 'document-text', 'tone' => 'violet'],
        ['label' => 'Formulários pendentes', 'value' => $summary['forms']['pending'], 'hint' => $summary['forms']['total'].' no total', 'icon' => 'document-check', 'tone' => 'amber'],
        ['label' => 'Eventos no período', 'value' => $summary['agenda']['in_period'], 'hint' => $summary['agenda']['total'].' na agenda', 'icon' => 'calendar-days', 'tone' => 'rose'],
    ];

    if (is_array($recordsSummary)) {
        $cards[] = ['label' => 'Registros FAMs', 'value' => $recordsSummary['total'], 'hint' => $recordsSummary['versions'].' versões no histórico', 'icon' => 'table-cells', 'tone' => 'indigo'];
    }

    if (is_array($aflSummary)) {
        $cards[] = ['label' => 'Registros AFL', 'value' => $aflSummary['total'], 'hint' => $aflSummary['versions'].' versões no histórico', 'icon' => 'flag', 'tone' => 'rose'];
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

    <x-analytics::toolbar
        :periods="$periods"
        :days="$days"
        :scope="$scope"
        :scope-query="$scopeQuery"
        :is-admin="auth()->user()?->is_admin"
    />

    @error('import')
        <x-analytics::error-summary
            class="mt-4"
            title="Não foi possível importar a planilha."
            :lines="explode(chr(10), $message)"
        />
    @enderror

    @error('file')
        <x-ui.alert tone="error" class="mt-4">{{ $message }}</x-ui.alert>
    @enderror

    <x-analytics::metric-grid :cards="$cards" class="mt-4" />

    <x-analytics::tabs :tabs="$tabs" class="mt-6" />

    <div class="mt-4">
        @include('analytics::partials.overview')
        @include('analytics::partials.fams')
        @include('analytics::partials.afl')
        @include('analytics::partials.snapshots')
    </div>

    @include('analytics::partials.scripts')
</x-layouts.admin>
