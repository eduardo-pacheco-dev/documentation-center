@php
    use App\Enums\AgendaColor;
    use App\Enums\AgendaEventStatus;
    use App\Enums\AgendaEventVisibility;
    use App\Enums\AgendaRecurrenceFrequency;
    use App\Enums\AgendaReminderMethod;
    use App\Enums\AgendaTransparency;

    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    $writableCalendars = $calendars->whereIn('id', $writableCalendarIds);
    $weekdayOptions = [1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sáb', 7 => 'Dom'];
@endphp

<div
    id="agenda-event-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="agenda-event-modal-title"
    class="fixed inset-0 z-[100] hidden"
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[92vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 dark:bg-gray-900">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="agenda-event-modal-title" data-agenda-event-title class="text-base font-semibold text-gray-900 dark:text-gray-100">Novo evento</h2>
                <p data-agenda-event-organizer class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"></p>
            </div>
            <button type="button" data-modal-close class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800" aria-label="Fechar">
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>

        <div class="mt-4 flex items-center gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1 text-xs font-semibold dark:border-gray-800 dark:bg-gray-950/40">
            <button type="button" data-agenda-step-tab="1" data-active="true" class="flex-1 rounded-lg px-2 py-1.5 text-gray-500 transition data-[active=true]:bg-indigo-600 data-[active=true]:text-white dark:text-gray-400">
                1. Detalhes
            </button>
            <button type="button" data-agenda-step-tab="2" data-active="false" class="flex-1 rounded-lg px-2 py-1.5 text-gray-500 transition data-[active=true]:bg-indigo-600 data-[active=true]:text-white dark:text-gray-400">
                2. Repetição
            </button>
            <button type="button" data-agenda-step-tab="3" data-active="false" class="flex-1 rounded-lg px-2 py-1.5 text-gray-500 transition data-[active=true]:bg-indigo-600 data-[active=true]:text-white dark:text-gray-400">
                3. Extras
            </button>
        </div>

        <form
            data-agenda-event-form
            data-store-url="{{ route('admin.agenda.events.store') }}"
            method="POST"
            action="{{ route('admin.agenda.events.store') }}"
            class="mt-4 space-y-4"
        >
            @csrf
            <input type="hidden" name="_method" value="POST" data-agenda-event-method>
            <input type="hidden" name="timezone" value="{{ config('app.timezone') }}">
            <input type="hidden" name="recurrence_id" data-agenda-recurrence-id>

            <div data-agenda-step="1" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_14rem]">
                <div>
                    <label for="agenda-event-title-field" class="{{ $label }}">Título</label>
                    <input id="agenda-event-title-field" data-agenda-field="title" type="text" name="title" required maxlength="255" class="{{ $input }}">
                </div>
                <div>
                    <label for="agenda-event-calendar" class="{{ $label }}">Calendário</label>
                    <select id="agenda-event-calendar" data-agenda-field="agenda_calendar_id" name="agenda_calendar_id" class="{{ $input }}" required>
                        @foreach ($writableCalendars as $calendar)
                            <option value="{{ $calendar->getKey() }}">{{ $calendar->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" data-agenda-field="is_all_day" name="is_all_day" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                Dia inteiro
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="agenda-event-start" class="{{ $label }}">Início</label>
                    <input id="agenda-event-start" data-agenda-field="start_at" type="datetime-local" name="start_at" required class="{{ $input }}">
                </div>
                <div>
                    <label for="agenda-event-end" class="{{ $label }}">Término</label>
                    <input id="agenda-event-end" data-agenda-field="end_at" type="datetime-local" name="end_at" required class="{{ $input }}">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="agenda-event-location" class="{{ $label }}"><x-icon name="map-pin" class="mr-1 inline h-4 w-4" />Local</label>
                    <input id="agenda-event-location" data-agenda-field="location" type="text" name="location" maxlength="255" class="{{ $input }}">
                </div>
                <div>
                    <label for="agenda-event-meeting" class="{{ $label }}"><x-icon name="link" class="mr-1 inline h-4 w-4" />Link da reunião</label>
                    <input id="agenda-event-meeting" data-agenda-field="meeting_url" type="url" name="meeting_url" maxlength="2048" placeholder="https://" class="{{ $input }}">
                </div>
            </div>

            <div>
                <label for="agenda-event-description" class="{{ $label }}">Descrição</label>
                <textarea id="agenda-event-description" data-agenda-field="description" name="description" rows="3" maxlength="10000" class="{{ $input }}"></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="agenda-event-status" class="{{ $label }}">Status</label>
                    <select id="agenda-event-status" data-agenda-field="status" name="status" class="{{ $input }}">
                        @foreach (AgendaEventStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="agenda-event-visibility" class="{{ $label }}">Visibilidade</label>
                    <select id="agenda-event-visibility" data-agenda-field="visibility" name="visibility" class="{{ $input }}">
                        @foreach (AgendaEventVisibility::cases() as $visibility)
                            <option value="{{ $visibility->value }}">{{ $visibility->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="agenda-event-transparency" class="{{ $label }}">Disponibilidade</label>
                    <select id="agenda-event-transparency" data-agenda-field="transparency" name="transparency" class="{{ $input }}">
                        @foreach (AgendaTransparency::cases() as $transparency)
                            <option value="{{ $transparency->value }}">{{ $transparency->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <span class="{{ $label }}">Cor</span>
                <div class="mt-2 flex flex-wrap gap-2.5">
                    <label class="cursor-pointer" title="Cor do calendário">
                        <input type="radio" name="color" value="" data-agenda-field="color" class="peer sr-only">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-dashed border-gray-300 text-[10px] font-semibold text-gray-400 peer-checked:border-indigo-500 peer-checked:text-indigo-600 dark:border-gray-600">—</span>
                    </label>
                    @foreach (AgendaColor::cases() as $color)
                        <label class="cursor-pointer" title="{{ $color->label() }}">
                            <input type="radio" name="color" value="{{ $color->value }}" data-agenda-field="color" class="peer sr-only">
                            <span class="block h-7 w-7 rounded-full peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2 peer-checked:ring-offset-white dark:peer-checked:ring-offset-gray-900" style="background-color: {{ $color->hex() }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>
            </div>

            <div data-agenda-step="2" class="space-y-4" hidden>
            <fieldset class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Repetição</legend>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="agenda-event-frequency" class="{{ $label }}">Frequência</label>
                        <select id="agenda-event-frequency" data-agenda-field="repeat_frequency" name="repeat_frequency" class="{{ $input }}">
                            <option value="">Não repetir</option>
                            @foreach (AgendaRecurrenceFrequency::cases() as $frequency)
                                <option value="{{ $frequency->value }}">{{ $frequency->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="agenda-event-interval" class="{{ $label }}">A cada</label>
                        <input id="agenda-event-interval" data-agenda-field="repeat_interval" type="number" min="1" max="99" value="1" name="repeat_interval" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="agenda-event-end-mode" class="{{ $label }}">Termina</label>
                        <select id="agenda-event-end-mode" data-agenda-field="repeat_end_mode" name="repeat_end_mode" class="{{ $input }}">
                            <option value="never">Nunca</option>
                            <option value="until">Em uma data</option>
                            <option value="count">Após repetições</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-2" data-agenda-repeat-until hidden>
                    <div>
                        <label for="agenda-event-repeat-until" class="{{ $label }}">Repetir até</label>
                        <input id="agenda-event-repeat-until" data-agenda-field="repeat_until" type="date" name="repeat_until" class="{{ $input }}">
                    </div>
                </div>

                <div class="mt-3" data-agenda-repeat-count hidden>
                    <label for="agenda-event-repeat-count" class="{{ $label }}">Número de repetições</label>
                    <input id="agenda-event-repeat-count" data-agenda-field="repeat_count" type="number" min="1" max="365" name="repeat_count" class="{{ $input }}">
                </div>

                <div class="mt-3" data-agenda-repeat-weekdays hidden>
                    <span class="{{ $label }}">Dias da semana</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($weekdayOptions as $value => $weekday)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="repeat_weekdays[]" value="{{ $value }}" data-agenda-weekday class="peer sr-only">
                                <span class="flex h-8 w-10 items-center justify-center rounded-lg border border-gray-200 text-xs font-semibold text-gray-500 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 dark:border-gray-700 dark:text-gray-400 dark:peer-checked:border-indigo-500 dark:peer-checked:bg-indigo-500/10 dark:peer-checked:text-indigo-300">{{ $weekday }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </fieldset>

            <fieldset data-agenda-scope-fieldset hidden class="rounded-xl border border-amber-200 bg-amber-50/60 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Este é um evento recorrente</legend>
                <div class="grid gap-2 sm:grid-cols-3">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="radio" name="scope" value="this" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700"> Somente esta
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="radio" name="scope" value="following" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700"> Esta e as seguintes
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="radio" name="scope" value="all" checked class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700"> Todos os eventos
                    </label>
                </div>
            </fieldset>

            <fieldset class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Convidados</legend>
                <div>
                    <label for="agenda-event-attendees" class="{{ $label }}">E-mails (separados por vírgula)</label>
                    <input id="agenda-event-attendees" data-agenda-attendees type="text" placeholder="email@exemplo.com, outro@exemplo.com" class="{{ $input }}">
                </div>
                <div data-agenda-attendee-list class="mt-3 space-y-1.5"></div>
                <div class="mt-3 space-y-2">
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" data-agenda-field="attendees_can_invite_others" name="attendees_can_invite_others" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                        Convidados podem convidar outros
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" data-agenda-field="attendees_can_modify" name="attendees_can_modify" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                        Convidados podem modificar o evento
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" data-agenda-field="attendees_can_see_other_attendees" name="attendees_can_see_other_attendees" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                        Convidados podem ver a lista de convidados
                    </label>
                </div>
            </fieldset>
            </div>

            <div data-agenda-step="3" class="space-y-4" hidden>
            <fieldset class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lembretes</legend>
                <div data-agenda-reminders class="space-y-2"></div>
                <button type="button" data-agenda-add-reminder class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-2.5 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-400">
                    <x-icon name="plus" class="h-3.5 w-3.5" />
                    Adicionar lembrete
                </button>
            </fieldset>

            <fieldset class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Anexos</legend>
                <div data-agenda-attachments class="space-y-2"></div>
                <button type="button" data-agenda-add-attachment class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-2.5 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-400">
                    <x-icon name="paper-clip" class="h-3.5 w-3.5" />
                    Adicionar anexo
                </button>
            </fieldset>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" data-modal-close class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                        Cancelar
                    </button>
                    <button type="button" data-agenda-step-prev style="display: none;" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                        Anterior
                    </button>
                    <button type="button" data-agenda-step-next class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        Próximo
                    </button>
                    <button type="submit" data-agenda-step-submit style="display: none;" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar
                    </button>
                </div>

                <div class="flex items-center gap-2" data-agenda-event-admin hidden>
                    <button type="button" data-agenda-duplicate class="rounded-xl border border-gray-200/70 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        Duplicar
                    </button>
                    <button type="button" data-agenda-delete class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                        Excluir
                    </button>
                </div>
            </div>
        </form>

        <form data-agenda-respond-form method="POST" action="#" class="mt-3 hidden rounded-xl border border-gray-200 p-3 dark:border-gray-800">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" data-agenda-respond-status>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sua resposta</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <button type="button" data-agenda-respond="accepted" class="rounded-lg bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 transition hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400">Vou</button>
                <button type="button" data-agenda-respond="tentative" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400">Talvez</button>
                <button type="button" data-agenda-respond="declined" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400">Não vou</button>
            </div>
        </form>

        <form id="agenda-event-delete-form" method="POST" action="#" class="hidden">
            @csrf
            @method('DELETE')
            <input type="hidden" name="scope" data-agenda-delete-scope>
            <input type="hidden" name="recurrence_id" data-agenda-delete-recurrence>
        </form>

        <form id="agenda-event-duplicate-form" method="POST" action="#" class="hidden">
            @csrf
        </form>
    </div>
</div>

<template data-agenda-reminder-template>
    <div class="flex items-center gap-2" data-agenda-reminder-row>
        <select name="reminders[__INDEX__][method]" class="w-40 rounded-xl border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            @foreach (AgendaReminderMethod::cases() as $method)
                <option value="{{ $method->value }}">{{ $method->label() }}</option>
            @endforeach
        </select>
        <input type="number" min="0" max="40320" name="reminders[__INDEX__][minutes_before]" placeholder="Minutos antes" class="w-full rounded-xl border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
        <button type="button" data-agenda-remove-reminder class="rounded-lg p-1.5 text-gray-400 transition hover:text-red-600"><x-icon name="x-mark" class="h-4 w-4" /></button>
    </div>
</template>

<template data-agenda-attachment-template>
    <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_auto]" data-agenda-attachment-row>
        <input type="text" name="attachments[__INDEX__][title]" placeholder="Título" class="rounded-xl border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
        <input type="url" name="attachments[__INDEX__][url]" placeholder="https://" class="rounded-xl border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
        <button type="button" data-agenda-remove-attachment class="rounded-lg p-1.5 text-gray-400 transition hover:text-red-600"><x-icon name="x-mark" class="h-4 w-4" /></button>
    </div>
</template>
