@php
    $user = auth()->user();

    $agendaLink = function (array $overrides = []) use ($activeView, $date, $visibleCalendarIds, $search): string {
        $params = array_filter(array_merge([
            'view' => $activeView,
            'date' => $date->toDateString(),
            'calendars' => $visibleCalendarIds,
            'q' => $search !== '' ? $search : null,
        ], $overrides), fn ($value): bool => ! ($value === null || $value === '' || $value === []));

        return route('admin.agenda.index').'?'.http_build_query($params);
    };

    $miniDays = [];
    $cursor = $date->copy()->startOfMonth()->startOfWeek();
    $miniEnd = $date->copy()->endOfMonth()->endOfWeek();

    while ($cursor->lessThanOrEqualTo($miniEnd)) {
        $miniDays[] = $cursor->copy();
        $cursor->addDay();
    }

    $pageSize = $calendarPageSize ?? 10;
    $initialCalendars = $calendars->take($pageSize);
    $hasMoreCalendars = $calendars->count() > $pageSize;
@endphp

<div class="rounded-2xl border border-gray-200/70 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-2 flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {{ ucfirst($date->locale('pt_BR')->translatedFormat('F \d\e Y')) }}
        </p>
        <div class="flex items-center gap-1">
            <a href="{{ $agendaLink(['date' => $date->copy()->subMonthNoOverflow()->toDateString()]) }}" class="rounded-md p-1 text-gray-400 hover:text-indigo-600"><x-icon name="chevron-left" class="h-3.5 w-3.5" /></a>
            <a href="{{ $agendaLink(['date' => $date->copy()->addMonthNoOverflow()->toDateString()]) }}" class="rounded-md p-1 text-gray-400 hover:text-indigo-600"><x-icon name="chevron-right" class="h-3.5 w-3.5" /></a>
        </div>
    </div>

    <div class="grid grid-cols-7 gap-0.5 text-center text-[10px] font-semibold uppercase text-gray-400">
        @foreach (['S', 'T', 'Q', 'Q', 'S', 'S', 'D'] as $label)
            <span>{{ $label }}</span>
        @endforeach
    </div>

    <div class="mt-1 grid grid-cols-7 gap-0.5 text-center text-xs">
        @foreach ($miniDays as $day)
            <a
                href="{{ $agendaLink(['view' => 'day', 'date' => $day->toDateString()]) }}"
                class="flex h-7 items-center justify-center rounded-md transition {{ $day->isToday() ? 'bg-indigo-600 font-semibold text-white' : ($day->month === $date->month ? 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' : 'text-gray-300 dark:text-gray-600') }}"
            >
                {{ $day->day }}
            </a>
        @endforeach
    </div>
</div>

<div class="rounded-2xl border border-gray-200/70 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-2 flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Meus calendários</p>
        <button type="button" data-agenda-calendar-create class="rounded-md p-1 text-gray-400 transition hover:text-indigo-600" title="Novo calendário">
            <x-icon name="plus" class="h-4 w-4" />
        </button>
    </div>

    <div
        data-agenda-calendar-list
        data-next-page="{{ $hasMoreCalendars ? 2 : 0 }}"
        data-last-page="{{ max(1, (int) ceil($calendars->count() / $pageSize)) }}"
        data-per-page="{{ $pageSize }}"
        class="max-h-72 space-y-1.5 overflow-y-auto overscroll-contain pr-1 [scrollbar-width:thin]"
    >
        @forelse ($initialCalendars as $calendar)
            @include('admin.agenda.partials.calendar-item', ['calendar' => $calendar, 'user' => $user])
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum calendário ainda.</p>
        @endforelse

        <div data-agenda-calendar-sentinel class="h-px"></div>

        <div data-agenda-calendar-loading class="hidden items-center justify-center gap-2 py-2 text-[11px] font-medium text-gray-400 dark:text-gray-500">
            <x-icon name="arrow-path" class="h-3.5 w-3.5 animate-spin" />
            Carregando…
        </div>

        <p data-agenda-calendar-end class="hidden py-1 text-center text-[11px] text-gray-400 dark:text-gray-500">
            Todos os calendários carregados.
        </p>
    </div>
</div>

@if ($writableCalendarIds !== [])
    <div class="rounded-2xl border border-gray-200/70 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Importar (.ics)</p>
        <form method="POST" action="{{ route('admin.agenda.import') }}" enctype="multipart/form-data" class="space-y-2">
            @csrf
            <select name="agenda_calendar_id" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                @foreach ($calendars->whereIn('id', $writableCalendarIds) as $calendar)
                    <option value="{{ $calendar->getKey() }}">{{ $calendar->name }}</option>
                @endforeach
            </select>
            <input type="file" name="file" accept=".ics,.ical,text/calendar" class="w-full text-xs text-gray-500 file:mr-2 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-indigo-600 dark:text-gray-400 dark:file:bg-indigo-500/10 dark:file:text-indigo-300">
            <button type="submit" class="w-full rounded-xl bg-gray-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">Importar arquivo</button>
        </form>
    </div>
@endif

<div class="rounded-2xl border border-gray-200/70 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-2 flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Disponibilidade</p>
        <button type="button" data-agenda-freebusy class="rounded-md p-1 text-gray-400 transition hover:text-indigo-600" title="Ver horários ocupados">
            <x-icon name="arrow-path" class="h-4 w-4" />
        </button>
    </div>
    <div data-agenda-freebusy-result class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
        <p class="text-gray-400 dark:text-gray-500">Toque em atualizar para ver os horários ocupados dos próximos dias.</p>
    </div>
</div>
