@php
    $grouped = collect($occurrences)->groupBy(fn ($occurrence) => $occurrence->start->toDateString());
@endphp

<div class="space-y-4">
    @forelse ($grouped as $day => $items)
        <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200/70 bg-gray-50/60 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-200">
                {{ ucfirst(\Illuminate\Support\Carbon::parse($day)->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y')) }}
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($items as $occurrence)
                    @php $hex = $occurrence->color()->hex(); @endphp
                    <button
                        type="button"
                        data-agenda-event
                        data-event='@json($occurrence->toModalPayload())'
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition hover:bg-gray-50 dark:hover:bg-gray-800/60"
                    >
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $hex }}"></span>
                        <span class="w-28 shrink-0 text-xs text-gray-500 dark:text-gray-400">
                            {{ $occurrence->isAllDay() ? 'Dia inteiro' : $occurrence->start->format('H:i').' – '.$occurrence->end->format('H:i') }}
                        </span>
                        <span class="flex-1 truncate text-sm font-medium text-gray-800 dark:text-gray-100 {{ $occurrence->isCancelled() ? 'line-through opacity-60' : '' }}">{{ $occurrence->title() }}</span>
                        @if ($occurrence->location())
                            <span class="hidden items-center gap-1 text-xs text-gray-400 sm:flex">
                                <x-icon name="map-pin" class="h-3.5 w-3.5" />
                                {{ $occurrence->location() }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            Nenhum evento encontrado no período.
        </div>
    @endforelse
</div>
