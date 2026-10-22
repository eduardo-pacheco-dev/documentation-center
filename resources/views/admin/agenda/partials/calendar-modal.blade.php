@php
    use App\Enums\AgendaCalendarRole;
    use App\Enums\AgendaColor;
    use App\Enums\AgendaReminderMethod;

    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    $timezones = [
        'America/Sao_Paulo' => 'Brasília (GMT-3)',
        'America/Manaus' => 'Manaus (GMT-4)',
        'America/Belem' => 'Belém (GMT-3)',
        'America/Fortaleza' => 'Fortaleza (GMT-3)',
        'America/Recife' => 'Recife (GMT-3)',
        'America/Cuiaba' => 'Cuiabá (GMT-4)',
        'America/Rio_Branco' => 'Rio Branco (GMT-5)',
        'America/Noronha' => 'Fernando de Noronha (GMT-2)',
        'UTC' => 'UTC',
    ];
    $storeUrl = route('admin.agenda.calendars.store');
    $updateUrl = route('admin.agenda.calendars.update', ['agendaCalendar' => '__ID__']);
    $deleteUrl = route('admin.agenda.calendars.destroy', ['agendaCalendar' => '__ID__']);
    $defaultUrl = route('admin.agenda.calendars.default', ['agendaCalendar' => '__ID__']);
    $shareUrl = route('admin.agenda.calendars.share', ['agendaCalendar' => '__ID__']);
    $unshareUrl = route('admin.agenda.calendars.unshare', ['agendaCalendar' => '__ID__', 'share' => '__SHARE__']);
@endphp

<div
    id="agenda-calendar-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="agenda-calendar-modal-title"
    class="fixed inset-0 z-[100] hidden"
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[92vh] w-full max-w-lg -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 dark:bg-gray-900">
        <div class="flex items-start justify-between gap-3">
            <h2 id="agenda-calendar-modal-title" data-agenda-calendar-title class="text-base font-semibold text-gray-900 dark:text-gray-100">Novo calendário</h2>
            <button type="button" data-modal-close class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800" aria-label="Fechar">
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>

        <form
            data-agenda-calendar-form
            data-store-url="{{ $storeUrl }}"
            data-update-url="{{ $updateUrl }}"
            method="POST"
            action="{{ $storeUrl }}"
            class="mt-4 space-y-4"
        >
            @csrf
            <input type="hidden" name="_method" value="POST" data-agenda-calendar-method>

            <div>
                <label for="agenda-calendar-name" class="{{ $label }}">Nome</label>
                <input id="agenda-calendar-name" data-agenda-calendar-field="name" type="text" name="name" required maxlength="120" class="{{ $input }}">
            </div>

            <div>
                <label for="agenda-calendar-description" class="{{ $label }}">Descrição</label>
                <textarea id="agenda-calendar-description" data-agenda-calendar-field="description" name="description" rows="2" maxlength="2000" class="{{ $input }}"></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="agenda-calendar-timezone" class="{{ $label }}">Fuso horário</label>
                    <select id="agenda-calendar-timezone" data-agenda-calendar-field="timezone" name="timezone" class="{{ $input }}">
                        @foreach ($timezones as $value => $labelText)
                            <option value="{{ $value }}">{{ $labelText }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="agenda-calendar-reminder-method" class="{{ $label }}">Lembrete padrão</label>
                    <div class="mt-1 flex items-center gap-2">
                        <select id="agenda-calendar-reminder-method" name="default_reminders[0][method]" class="w-full rounded-xl border border-gray-300 bg-white px-2 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @foreach (AgendaReminderMethod::cases() as $method)
                                <option value="{{ $method->value }}">{{ $method->label() }}</option>
                            @endforeach
                        </select>
                        <input type="number" min="0" max="40320" name="default_reminders[0][minutes_before]" placeholder="Min" class="w-24 rounded-xl border border-gray-300 bg-white px-2 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    </div>
                </div>
            </div>

            <div>
                <span class="{{ $label }}">Cor</span>
                <div class="mt-2 flex flex-wrap gap-2.5">
                    @foreach (AgendaColor::cases() as $color)
                        <label class="cursor-pointer" title="{{ $color->label() }}">
                            <input type="radio" name="color" value="{{ $color->value }}" data-agenda-calendar-color class="peer sr-only">
                            <span class="block h-7 w-7 rounded-full peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2 peer-checked:ring-offset-white dark:peer-checked:ring-offset-gray-900" style="background-color: {{ $color->hex() }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar
                    </button>
                    <button type="button" data-modal-close class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                        Cancelar
                    </button>
                </div>

                <div class="flex items-center gap-2" data-agenda-calendar-admin hidden>
                    <button type="submit" form="agenda-calendar-default-form" class="rounded-xl border border-gray-200/70 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        Tornar padrão
                    </button>
                    <button type="submit" form="agenda-calendar-delete-form" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                        Excluir
                    </button>
                </div>
            </div>
        </form>

        <form id="agenda-calendar-delete-form" data-agenda-calendar-delete-form method="POST" action="#" data-confirm-delete="Excluir este calendário e todos os seus eventos?" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <form id="agenda-calendar-default-form" data-agenda-calendar-default-form method="POST" action="#" class="hidden">
            @csrf
            @method('PATCH')
        </form>

        <div data-agenda-calendar-shares hidden class="mt-4 rounded-xl border border-gray-200 p-3 dark:border-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Compartilhado com</p>
            <div data-agenda-calendar-share-list class="mt-2 space-y-1.5"></div>

            <form data-agenda-calendar-share-form method="POST" action="#" class="mt-3 flex flex-wrap items-center gap-2">
                @csrf
                <input type="email" name="email" placeholder="email@exemplo.com" required class="min-w-0 flex-1 rounded-xl border border-gray-300 bg-white px-2.5 py-1.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <select name="role" class="rounded-xl border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @foreach (AgendaCalendarRole::assignable() as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">
                    <x-icon name="user-plus" class="h-3.5 w-3.5" />
                    Compartilhar
                </button>
            </form>
        </div>
    </div>
</div>

<script type="application/json" data-agenda-calendar-urls>{!! json_encode([
    'update' => $updateUrl,
    'delete' => $deleteUrl,
    'default' => $defaultUrl,
    'share' => $shareUrl,
    'unshare' => $unshareUrl,
]) !!}</script>
