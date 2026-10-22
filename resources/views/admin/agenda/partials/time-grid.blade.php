@php
    $columns = array_values($days);
    $hourHeight = \App\Services\Agenda\AgendaGridBuilder::HOUR_HEIGHT;
    $single = count($columns) === 1;
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex border-b border-gray-200/70 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-950/40">
        <div class="w-14 shrink-0"></div>
        @foreach ($columns as $day)
            <div class="flex-1 border-l border-gray-200/70 px-1 py-1.5 text-center dark:border-gray-800">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ $day['date']->locale('pt_BR')->translatedFormat('D') }}</div>
                <div class="text-sm font-semibold {{ $day['is_today'] ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-200' }}">
                    {{ $single ? $day['date']->format('d/m/Y') : $day['date']->day }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex border-b border-gray-200/70 dark:border-gray-800">
        <div class="w-14 shrink-0 px-1 py-1 text-right text-[10px] font-semibold uppercase text-gray-400">Dia</div>
        @foreach ($columns as $day)
            <div class="min-h-[2.25rem] flex-1 space-y-0.5 border-l border-gray-200/70 p-1 dark:border-gray-800">
                @foreach ($day['all_day'] as $occurrence)
                    @include('admin.agenda.partials.event-chip', ['occurrence' => $occurrence])
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="flex max-h-[38rem] overflow-y-auto" data-agenda-grid>
        <div class="w-14 shrink-0">
            @for ($hour = 0; $hour < 24; $hour++)
                <div class="h-12 border-b border-gray-100 pr-1 text-right text-[10px] text-gray-400 dark:border-gray-800/70">{{ sprintf('%02d:00', $hour) }}</div>
            @endfor
        </div>

        @foreach ($columns as $day)
            <div
                class="relative flex-1 border-l border-gray-200/70 dark:border-gray-800"
                data-agenda-day-column="{{ $day['date']->toDateString() }}"
                style="height: {{ 24 * $hourHeight }}px; background-image: repeating-linear-gradient(to bottom, transparent, transparent {{ $hourHeight - 1 }}px, rgb(0 0 0 / 0.06) {{ $hourHeight - 1 }}px, rgb(0 0 0 / 0.06) {{ $hourHeight }}px);"
            >
                @foreach ($day['timed'] as $segment)
                    @php
                        $occurrence = $segment['occurrence'];
                        $hex = $occurrence->color()->hex();
                    @endphp
                    <button
                        type="button"
                        data-agenda-event
                        data-agenda-draggable
                        data-event='@json($occurrence->toModalPayload())'
                        style="top: {{ $segment['top'] }}px; height: {{ $segment['height'] }}px; left: calc({{ $segment['left'] }}% + 2px); width: calc({{ $segment['width'] }}% - 4px); background-color: {{ $hex }}26; border-left: 3px solid {{ $hex }};"
                        class="absolute overflow-hidden rounded-md px-1 py-0.5 text-left text-[11px] font-medium text-gray-700 transition hover:brightness-95 dark:text-gray-100 {{ $occurrence->isCancelled() ? 'line-through opacity-60' : '' }}"
                    >
                        <span class="font-semibold">{{ $occurrence->start->format('H:i') }}</span>
                        {{ $occurrence->title() }}
                        <span data-agenda-resize class="absolute inset-x-0 bottom-0 block h-1.5 cursor-ns-resize"></span>
                    </button>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
