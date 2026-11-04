@props(['followUp'])

@php
    $recipientDefaults = collect([$followUp->creator?->email, $followUp->assignee?->email])
        ->filter()
        ->unique()
        ->implode(', ');

    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';

    $openOnLoad = request()->boolean('email') || old('modal') === 'email';
@endphp

<x-ui.modal
    id="follow-up-email-modal"
    title="Enviar follow-up por e-mail"
    description="O envio é processado em segundo plano e inclui o resumo, o histórico e o anexo Excel."
    :data-open="$openOnLoad"
>
    <form method="POST" action="{{ route('admin.follow-ups.email.send', $followUp) }}" class="space-y-4">
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

        <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
            <x-ui.action type="submit" icon="paper-airplane">Enviar e-mail</x-ui.action>
            <x-ui.action type="button" variant="secondary" data-modal-close>Cancelar</x-ui.action>
        </div>
    </form>
</x-ui.modal>

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
