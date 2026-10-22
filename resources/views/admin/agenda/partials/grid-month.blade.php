@php
    $weekdayLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="grid grid-cols-7 border-b border-gray-200/70 bg-gray-50/60 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-400">
        @foreach ($weekdayLabels as $label)
            <div class="px-2 py-2">{{ $label }}</div>
        @endforeach
    </div>

    <div class="grid grid-cols-7 auto-rows-[7rem]">
        @foreach ($days as $key => $day)
            <div class="group relative flex flex-col gap-1 border-b border-r border-gray-100 p-1.5 last:border-r-0 dark:border-gray-800/70 {{ $day['date']->month !== $date->month ? 'bg-gray-50/60 dark:bg-gray-950/40' : '' }}">
                <div class="flex items-center justify-between">
                    <a
                        href="{{ route('admin.agenda.index').'?'.http_build_query(array_filter(['view' => 'day', 'date' => $day['date']->toDateString(), 'calendars' => $visibleCalendarIds])) }}"
                        class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold transition {{ $day['is_today'] ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                    >
                        {{ $day['date']->day }}
                    </a>
                    @if ($canCreate)
                        <button
                            type="button"
                            data-agenda-create
                            data-date="{{ $day['date']->toDateString() }}"
                            class="rounded-md p-0.5 text-gray-300 opacity-0 transition group-hover:opacity-100 hover:text-indigo-600"
                            title="Novo evento"
                        >
                            <x-icon name="plus" class="h-3.5 w-3.5" />
                        </button>
                    @endif
                </div>

                <div class="flex min-h-0 flex-1 flex-col gap-0.5 overflow-hidden">
                    @foreach (collect($day['occurrences'])->take(3) as $occurrence)
                        @include('admin.agenda.partials.event-chip', ['occurrence' => $occurrence])
                    @endforeach

                    @if (count($day['occurrences']) > 3)
                        <a
                            href="{{ route('admin.agenda.index').'?'.http_build_query(array_filter(['view' => 'day', 'date' => $day['date']->toDateString(), 'calendars' => $visibleCalendarIds])) }}"
                            class="px-1.5 text-[11px] font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                        >
                            + {{ count($day['occurrences']) - 3 }} mais
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
