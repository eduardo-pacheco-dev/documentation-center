@props(['followUps', 'schedule'])

@php
    $recipientDefaults = fn ($followUp): string => collect([$followUp->creator?->email, $followUp->assignee?->email])
        ->filter()
        ->unique()
        ->implode(', ');

    $recipientChips = old('summary_recipients')
        ? \Plugins\FollowUp\Models\FollowUpSetting::parseSummaryRecipients((string) old('summary_recipients'))
        : $schedule['recipientsList'];

    $ccChips = old('summary_cc_recipients')
        ? \Plugins\FollowUp\Models\FollowUpSetting::parseSummaryRecipients((string) old('summary_cc_recipients'))
        : $schedule['ccRecipients'];

    $selectedDays = collect((array) old('summary_days', $schedule['days']))
        ->map(fn ($day): int => (int) $day)
        ->all();
@endphp

<div
    x-data="{
        open: {{ $errors->hasAny(['recipients', 'subject', 'summary_times', 'summary_days', 'email_summary_enabled', 'summary_recipients', 'summary_cc_recipients']) ? 'true' : 'false' }},
        tab: {{ $errors->hasAny(['summary_times', 'summary_days', 'email_summary_enabled', 'summary_recipients', 'summary_cc_recipients']) ? "'scheduled'" : "'manual'" }},
        recipientsList: [],
        ccList: [],
        newRecipient: '',
        newCc: '',
        initScheduleLists() {
            this.recipientsList = JSON.parse(this.$root.dataset.summaryRecipients || '[]');
            this.ccList = JSON.parse(this.$root.dataset.summaryCcRecipients || '[]');
        },
        addRecipient() {
            const email = this.newRecipient.trim();

            if (! email.includes('@') || this.recipientsList.includes(email)) {
                return;
            }

            this.recipientsList.push(email);
            this.newRecipient = '';
        },
        addCc() {
            const email = this.newCc.trim();

            if (! email.includes('@') || this.ccList.includes(email)) {
                return;
            }

            this.ccList.push(email);
            this.newCc = '';
        },
        removeRecipient(email) {
            this.recipientsList = this.recipientsList.filter((item) => item !== email);
        },
        removeCc(email) {
            this.ccList = this.ccList.filter((item) => item !== email);
        },
        selected: '',
        recipients: '',
        subject: '',
        formAction() {
            if (this.selected === 'all') {
                return this.$root.dataset.actionTable;
            }

            return this.$root.dataset.actionTemplate.replace('__ID__', this.selected);
        },
        openComposer() {
            this.open = true;

            this.$nextTick(() => this.$refs.followUp?.focus());
        },
        chooseFollowUp(event) {
            const option = event.target.selectedOptions[0];

            this.selected = option.value;
            this.recipients = option.dataset.recipients || this.$root.dataset.userEmail;
            this.subject = option.dataset.subject || '';
        },
        close() {
            this.open = false;
        },
    }"
    data-user-email="{{ auth()->user()?->email }}"
    data-action-template="{{ route('admin.follow-ups.email.send', ['followUp' => '__ID__']) }}"
    data-action-table="{{ route('admin.follow-ups.email.table') }}"
    data-summary-recipients="{{ json_encode($recipientChips, JSON_HEX_APOS | JSON_HEX_QUOT) }}"
    data-summary-cc-recipients="{{ json_encode($ccChips, JSON_HEX_APOS | JSON_HEX_QUOT) }}"
    x-init="initScheduleLists()"
    x-on:open-email-composer.window="openComposer()"
    x-on:keydown.escape.window="open && close()"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.100ms
        class="fixed inset-0 z-[100]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="follow-up-composer-title"
    >
        <div class="absolute inset-0 bg-gray-900/50" x-on:click="close()"></div>

        <div class="absolute inset-x-0 bottom-0 top-4 flex flex-col overflow-hidden rounded-t-2xl bg-white shadow-xl dark:bg-[#1c1c1b] sm:inset-0 sm:m-auto sm:h-fit sm:max-h-[90vh] sm:w-full sm:max-w-lg sm:rounded-2xl">
            <div class="shrink-0 px-5 pt-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="follow-up-composer-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Enviar follow-up por e-mail</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">O e-mail inclui o resumo, o histórico e o anexo Excel. Você verá um aviso com o resultado do envio.</p>
                </div>

                <button
                    type="button"
                    x-on:click="close()"
                    class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                    aria-label="Fechar"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-4 inline-flex rounded-xl border border-gray-200 bg-white p-1 dark:border-white/10 dark:bg-white/[0.03]" role="tablist" aria-label="Modo de envio">
                <button
                    type="button"
                    role="tab"
                    x-bind:aria-selected="tab === 'manual' ? 'true' : 'false'"
                    x-on:click="tab = 'manual'"
                    x-bind:class="tab === 'manual' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition"
                >
                    <x-icon name="paper-airplane" class="h-4 w-4" />
                    Envio manual
                </button>

                @if (auth()->user()?->is_admin)
                    <button
                        type="button"
                        role="tab"
                        x-bind:aria-selected="tab === 'scheduled' ? 'true' : 'false'"
                        x-on:click="tab = 'scheduled'"
                        x-bind:class="tab === 'scheduled' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10'"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition"
                    >
                        <x-icon name="clock" class="h-4 w-4" />
                        Agendado
                    </button>
                @endif
            </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-5 pb-5">
            <div x-show="tab === 'manual'">
            <form
                id="follow-up-email-manual-form"
                method="POST"
                x-bind:action="formAction()"
                class="mt-4 space-y-4"
            >
                @csrf

                <div>
                    <label for="composer-follow-up" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Follow-up <span class="text-red-500">*</span></label>
                    <select
                        id="composer-follow-up"
                        x-ref="followUp"
                        x-on:change="chooseFollowUp($event)"
                        required
                        class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="">Selecione um follow-up</option>

                        @if ($followUps->isNotEmpty())
                            <option
                                value="all"
                                data-recipients="{{ auth()->user()?->email }}"
                                data-subject="Follow-ups — {{ now()->format('d/m/Y') }}"
                            >
                                Todos os follow-ups da listagem ({{ $followUps->count() }})
                            </option>
                        @endif

                        @foreach ($followUps as $followUp)
                            <option
                                value="{{ $followUp->getKey() }}"
                                data-recipients="{{ $recipientDefaults($followUp) }}"
                                data-subject="Follow-up: {{ $followUp->title }}"
                            >
                                {{ $followUp->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="composer-recipients" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Destinatários <span class="text-red-500">*</span></label>
                    <input
                        id="composer-recipients"
                        name="recipients"
                        type="text"
                        x-model="recipients"
                        required
                        placeholder="email@exemplo.com, outro@exemplo.com"
                        class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Separe múltiplos e-mails por vírgula, ponto e vírgula ou espaço.</p>
                    @error('recipients')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="composer-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Assunto</label>
                    <input
                        id="composer-subject"
                        name="subject"
                        type="text"
                        x-model="subject"
                        maxlength="150"
                        class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    @error('subject')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

            </form>
            </div>

            @if (auth()->user()?->is_admin)
                <div x-show="tab === 'scheduled'" x-cloak>
                    <form id="follow-up-email-schedule-form" method="POST" action="{{ route('admin.follow-ups.settings.schedule') }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            O resumo periódico envia os follow-ups em aberto nos horários configurados.
                        </p>

                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200/80 px-4 py-3 dark:border-white/10">
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

                        <div>
                            <label for="composer-subject-prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Prefixo do assunto</label>
                            <input
                                id="composer-subject-prefix"
                                name="email_subject_prefix"
                                type="text"
                                value="{{ old('email_subject_prefix', $schedule['prefix']) }}"
                                maxlength="150"
                                required
                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Usado no assunto dos e-mails, ex.: "{{ $schedule['prefix'] }} — Resumo {{ now()->format('d/m/Y') }}".</p>
                            @error('email_subject_prefix')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="composer-summary-period" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Período do resumo</label>
                            <select
                                id="composer-summary-period"
                                name="summary_period"
                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                                @foreach ($schedule['periodOptions'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('summary_period', $schedule['period']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">“Últimos 30 dias” considera follow-ups em aberto com prazo entre 30 dias atrás e hoje.</p>
                            @error('summary_period')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="composer-summary-recipients" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Destinatários (Para)</label>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <input
                                    id="composer-summary-recipients"
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

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Todos recebem o mesmo e-mail. Se a lista ficar vazia, cada criador recebe apenas os próprios follow-ups.</p>
                            @error('summary_recipients')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="composer-summary-cc" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Cópias (CC)</label>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <input
                                    id="composer-summary-cc"
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

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recebem uma cópia de cada resumo agendado. E-mails cadastrados em Configurações também entram em cópia.</p>
                            @error('summary_cc_recipients')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <fieldset>
                                <legend class="block text-sm font-medium text-gray-700 dark:text-gray-200">Dias da semana</legend>

                                <div class="mt-1.5 grid grid-cols-2 gap-2 sm:grid-cols-4">
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
                            <label for="composer-summary-times" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Horários</label>
                            <input
                                id="composer-summary-times"
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

                        <p class="rounded-xl border border-dashed border-gray-300 bg-gray-50/60 px-3 py-2 text-xs text-gray-500 dark:border-white/15 dark:bg-white/[0.03] dark:text-gray-400">
                            Requer o scheduler ativo no servidor.
                        </p>

                    </form>
                </div>
            @endif
            </div>

            <div class="shrink-0 border-t border-gray-100 px-5 py-4 dark:border-white/10">
                <div class="flex flex-wrap items-center gap-2" x-show="tab === 'manual'">
                    <x-ui.action type="submit" icon="paper-airplane" form="follow-up-email-manual-form">Enviar e-mail</x-ui.action>
                    <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                </div>

                @if (auth()->user()?->is_admin)
                    <div class="flex flex-wrap items-center gap-2" x-show="tab === 'scheduled'" x-cloak>
                        <x-ui.action type="submit" icon="check" form="follow-up-email-schedule-form">Salvar agendamento</x-ui.action>
                        <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
