@props([
    'mode' => 'create',
    'site' => null,
    'roms' => [],
    'hasErrors' => false,
])

@php
    $isEdit = $mode === 'edit';
    $modalId = $isEdit ? 'edit-site-modal' : 'create-site-modal';
    $fieldPrefix = $isEdit ? 'edit-site-' : 'create-site-';
@endphp

@once
    <style>
        .nokia-site-modal::backdrop {
            background-color: rgb(17 24 39 / 0.65);
            backdrop-filter: blur(2px);
        }

        .nokia-site-modal[open] {
            animation: nokia-site-modal-in 160ms ease-out;
        }

        @keyframes nokia-site-modal-in {
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
            .nokia-site-modal[open] {
                animation: none;
            }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            if (window.__nokiaSiteFormModalRegistered) {
                return;
            }

            window.__nokiaSiteFormModalRegistered = true;

            Alpine.data('nokiaSiteFormModal', ({ mode, open }) => ({
                mode,
                open,

                init() {
                    if (! this.open) {
                        return;
                    }

                    setTimeout(() => {
                        this.$refs.dialog.showModal();
                        document.body.classList.add('overflow-hidden');

                        setTimeout(() => {
                            this.$refs.dialog.querySelector('[data-autofocus]')?.focus();
                        }, 0);
                    }, 0);
                },

                show() {
                    if (this.$refs.dialog.open) {
                        return;
                    }

                    this.$refs.dialog.showModal();
                    document.body.classList.add('overflow-hidden');

                    setTimeout(() => {
                        this.$refs.dialog.querySelector('[data-autofocus]')?.focus();
                    }, 0);
                },

                openForEdit(detail) {
                    const form = this.$refs.form;

                    this.mode = 'edit';
                    form.action = detail.action;

                    const editingId = form.elements.namedItem('editing_id');

                    if (editingId) {
                        editingId.value = detail.id;
                    }

                    Object.entries(detail.fields ?? {}).forEach(([name, value]) => {
                        form.querySelectorAll(`[name="${name}"]`).forEach((input) => {
                            if (input.type === 'checkbox') {
                                input.checked = Boolean(value);

                                return;
                            }

                            input.value = input.type === 'hidden'
                                ? (value ? '1' : '0')
                                : (value ?? '');
                        });
                    });

                    this.show();
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
            }));
        });
    </script>
@endonce

<div
    x-data="nokiaSiteFormModal({ mode: @js($mode), open: @js((bool) $hasErrors) })"
    @if ($isEdit)
        x-on:open-edit-site-modal.window="openForEdit($event.detail)"
    @else
        x-on:open-site-modal.window="show()"
    @endif
>
    <dialog
        id="{{ $modalId }}"
        x-ref="dialog"
        x-on:close="onClose()"
        x-on:click="onBackdropClick($event)"
        class="nokia-site-modal m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-2xl overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
        aria-labelledby="{{ $modalId }}-title"
    >
        <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
            <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon :name="$isEdit ? 'pencil-square' : 'signal'" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="{{ $modalId }}-title" class="truncate text-base font-semibold sm:text-lg">
                            {{ $isEdit ? 'Editar site' : 'Novo site' }}
                        </h2>
                        <p class="mt-0.5 hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                            {{ $isEdit
                                ? 'Atualize os dados do site e o andamento do checklist.'
                                : 'Preencha os dados do site e marque o checklist de atividades.' }}
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

            <form
                method="POST"
                action="{{ $isEdit && $site ? route('admin.nokia.sites.update', $site) : ($isEdit ? '#' : route('admin.nokia.sites.store')) }}"
                class="flex min-h-0 flex-1 flex-col"
                x-ref="form"
            >
                @csrf
                <input type="hidden" name="modal" value="{{ $mode }}">

                @if ($isEdit)
                    @method('PUT')
                    <input type="hidden" name="editing_id" value="{{ $site?->getKey() }}">
                @endif

                <div class="min-h-0 flex-1 space-y-6 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                    @if ($hasErrors)
                        <div class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/60 dark:bg-red-950/40">
                            <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                                <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                Não foi possível salvar o site
                            </p>
                        </div>
                    @endif

                    <x-nokia::partials.form-fields :prefix="$fieldPrefix" :site="$site" :roms="$roms" />
                </div>

                <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                    <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                    <x-ui.action type="submit" icon="check">
                        {{ $isEdit ? 'Salvar alterações' : 'Cadastrar site' }}
                    </x-ui.action>
                </footer>
            </form>
        </div>
    </dialog>
</div>
