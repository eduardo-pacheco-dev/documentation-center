@once
    <style>
        .nokia-details-modal::backdrop {
            background-color: rgb(17 24 39 / 0.65);
            backdrop-filter: blur(2px);
        }

        .nokia-details-modal[open] {
            animation: nokia-details-modal-in 160ms ease-out;
        }

        @keyframes nokia-details-modal-in {
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
            .nokia-details-modal[open] {
                animation: none;
            }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            if (window.__nokiaSiteDetailsModalRegistered) {
                return;
            }

            window.__nokiaSiteDetailsModalRegistered = true;

            Alpine.data('nokiaSiteDetailsModal', () => ({
                site: null,

                show(detail) {
                    this.site = detail;

                    if (this.$refs.dialog.open) {
                        return;
                    }

                    this.$refs.dialog.showModal();
                    document.body.classList.add('overflow-hidden');

                    setTimeout(() => {
                        this.$refs.dialog.querySelector('[data-autofocus]')?.focus();
                    }, 0);
                },

                close() {
                    this.$refs.dialog.close();
                },

                onClose() {
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

<div x-data="nokiaSiteDetailsModal()" x-on:open-site-details-modal.window="show($event.detail)">
    <dialog
        id="details-site-modal"
        x-ref="dialog"
        x-on:close="onClose()"
        x-on:click="onBackdropClick($event)"
        class="nokia-details-modal m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-2xl overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
        aria-labelledby="details-site-modal-title"
    >
        <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
            <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="eye" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="details-site-modal-title" class="truncate text-base font-semibold sm:text-lg">Detalhes do site</h2>
                        <p class="mt-0.5 truncate font-mono text-xs text-gray-500 dark:text-gray-400" x-text="site?.site_id ?? ''"></p>
                    </div>
                </div>

                <button
                    type="button"
                    x-on:click="close()"
                    data-autofocus
                    class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-white/10"
                    aria-label="Fechar"
                >
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">End ID</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white" x-text="site?.end_id ?? '—'"></dd>
                    </div>

                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">OC</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white" x-text="site?.oc ?? '—'"></dd>
                    </div>

                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">OS FAM Entrega</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white" x-text="site?.os_fam_entrega ?? '—'"></dd>
                    </div>

                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">OS FAM Instalação</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white" x-text="site?.os_fam_instalacao ?? '—'"></dd>
                    </div>
                </dl>

                <section class="mt-5">
                    <h3 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Checklist de atividades</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Andamento das atividades de entrega e instalação deste site.</p>

                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        <template x-for="item in (site?.checklists ?? [])" :key="item.field">
                            <li class="flex items-center gap-3 rounded-2xl border border-gray-200/80 px-4 py-3 dark:border-white/10">
                                <span
                                    class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full"
                                    x-bind:class="item.done
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                        : 'bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500'"
                                >
                                    <x-icon name="check" x-show="item.done" class="h-3.5 w-3.5" />
                                    <x-icon name="x-mark" x-show="! item.done" class="h-3.5 w-3.5" />
                                </span>

                                <span class="text-sm text-gray-700 dark:text-gray-200" x-text="item.label"></span>

                                <span
                                    class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    x-bind:class="item.done
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                        : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400'"
                                    x-text="item.done ? 'Concluído' : 'Pendente'"
                                ></span>
                            </li>
                        </template>
                    </ul>
                </section>

                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Atualizado em <span class="font-medium text-gray-700 dark:text-gray-200" x-text="site?.updated_at ?? '—'"></span>
                </p>
            </div>

            <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                <x-ui.action type="button" variant="secondary" x-on:click="close()">Fechar</x-ui.action>
                <x-ui.action
                    type="button"
                    icon="pencil-square"
                    x-on:click="close(); $dispatch('open-edit-site-modal', site?.edit)"
                >
                    Editar site
                </x-ui.action>
            </footer>
        </div>
    </dialog>
</div>
