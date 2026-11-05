@props(['schedule'])

@php
    $recipientChips = old('summary_recipients')
        ? \Plugins\FollowUp\Models\FollowUpSetting::parseSummaryRecipients((string) old('summary_recipients'))
        : $schedule['recipientsList'];

    $ccChips = old('summary_cc_recipients')
        ? \Plugins\FollowUp\Models\FollowUpSetting::parseSummaryRecipients((string) old('summary_cc_recipients'))
        : $schedule['ccRecipients'];

    $selectedDays = collect((array) old('summary_days', $schedule['days']))
        ->map(fn ($day): int => (int) $day)
        ->all();

    $hasScheduleErrors = $errors->hasAny([
        'summary_period',
        'summary_times',
        'summary_days',
        'summary_days.*',
        'email_summary_enabled',
        'summary_recipients',
        'summary_cc_recipients',
    ]);
@endphp

<div
    x-data="{
        open: {{ $hasScheduleErrors ? 'true' : 'false' }},
        summaryPeriod: @js(old('summary_period', $schedule['period'])),
        recipientsList: @js($recipientChips),
        ccList: @js($ccChips),
        newRecipient: '',
        newCc: '',
        addRecipient() {
            const email = this.newRecipient.trim().toLowerCase();

            if (! email.includes('@') || this.recipientsList.includes(email)) {
                return;
            }

            this.recipientsList.push(email);
            this.newRecipient = '';
        },
        removeRecipient(email) {
            this.recipientsList = this.recipientsList.filter((item) => item !== email);
        },
        addCc() {
            const email = this.newCc.trim().toLowerCase();

            if (! email.includes('@') || this.ccList.includes(email)) {
                return;
            }

            this.ccList.push(email);
            this.newCc = '';
        },
        removeCc(email) {
            this.ccList = this.ccList.filter((item) => item !== email);
        },
        close() {
            this.open = false;
        },
    }"
    x-on:open-follow-up-schedule.window="open = true"
    x-on:keydown.escape.window="open && close()"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[100] flex items-end justify-center overflow-hidden sm:items-center sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="follow-up-schedule-title"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" x-on:click="close()"></div>

        <div class="relative flex h-[calc(100dvh-1rem)] max-h-[calc(100dvh-1rem)] w-full max-w-xl flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl dark:bg-[#1c1c1b] sm:h-auto sm:max-h-[90vh] sm:rounded-3xl">
            <header class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-6 py-5 dark:border-white/10">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="clock" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="follow-up-schedule-title" class="text-base font-semibold text-gray-900 dark:text-white">
                            Agendamento do resumo
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Defina quando e quais follow-ups são enviados por e-mail.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    x-on:click="close()"
                    class="rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"
                    aria-label="Fechar agendamento"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                @if ($hasScheduleErrors)
                    <div class="mb-5 flex items-start gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>Revise os campos destacados para salvar o agendamento.</span>
                    </div>
                @endif

                <form id="follow-up-summary-schedule-form" method="POST" action="{{ route('admin.follow-ups.settings.schedule') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-gray-200/80 px-4 py-3 dark:border-white/10">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Agendamento ativo</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Quando desativado, nenhum resumo periódico é enviado.</p>
                        </div>

                        <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                            <input type="hidden" name="email_summary_enabled" value="0">
                            <input
                                type="checkbox"
                                name="email_summary_enabled"
                                value="1"
                                @checked(old('email_summary_enabled', $schedule['enabled']))
                                class="peer sr-only"
                            >
                            <span class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500/40 dark:bg-white/20 dark:peer-checked:bg-indigo-500" aria-hidden="true"></span>
                            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5" aria-hidden="true"></span>
                            <span class="sr-only">Ativar ou desabilitar o agendamento</span>
                        </label>
                    </div>
                    @error('email_summary_enabled')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="schedule-subject-prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Prefixo do assunto</label>
                            <input
                                id="schedule-subject-prefix"
                                name="email_subject_prefix"
                                type="text"
                                value="{{ old('email_subject_prefix', $schedule['prefix']) }}"
                                maxlength="150"
                                required
                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ex.: “{{ $schedule['prefix'] }} — Resumo {{ now()->format('d/m/Y') }}”.</p>
                            @error('email_subject_prefix')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="schedule-summary-period" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Período do resumo</label>
                            <select
                                id="schedule-summary-period"
                                name="summary_period"
                                x-model="summaryPeriod"
                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                                @foreach ($schedule['periodOptions'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('summary_period', $schedule['period']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                x-text="summaryPeriod === 'all_existing'
                                    ? 'Inclui follow-ups em aberto, concluídos e cancelados.'
                                    : (summaryPeriod === 'last_30_days'
                                        ? 'Somente follow-ups em aberto com prazo entre 30 dias atrás e hoje.'
                                        : 'Todos os follow-ups em aberto, sem recorte de prazo.')"
                            ></p>
                            @error('summary_period')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="schedule-summary-recipients" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Destinatários (Para)</label>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <input
                                id="schedule-summary-recipients"
                                type="email"
                                x-model="newRecipient"
                                x-on:keydown.enter.prevent="addRecipient()"
                                placeholder="financeiro@exemplo.com"
                                class="h-10 min-w-0 flex-1 rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                            <x-ui.action type="button" variant="secondary" icon="plus" x-on:click="addRecipient()">Adicionar</x-ui.action>
                        </div>

                        <input type="hidden" name="summary_recipients" x-bind:value="recipientsList.join(', ')">

                        <div class="mt-2 flex flex-wrap gap-1.5" x-show="recipientsList.length" x-cloak>
                            <template x-for="email in recipientsList" :key="email">
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                    <span x-text="email"></span>
                                    <button type="button" x-on:click="removeRecipient(email)" aria-label="Remover destinatário" class="rounded-full p-0.5 transition hover:bg-indigo-100 dark:hover:bg-indigo-500/25">
                                        <x-icon name="x-mark" class="h-3 w-3" />
                                    </button>
                                </span>
                            </template>
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Todos recebem o mesmo e-mail. Com a lista vazia, cada criador recebe apenas os próprios follow-ups.</p>
                        @error('summary_recipients')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="schedule-summary-cc" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Cópias (CC)</label>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <input
                                id="schedule-summary-cc"
                                type="email"
                                x-model="newCc"
                                x-on:keydown.enter.prevent="addCc()"
                                placeholder="copia@exemplo.com"
                                class="h-10 min-w-0 flex-1 rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                            <x-ui.action type="button" variant="secondary" icon="plus" x-on:click="addCc()">Adicionar</x-ui.action>
                        </div>

                        <input type="hidden" name="summary_cc_recipients" x-bind:value="ccList.join(', ')">

                        <div class="mt-2 flex flex-wrap gap-1.5" x-show="ccList.length" x-cloak>
                            <template x-for="email in ccList" :key="email">
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                    <span x-text="email"></span>
                                    <button type="button" x-on:click="removeCc(email)" aria-label="Remover cópia" class="rounded-full p-0.5 transition hover:bg-gray-200 dark:hover:bg-white/20">
                                        <x-icon name="x-mark" class="h-3 w-3" />
                                    </button>
                                </span>
                            </template>
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recebem uma cópia de cada resumo agendado.</p>
                        @error('summary_cc_recipients')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-700 dark:text-gray-200">Dias da semana</legend>

                            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @foreach ($schedule['dayOptions'] as $value => $label)
                                    <label class="flex min-h-10 cursor-pointer items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:has-[:checked]:border-indigo-400 dark:has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:text-indigo-200">
                                        <input
                                            type="checkbox"
                                            name="summary_days[]"
                                            value="{{ $value }}"
                                            @checked(in_array((int) $value, $selectedDays, true))
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-indigo-400"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">O resumo é enviado apenas nos dias selecionados.</p>
                            @error('summary_days')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            @error('summary_days.*')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>

                    <div>
                        <label for="schedule-summary-times" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Horários</label>
                        <input
                            id="schedule-summary-times"
                            name="summary_times"
                            type="text"
                            value="{{ old('summary_times', $schedule['times']) }}"
                            placeholder="08:00, 17:00"
                            required
                            class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Separe múltiplos horários por vírgula, no formato HH:MM.</p>
                        @error('summary_times')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <p class="rounded-2xl border border-dashed border-gray-300 bg-gray-50/60 px-4 py-3 text-xs text-gray-500 dark:border-white/15 dark:bg-white/[0.03] dark:text-gray-400">
                        Requer o scheduler ativo no servidor.
                    </p>
                </form>
            </div>

            <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-6 py-4 dark:border-white/10">
                <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                <x-ui.action type="submit" icon="check" form="follow-up-summary-schedule-form">Salvar agendamento</x-ui.action>
            </footer>
        </div>
    </div>
</div>
