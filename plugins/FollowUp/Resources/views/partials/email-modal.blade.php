@props(['followUp'])

@php
    $recipientDefaults = collect([$followUp->creator?->email, $followUp->assignee?->email])
        ->filter()
        ->unique()
        ->implode(', ');

    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
@endphp

<div
    id="follow-up-email-modal"
    class="fixed inset-0 z-[100] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="follow-up-email-title"
    @if (request()->boolean('email') || old('modal') === 'email') data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute inset-x-0 bottom-0 top-4 flex flex-col overflow-hidden rounded-t-2xl bg-white shadow-xl dark:bg-[#1c1c1b] sm:inset-0 sm:m-auto sm:h-fit sm:max-h-[90vh] sm:w-full sm:max-w-lg sm:rounded-2xl">
        <div class="shrink-0 px-5 pt-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="follow-up-email-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Enviar follow-up por e-mail</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">O e-mail inclui o resumo, o histórico e o anexo Excel. Você verá um aviso com o resultado do envio.</p>
                </div>

                <button
                    type="button"
                    data-modal-close
                    class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                    aria-label="Fechar"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4">
            <form id="follow-up-email-form" method="POST" action="{{ route('admin.follow-ups.email.send', $followUp) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="modal" value="email">

                <div>
                    <label for="email-recipients" class="{{ $labelClass }}">Destinatários <span class="text-red-500">*</span></label>
                    <input
                        id="email-recipients"
                        name="recipients"
                        type="text"
                        value="{{ old('recipients', $recipientDefaults) }}"
                        required
                        placeholder="email@exemplo.com, outro@exemplo.com"
                        class="{{ $inputClass }}"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Separe múltiplos e-mails por vírgula, ponto e vírgula ou espaço.</p>
                    @error('recipients')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email-subject" class="{{ $labelClass }}">Assunto</label>
                    <input
                        id="email-subject"
                        name="subject"
                        type="text"
                        value="{{ old('subject', 'Follow-up: '.$followUp->title) }}"
                        maxlength="150"
                        class="{{ $inputClass }}"
                    >
                    @error('subject')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </form>
        </div>

        <div class="shrink-0 border-t border-gray-100 px-5 py-4 dark:border-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.action type="submit" icon="paper-airplane" form="follow-up-email-form">Enviar e-mail</x-ui.action>
                <x-ui.action type="button" variant="secondary" data-modal-close>Cancelar</x-ui.action>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const modal = document.getElementById('follow-up-email-modal');

            if (!modal) {
                return;
            }

            const open = () => {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                modal.querySelector('input[name="recipients"]')?.focus();
            };

            const close = () => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('[data-follow-up-email-open]').forEach((trigger) => {
                trigger.addEventListener('click', open);
            });

            modal.addEventListener('click', (event) => {
                if (event.target.closest('[data-modal-close]')) {
                    close();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    close();
                }
            });

            if (modal.hasAttribute('data-open')) {
                open();
            }
        })();
    </script>
@endpush
