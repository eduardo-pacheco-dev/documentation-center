@php
    $editModalOpen = $errors->any() && old('modal') === 'edit';
@endphp

<div
    id="erb-edit-modal"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="erb-edit-modal-title"
    data-modal-hash="edit"
    @if ($editModalOpen) data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-3xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="erb-edit-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Editar ERB
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $erb->code }} — {{ $erb->name }}</p>
            </div>

            <button
                type="button"
                class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                data-modal-close
            >
                <span class="sr-only">Fechar</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        @if ($editModalOpen)
            <div
                data-modal-error
                class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                <p class="font-medium">Não foi possível salvar a ERB. Corrija os campos destacados e tente novamente.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.erbs.update', $erb) }}" class="mt-5" data-wizard novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="modal" value="edit">

            <ol data-wizard-steps class="flex flex-wrap items-center gap-2" role="list"></ol>

            <div class="mt-5">
                @include('admin.erbs.partials.form', ['erb' => $erb, 'wizard' => true])
            </div>

            <div class="mt-6 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    data-modal-close
                >
                    Cancelar
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-wizard-prev
                        class="hidden rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        Voltar
                    </button>

                    <button
                        type="button"
                        data-wizard-next
                        class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        Próximo
                    </button>

                    <button
                        type="submit"
                        data-wizard-submit
                        class="hidden items-center gap-1.5 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar alterações
                    </button>
                </div>
            </div>
        </form>

        <form
            method="POST"
            action="{{ route('admin.erbs.destroy', $erb) }}"
            class="mt-6 border-t border-gray-100 pt-4 dark:border-gray-800"
            data-confirm="Excluir a ERB {{ $erb->code }}?"
            data-confirm-title="Excluir ERB"
            data-confirm-label="Excluir"
        >
            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir ERB
            </button>
        </form>
    </div>
</div>

<script>
    (function () {
        const wizard = document.querySelector('[data-wizard]');

        if (! wizard) {
            return;
        }

        const panels = Array.from(wizard.querySelectorAll('[data-wizard-panel]'));
        const stepsList = wizard.querySelector('[data-wizard-steps]');
        const prevButton = wizard.querySelector('[data-wizard-prev]');
        const nextButton = wizard.querySelector('[data-wizard-next]');
        const submitButton = wizard.querySelector('[data-wizard-submit]');
        const scroller = wizard.closest('.overflow-y-auto');
        const baseIndicator = 'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition';
        const inactiveIndicator = baseIndicator + ' border-gray-200 bg-white text-gray-600 hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-indigo-500/40';
        const activeIndicator = baseIndicator + ' border-indigo-600 bg-indigo-600 text-white shadow-sm';
        const doneIndicator = baseIndicator + ' border-emerald-500 text-emerald-600 dark:text-emerald-400';
        let current = 0;

        if (stepsList) {
            stepsList.innerHTML = panels.map((panel, index) => `
                <li>
                    <button type="button" data-wizard-indicator="${index}" class="${inactiveIndicator}">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 text-[0.65rem] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">${index + 1}</span>
                        <span>${panel.dataset.wizardTitle ?? ''}</span>
                    </button>
                </li>
            `).join('');
        }

        const indicators = stepsList ? Array.from(stepsList.querySelectorAll('[data-wizard-indicator]')) : [];

        const firstInvalid = (panel) => Array.from(panel.querySelectorAll('input, select, textarea'))
            .find((field) => ! field.checkValidity());

        const render = () => {
            panels.forEach((panel, index) => panel.classList.toggle('hidden', index !== current));

            indicators.forEach((indicator, index) => {
                indicator.className = index === current
                    ? activeIndicator
                    : (index < current ? doneIndicator : inactiveIndicator);
            });

            prevButton.classList.toggle('hidden', current === 0);
            nextButton.classList.toggle('hidden', current === panels.length - 1);
            submitButton.classList.toggle('hidden', current !== panels.length - 1);
            submitButton.classList.toggle('inline-flex', current === panels.length - 1);
        };

        const goTo = (index) => {
            current = Math.max(0, Math.min(index, panels.length - 1));
            render();

            if (scroller) {
                scroller.scrollTop = 0;
            }
        };

        nextButton.addEventListener('click', () => {
            const invalid = firstInvalid(panels[current]);

            if (invalid) {
                invalid.reportValidity();

                return;
            }

            goTo(current + 1);
        });

        prevButton.addEventListener('click', () => goTo(current - 1));

        indicators.forEach((indicator) => {
            indicator.addEventListener('click', () => goTo(Number(indicator.dataset.wizardIndicator)));
        });

        wizard.addEventListener('submit', (event) => {
            for (let index = 0; index < panels.length; index++) {
                const invalid = firstInvalid(panels[index]);

                if (invalid) {
                    event.preventDefault();
                    goTo(index);
                    invalid.reportValidity();

                    return;
                }
            }
        });

        document.querySelectorAll('[data-modal-open="erb-edit-modal"]').forEach((opener) => {
            opener.addEventListener('click', () => goTo(0));
        });

        const errorPanel = panels.findIndex((panel) => panel.querySelector('.text-red-600'));

        goTo(errorPanel > -1 ? errorPanel : 0);
    })();
</script>
