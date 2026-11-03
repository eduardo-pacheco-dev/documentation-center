@props([
    'hasErrors' => false,
    'initialStep' => 0,
])

@php
    $groups = config('stations.fields', []);
    $titles = array_column($groups, 'title');

    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';

    $errorSummary = [];

    foreach ($groups as $index => $group) {
        foreach ($group['fields'] as $field) {
            foreach ($errors->get($field['name']) as $message) {
                $errorSummary[] = [
                    'step' => $index,
                    'field' => $field['name'],
                    'message' => $message,
                ];
            }
        }
    }
@endphp

<style>
    .station-modal::backdrop {
        background-color: rgb(17 24 39 / 0.65);
        backdrop-filter: blur(2px);
    }

    .station-modal[open] {
        animation: station-modal-in 160ms ease-out;
    }

    @keyframes station-modal-in {
        from {
            opacity: 0;
            transform: translateY(10px) scale(0.985);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .station-modal[open] {
            animation: none;
        }
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        if (window.__stationCreateModalRegistered) {
            return;
        }

        window.__stationCreateModalRegistered = true;

        Alpine.data('stationCreateModal', ({ open, initialStep, titles }) => ({
            open,
            step: initialStep,
            total: titles.length,
            titles,

            init() {
                if (! this.open) {
                    return;
                }

                this.$nextTick(() => {
                    this.$refs.dialog.showModal();
                    document.body.classList.add('overflow-hidden');

                    this.$nextTick(() => {
                        const target = this.$refs.dialog.querySelector('[aria-invalid="true"]')
                            ?? this.$refs.dialog.querySelector('[data-autofocus]');

                        target?.focus();
                    });
                });
            },

            show() {
                this.step = 0;

                if (this.$refs.dialog.open) {
                    return;
                }

                this.$refs.dialog.showModal();
                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => {
                    this.$refs.dialog.querySelector('[data-autofocus]')?.focus();
                });
            },

            close() {
                this.$refs.dialog.close();
            },

            onClose() {
                this.open = false;
                document.body.classList.remove('overflow-hidden');
            },

            onBackdropClick(event) {
                if (event.target === this.$refs.dialog) {
                    this.close();
                }
            },

            go(step, field = null) {
                this.step = Math.min(Math.max(step, 0), this.total - 1);
                this.focusStep(field);
            },

            next() {
                if (! this.validateStep()) {
                    return;
                }

                this.step = Math.min(this.step + 1, this.total - 1);
                this.focusStep();
            },

            back() {
                this.step = Math.max(this.step - 1, 0);
                this.focusStep();
            },

            onEnter(event) {
                if (event.defaultPrevented || event.target.closest('button, textarea')) {
                    return;
                }

                event.preventDefault();

                if (this.step < this.total - 1) {
                    this.next();

                    return;
                }

                this.$refs.dialog.querySelector('form')?.requestSubmit();
            },

            focusStep(field = null) {
                this.$nextTick(() => {
                    const dialog = this.$refs.dialog;

                    if (field) {
                        dialog.querySelector(`#modal-${field}`)?.focus();

                        return;
                    }

                    dialog
                        .querySelector(`[data-step="${this.step}"] input, [data-step="${this.step}"] select`)
                        ?.focus();
                });
            },

            validateStep() {
                const root = this.$refs.dialog.querySelector(`[data-step="${this.step}"]`);
                const invalid = root
                    ? Array.from(root.querySelectorAll('[required]')).find((field) => ! field.value.trim())
                    : null;

                if (! invalid) {
                    return true;
                }

                invalid.setCustomValidity('Preencha este campo para continuar.');
                invalid.reportValidity();
                invalid.setCustomValidity('');
                invalid.focus();

                return false;
            },
        }));
    });
</script>

<div
    x-data="stationCreateModal({
        open: @js((bool) $hasErrors),
        initialStep: {{ (int) $initialStep }},
        titles: @js($titles),
    })"
    x-on:open-station-modal.window="show()"
