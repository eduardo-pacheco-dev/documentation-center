@props(['followUps'])

@php
    $recipientDefaults = fn ($followUp): string => collect([$followUp->creator?->email, $followUp->assignee?->email])
        ->filter()
        ->unique()
        ->implode(', ');
@endphp

<div
    x-data="{
        open: {{ $errors->hasAny(['recipients', 'subject']) ? 'true' : 'false' }},
        selected: '',
        recipients: @js(old('recipients', '')),
        subject: @js(old('subject', '')),
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

            if (! this.recipients) {
                this.recipients = option.dataset.recipients || this.$root.dataset.userEmail || '';
            }

            this.subject = option.dataset.subject || '';
        },
        close() {
            this.open = false;
        },
    }"
    data-user-email="{{ auth()->user()?->email }}"
    data-action-template="{{ route('admin.follow-ups.email.send', ['followUp' => '__ID__']) }}"
    data-action-table="{{ route('admin.follow-ups.email.table') }}"
    x-on:open-email-composer.window="openComposer()"
    x-on:keydown.escape.window="open && close()"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[100] flex items-end justify-center overflow-hidden sm:items-center sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="follow-up-composer-title"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" x-on:click="close()"></div>

        <div class="relative flex h-[calc(100dvh-1rem)] max-h-[calc(100dvh-1rem)] w-full max-w-lg flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl dark:bg-[#1c1c1b] sm:h-auto sm:max-h-[90vh] sm:rounded-3xl">
            <header class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-6 py-5 dark:border-white/10">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="paper-airplane" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="follow-up-composer-title" class="text-base font-semibold text-gray-900 dark:text-white">
                            Enviar follow-up por e-mail
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            O e-mail inclui o resumo, o histórico e o anexo Excel.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    x-on:click="close()"
                    class="rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"
                    aria-label="Fechar envio de e-mail"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                <form id="follow-up-email-manual-form" method="POST" x-bind:action="formAction()" class="space-y-5">
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

            <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-6 py-4 dark:border-white/10">
                <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                <x-ui.action type="submit" icon="paper-airplane" form="follow-up-email-manual-form">Enviar e-mail</x-ui.action>
            </footer>
        </div>
    </div>
</div>
