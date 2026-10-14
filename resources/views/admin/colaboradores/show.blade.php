<x-layouts.admin :title="$colaborador->name">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 p-6 text-white shadow-xl shadow-indigo-600/10 sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-violet-400/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <span class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-semibold text-white backdrop-blur">
                    {{ strtoupper(substr($colaborador->name, 0, 1)) }}
                </span>

                <div class="min-w-0">
                    <a
                        href="{{ route('admin.colaboradores.index') }}"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-100/90 transition hover:text-white"
                    >
                        <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                        Colaboradores
                    </a>

                    <h1 class="mt-1 truncate text-2xl font-semibold tracking-tight sm:text-3xl">{{ $colaborador->name }}</h1>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-colaborador-status :colaborador="$colaborador" />
                        @if ($colaborador->role)
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur">
                                <x-icon name="briefcase" class="h-3.5 w-3.5" />
                                {{ $colaborador->role }}
                            </span>
                        @endif
                        @if ($colaborador->regional || $colaborador->uf)
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur">
                                <x-icon name="map-pin" class="h-3.5 w-3.5" />
                                {{ $colaborador->regional ? $colaborador->regional.' · ' : '' }}{{ $colaborador->uf?->value ?? '' }}
                            </span>
                        @endif
                        @if ($colaborador->contract_regime)
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur">
                                {{ $colaborador->contract_regime->label() }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    data-modal-open="colaborador-edit-modal"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50"
                >
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    Editar
                </button>

                <a
                    href="{{ route('admin.colaboradores.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                >
                    Voltar
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="user" class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Dados pessoais</h2>
                </div>

                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Nome</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Função</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->role ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Data de nascimento</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->birth_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Nome da mãe</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->mother_name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="text-right"><x-colaborador-status :colaborador="$colaborador" /></dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                        <x-icon name="document-text" class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Documentos</h2>
                </div>

                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">CPF</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->document ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">CNPJ</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->cnpj ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">PIS</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->pis ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">RG</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->rg ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Órgão emissor</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->rg_issuer ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                        <x-icon name="briefcase" class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Contrato e lotação</h2>
                </div>

                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Regime de contrato</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->contract_regime?->label() ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Regional</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->regional ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">UF</dt>
                        <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->uf?->value ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <x-icon name="chat-bubble-oval-left-ellipsis" class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observações</h2>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                    {{ $colaborador->notes ?? 'Nenhuma observação registrada.' }}
                </p>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-icon name="phone" class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Contato</h2>
                </div>

                <div class="mt-4 space-y-2">
                    @if ($colaborador->phone)
                        <a
                            href="tel:{{ $colaborador->phone }}"
                            class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:border-indigo-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-indigo-500/40 dark:hover:bg-gray-800/50"
                        >
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="phone" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs text-gray-400 dark:text-gray-500">Telefone</span>
                                <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->phone }}</span>
                            </span>
                        </a>
                    @endif

                    @if ($colaborador->email)
                        <a
                            href="mailto:{{ $colaborador->email }}"
                            class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:border-indigo-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-indigo-500/40 dark:hover:bg-gray-800/50"
                        >
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="envelope" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs text-gray-400 dark:text-gray-500">E-mail</span>
                                <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->email }}</span>
                            </span>
                        </a>
                    @endif

                    @if (! $colaborador->phone && ! $colaborador->email)
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum contato registrado.</p>
                    @endif
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="clock" class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Histórico</h2>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Criado em</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $colaborador->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Atualizado em</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $colaborador->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>

    @include('admin.colaboradores.partials.edit-modal', ['colaborador' => $colaborador])

    <script>
        const openModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeModal = (modal) => {
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        };

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-modal-open]');

            if (opener) {
                event.preventDefault();
                const modal = document.getElementById(opener.dataset.modalOpen);
                if (modal) {
                    openModal(modal);
                }

                return;
            }

            const closer = event.target.closest('[data-modal-close]');
            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
            }
        });

        const editModal = document.getElementById('colaborador-edit-modal');

        if (editModal?.hasAttribute('data-open')) {
            openModal(editModal);
        }

        const wizard = editModal?.querySelector('[data-wizard]');

        if (wizard) {
            const steps = Array.from(wizard.querySelectorAll('[data-wizard-step]'));
            const label = editModal.querySelector('[data-wizard-label]');
            const counter = editModal.querySelector('[data-wizard-counter]');
            const progressBar = editModal.querySelector('[data-wizard-progress]');
            const prevButton = editModal.querySelector('[data-wizard-prev]');
            const nextButton = editModal.querySelector('[data-wizard-next]');
            const submitButton = editModal.querySelector('[data-wizard-submit]');

            const stepLabels = ['Dados pessoais', 'Documentos', 'Contrato e contato'];
            const progressWidths = ['w-1/3', 'w-2/3', 'w-full'];

            let current = 0;

            const render = () => {
                steps.forEach((step, index) => {
                    step.classList.toggle('hidden', index !== current);
                });

                if (label) {
                    label.textContent = stepLabels[current] || '';
                }

                if (counter) {
                    counter.textContent = 'Passo ' + (current + 1) + ' de ' + steps.length;
                }

                if (progressBar) {
                    progressBar.classList.remove('w-1/3', 'w-2/3', 'w-full');
                    progressBar.classList.add(progressWidths[current] || 'w-full');
                }

                prevButton?.classList.toggle('hidden', current === 0);
                nextButton?.classList.toggle('hidden', current === steps.length - 1);
                submitButton?.classList.toggle('hidden', current !== steps.length - 1);
                submitButton?.classList.toggle('inline-flex', current === steps.length - 1);
            };

            const goTo = (index) => {
                current = Math.max(0, Math.min(index, steps.length - 1));
                render();
            };

            const firstErrorStep = () => {
                const index = steps.findIndex((step) => step.querySelector('.text-red-600'));

                return index === -1 ? 0 : index;
            };

            nextButton?.addEventListener('click', () => {
                const step = steps[current];
                const invalid = Array.from(step.querySelectorAll('input, select, textarea')).find((field) => !field.checkValidity());

                if (invalid) {
                    invalid.reportValidity();

                    return;
                }

                goTo(current + 1);
            });

            prevButton?.addEventListener('click', () => goTo(current - 1));

            document.querySelectorAll('[data-modal-open="colaborador-edit-modal"]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            goTo(firstErrorStep());
        }
    </script>
</x-layouts.admin>