>
    <dialog
        x-ref="dialog"
        x-on:close="onClose()"
        x-on:click="onBackdropClick($event)"
        class="station-modal m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-3xl overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
        aria-labelledby="create-station-modal-title"
    >
        <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
            <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="signal" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="create-station-modal-title" class="truncate text-base font-semibold sm:text-lg">Nova estação</h2>
                        <p class="mt-0.5 hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                            Preencha as {{ count($groups) }} etapas para cadastrar o elemento de rede.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    x-on:click="close()"
                    class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-white/10"
                    aria-label="Fechar"
                >
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </header>

            <div class="h-1 shrink-0 bg-gray-100 dark:bg-white/10" aria-hidden="true">
                <div
                    class="h-full rounded-r-full bg-indigo-600 transition-all duration-300 dark:bg-indigo-500"
                    x-bind:style="`width: ${((step + 1) / total) * 100}%`"
                ></div>
            </div>

            <form method="POST" action="{{ route('admin.stations.store') }}" novalidate class="flex min-h-0 flex-1 flex-col" x-on:keydown.enter="onEnter($event)">
                @csrf
                <input type="hidden" name="modal" value="create">

                <ol class="flex shrink-0 items-stretch gap-1 border-b border-gray-100 px-3 py-2 sm:gap-1.5 sm:px-5 dark:border-white/10">
                    @foreach ($groups as $index => $group)
                        <li class="min-w-0 flex-1">
                            <button
                                type="button"
                                x-on:click="go({{ $index }})"
                                x-bind:aria-current="step === {{ $index }} ? 'step' : null"
                                class="flex w-full items-center justify-center gap-2 rounded-lg px-1.5 py-1.5 text-left transition sm:justify-start"
                                x-bind:class="step === {{ $index }} ? 'bg-indigo-50 dark:bg-indigo-500/10' : 'hover:bg-gray-50 dark:hover:bg-white/5'"
                            >
                                <span
                                    class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold"
                                    x-bind:class="step > {{ $index }}
                                        ? 'bg-emerald-500 text-white'
                                        : (step === {{ $index }} ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400')"
                                >
                                    <template x-if="step > {{ $index }}">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                    </template>

                                    <span x-show="step <= {{ $index }}">{{ $index + 1 }}</span>
                                </span>

                                <span
                                    class="hidden min-w-0 truncate text-xs font-medium sm:inline"
                                    x-bind:class="step === {{ $index }} ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-500 dark:text-gray-400'"
                                >
                                    {{ $group['title'] }}
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ol>

                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                    @if ($errorSummary !== [])
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/60 dark:bg-red-950/40">
                            <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                                <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                Não foi possível cadastrar a estação
                            </p>

                            <ul class="mt-1.5 space-y-1 pl-6 text-sm text-red-700 dark:text-red-300">
                                @foreach ($errorSummary as $error)
                                    <li class="list-disc">
                                        <button
                                            type="button"
                                            x-on:click="go({{ $error['step'] }}, '{{ $error['field'] }}')"
                                            class="text-left underline decoration-red-300 underline-offset-2 transition hover:decoration-red-500"
                                        >
                                            {{ $error['message'] }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @foreach ($groups as $index => $group)
                        <fieldset
                            x-cloak
                            x-show="step === {{ $index }}"
                            data-step="{{ $index }}"
                            class="min-w-0"
                        >
                            <legend class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ $group['title'] }}</legend>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $group['description'] }}</p>

                            @if (($group['component'] ?? null) === 'address')
                                <x-stations::partials.address-fields
                                    :group="$group"
                                    prefix="modal"
                                    :input-class="$inputClass"
                                    :label-class="$labelClass"
                                />
                            @else
                                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                    @foreach ($group['fields'] as $field)
                                        <div @class(['sm:col-span-2' => ($field['span'] ?? 1) === 2])>
                                            <label for="modal-{{ $field['name'] }}" class="{{ $labelClass }}">{{ $field['label'] }}</label>
                                            <input
                                                id="modal-{{ $field['name'] }}"
                                                name="{{ $field['name'] }}"
                                                type="text"
                                                value="{{ old($field['name']) }}"
                                                @if ($field['required'] ?? false) required @endif
                                                @if (isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                                                @if (isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                                @if (isset($field['inputmode'])) inputmode="{{ $field['inputmode'] }}" @endif
                                                @if ($field['name'] === 'site_id') data-autofocus @endif
                                                @error($field['name']) aria-invalid="true" @enderror
                                                class="{{ $inputClass }}"
                                            >
                                            @error($field['name'])
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </fieldset>
                    @endforeach
                </div>

                <footer class="shrink-0 border-t border-gray-100 px-3 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                        <p class="order-2 text-xs text-gray-500 sm:order-1 dark:text-gray-400">
                            Etapa <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="step + 1"></span>
                            de {{ count($groups) }} ·
                            <span class="font-medium text-gray-700 dark:text-gray-200" x-text="titles[step]"></span>
                        </p>

                        <div class="order-1 flex items-center gap-2 sm:order-2">
                            <x-ui.action
                                type="button"
                                variant="secondary"
                                icon="chevron-left"
                                class="flex-1 justify-center sm:flex-none"
                                x-show="step > 0"
                                x-on:click="back()"
                            >
                                Voltar
                            </x-ui.action>

                            <x-ui.action
                                type="button"
                                icon="chevron-right"
                                class="flex-1 justify-center sm:flex-none"
                                x-show="step < total - 1"
                                x-on:click="next()"
                            >
                                Próximo
                            </x-ui.action>

                            <x-ui.action
                                type="submit"
                                icon="check"
                                class="flex-1 justify-center sm:flex-none"
                                x-show="step === total - 1"
                            >
                                Cadastrar estação
                            </x-ui.action>
                        </div>
                    </div>
                </footer>
            </form>
        </div>
    </dialog>
</div>
