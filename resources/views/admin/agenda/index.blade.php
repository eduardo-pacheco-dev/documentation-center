@php
    use Illuminate\Support\Carbon;

    $periodLabel = match ($activeView) {
        'week' => $rangeStart->locale('pt_BR')->translatedFormat('d \d\e F')
            .' – '.$rangeEnd->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y'),
        'day' => ucfirst($date->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y')),
        'list' => 'Próximos 30 dias',
        default => ucfirst($date->locale('pt_BR')->translatedFormat('F \d\e Y')),
    };

    $baseQuery = function (array $overrides = []) use ($activeView, $date, $visibleCalendarIds, $search): string {
        $params = array_merge([
            'view' => $activeView,
            'date' => $date->toDateString(),
            'calendars' => $visibleCalendarIds,
            'q' => $search !== '' ? $search : null,
        ], $overrides);

        $params = array_filter($params, fn ($value): bool => ! ($value === null || $value === '' || $value === []));

        return '?'.http_build_query($params);
    };

    $step = fn (int $direction): array => match ($activeView) {
        'week' => ['date' => $date->copy()->addWeeks($direction)->toDateString()],
        'day' => ['date' => $date->copy()->addDays($direction)->toDateString()],
        'list' => ['date' => $date->copy()->addDays(30 * $direction)->toDateString()],
        default => ['date' => $date->copy()->addMonthsNoOverflow($direction)->toDateString()],
    };

    $agendaUrl = fn (array $overrides = []): string => route('admin.agenda.index').$baseQuery($overrides);

    $views = [
        'month' => ['label' => 'Mês', 'icon' => 'calendar-days'],
        'week' => ['label' => 'Semana', 'icon' => 'calendar-days'],
        'day' => ['label' => 'Dia', 'icon' => 'clock'],
        'list' => ['label' => 'Agenda', 'icon' => 'clipboard-document-list'],
    ];

    $iconButton = 'inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-indigo-500/40';
@endphp

<x-layouts.admin :title="'Agenda'">
    <div class="space-y-5" data-agenda-root>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Agenda</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $periodLabel }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white p-0.5 dark:border-gray-800 dark:bg-gray-900">
                    <a href="{{ $agendaUrl($step(-1)) }}" class="{{ $iconButton }} border-0"><x-icon name="chevron-left" /></a>
                    <a href="{{ $agendaUrl(['date' => Carbon::today()->toDateString()]) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-indigo-600 dark:text-gray-300">Hoje</a>
                    <a href="{{ $agendaUrl($step(1)) }}" class="{{ $iconButton }} border-0"><x-icon name="chevron-right" /></a>
                </div>

                <div class="flex items-center gap-0.5 rounded-xl border border-gray-200 bg-white p-0.5 dark:border-gray-800 dark:bg-gray-900">
                    @foreach ($views as $key => $meta)
                        <a
                            href="{{ $agendaUrl(['view' => $key]) }}"
                            class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition {{ $activeView === $key ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:text-indigo-600 dark:text-gray-300' }}"
                        >
                            {{ $meta['label'] }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('admin.agenda.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="view" value="{{ $activeView }}">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    @foreach ($visibleCalendarIds as $calendarId)
                        <input type="hidden" name="calendars[]" value="{{ $calendarId }}">
                    @endforeach
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Buscar eventos"
                        class="w-44 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                    >
                </form>

                <a href="{{ route('admin.agenda.export', array_filter(['calendar' => count($visibleCalendarIds) === 1 ? $visibleCalendarIds[0] : null])) }}" class="{{ $iconButton }}" title="Exportar .ics"><x-icon name="arrow-down-tray" /></a>

                @if ($canCreate)
                    <button type="button" data-agenda-create data-date="{{ $date->toDateString() }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        <x-icon name="plus" class="h-4 w-4" />
                        Novo evento
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[18rem_minmax(0,1fr)]">
            <div class="order-2 space-y-4 lg:order-1">
                @include('admin.agenda.partials.sidebar')
            </div>

            <div class="order-1 min-w-0 lg:order-2">
                @switch($activeView)
                    @case('week')
                        @include('admin.agenda.partials.time-grid', ['days' => $days, 'title' => null])
                        @break
                    @case('day')
                        @include('admin.agenda.partials.time-grid', ['days' => $days, 'title' => null])
                        @break
                    @case('list')
                        @include('admin.agenda.partials.list')
                        @break
                    @default
                        @include('admin.agenda.partials.grid-month')
                @endswitch
            </div>
        </div>
    </div>

    @include('admin.agenda.partials.event-modal')
    @include('admin.agenda.partials.calendar-modal')
    @include('admin.agenda.partials.scripts')
</x-layouts.admin>
