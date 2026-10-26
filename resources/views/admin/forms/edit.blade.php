@php
    $steps = $steps ?? [];
    $structureCount = count($steps);
    $fieldCount = collect($steps)->sum(fn ($step) => count($step['fields'] ?? []));

    $answeredFields = collect($steps)->flatMap(fn ($step) => collect($step['fields'] ?? [])->filter(
        fn ($field) => filled($field['value'] ?? null) || ! empty($field['images'] ?? []),
    ));
    $hasResponses = $answeredFields->isNotEmpty();
    $answeredCount = $answeredFields->count();

    $structureError = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'steps'));
    $activeTab = $structureError ? 'structure' : 'details';
@endphp

<x-layouts.admin :title="$form->code">
    <div class="border-b border-gray-200 pb-6 dark:border-gray-800">
        <nav class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
            <a href="{{ route('admin.forms.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Formulários</a>
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            <span class="text-gray-700 dark:text-gray-200">{{ $form->code }}</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm sm:inline-flex dark:bg-indigo-500">
                    <x-icon name="clipboard-document-list" class="h-6 w-6" />
                </span>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $form->code }}</h1>
                        <x-form-status :status="$form->status" />
                    </div>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $form->name }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm">
                        <span class="inline-flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                            <x-icon name="document-text" class="h-4 w-4" />
                            {{ $form->type }}
                        </span>

                        <span class="inline-flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                            <x-icon name="rectangle-stack" class="h-4 w-4" />
                            {{ $form->allows_multiple_responses ? 'Múltiplas respostas' : 'Resposta única' }}
                        </span>

                        @if ($form->project)
                            <a href="{{ route('admin.projects.show', $form->project) }}" class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                <x-icon name="briefcase" class="h-4 w-4" />
                                [{{ $form->project->code }}] {{ $form->project->name }}
                            </a>
                        @endif

                        <span class="inline-flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                            <x-icon name="clock" class="h-4 w-4" />
                            Atualizado <x-relative-time :value="$form->updated_at" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('admin.forms.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                >
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Voltar
                </a>

                <button
                    type="button"
                    data-modal-open="save-template-modal"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <x-icon name="document-duplicate" class="h-4 w-4" />
                    Salvar template
                </button>

                <a
                    href="{{ route('admin.forms.evaluate', $form) }}"
                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                >
                    <x-icon name="document-check" class="h-4 w-4" />
                    Avaliar resposta
                </a>
            </div>
        </div>
    </div>

    <div data-form-tabs class="mt-2 flex items-end gap-1 overflow-x-auto border-b border-gray-200 dark:border-gray-800" role="tablist" aria-label="Seções do formulário">
        <button
            type="button"
            data-tab="details"
            role="tab"
            aria-selected="{{ $activeTab === 'details' ? 'true' : 'false' }}"
            class="inline-flex shrink-0 items-center gap-2 border-b-2 px-3 py-3 text-sm font-medium transition-colors {{ $activeTab === 'details' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-100' }}"
        >
            <x-icon name="adjustments-horizontal" class="h-4 w-4" />
            Dados
        </button>

        <button
            type="button"
            data-tab="structure"
            role="tab"
            aria-selected="{{ $activeTab === 'structure' ? 'true' : 'false' }}"
            class="inline-flex shrink-0 items-center gap-2 border-b-2 px-3 py-3 text-sm font-medium transition-colors {{ $activeTab === 'structure' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-100' }}"
        >
            <x-icon name="squares-2x2" class="h-4 w-4" />
            Estrutura
            <span class="rounded-full px-1.5 py-0.5 text-xs font-semibold {{ $activeTab === 'structure' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                {{ $fieldCount }}
            </span>
        </button>
    </div>

    <div data-form-workspace data-initial-tab="{{ $activeTab }}" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">
        <section data-tab-panel="details" role="tabpanel" class="{{ $activeTab === 'details' ? '' : 'hidden' }}">
            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    <x-icon name="pencil-square" class="h-5 w-5" />
                                </span>
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Dados do formulário</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Atualize as informações gerais do formulário.</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.forms.update', $form) }}" class="p-5">
                            @csrf
                            @method('PUT')

                            @include('admin.forms.partials.form', [
                                'form' => $form,
                                'projects' => $projects,
                                'types' => $types,
                                'statuses' => $statuses,
                            ])

                            <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <a
                                    href="{{ route('admin.forms.index') }}"
                                    class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </a>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                                >
                                    <x-icon name="check" class="h-4 w-4" />
                                    Salvar alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            <x-icon name="squares-2x2" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                            Estrutura
                        </h3>

                        <dl class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-gray-50 px-3 py-3 text-center dark:bg-gray-800">
                                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $structureCount }}</dd>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Seções</dt>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-3 text-center dark:bg-gray-800">
                                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $fieldCount }}</dd>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Perguntas</dt>
                            </div>
                        </dl>

                        <button
                            type="button"
                            data-tab-go="structure"
                            class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            Editar estrutura
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            <x-icon name="calendar-days" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                            Registro
                        </h3>

                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Criado por</dt>
                                <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $form->user?->name ?: '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Criado em</dt>
                                <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $form->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Atualizado em</dt>
                                <dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $form->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if ($hasResponses)
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                <x-icon name="document-check" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                Respostas do aplicativo
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $answeredCount }} {{ $answeredCount === 1 ? 'resposta preenchida' : 'respostas preenchidas' }}. Somente leitura.
                            </p>

                            <a
                                href="{{ route('admin.forms.show', $form) }}"
                                class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                            >
                                Visualizar respostas
                                <x-icon name="chevron-right" class="h-4 w-4" />
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section data-tab-panel="structure" role="tabpanel" class="{{ $activeTab === 'structure' ? '' : 'hidden' }}">
            <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon name="squares-2x2" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Estrutura do formulário</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $structureCount }} seções &middot; {{ $fieldCount }} perguntas</p>
                        </div>
                    </div>
                </div>

                @if ($structureError)
                    <div class="mx-5 mt-5 flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                        <x-icon name="exclamation-circle" class="h-4 w-4 shrink-0" />
                        <p>Não foi possível salvar a estrutura. Corrija os campos destacados e tente novamente.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.forms.steps.update', $form) }}" class="px-5 pt-5">
                    @csrf
                    @method('PATCH')

                    @include('admin.forms.partials.builder', [
                        'steps' => $steps,
                        'autosaveUrl' => route('admin.forms.steps.update', $form),
                    ])

                    <div class="sticky bottom-0 -mx-5 mt-6 border-t border-gray-100 bg-gray-50/95 px-5 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">As alterações são salvas separadamente dos dados do formulário.</p>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                            >
                                <x-icon name="check" class="h-4 w-4" />
                                Salvar estrutura
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div
        id="save-template-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="save-template-modal-title"
        @if ($errors->any() && old('modal') === 'template') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="save-template-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="document-duplicate" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Salvar como template
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        As seções e perguntas atuais serão copiadas. O template fica disponível para todos os usuários.
                    </p>
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

            @if ($errors->any() && old('modal') === 'template')
                <div
                    data-modal-error
                    class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">Não foi possível salvar o template. Corrija os itens destacados e tente novamente.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.form-templates.store') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="modal" value="template">
                <input type="hidden" name="form_id" value="{{ $form->id }}">

                <div>
                    <label for="template_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do template</label>
                    <input
                        type="text"
                        name="name"
                        id="template_name"
                        value="{{ old('name', $form->name) }}"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const openModal = (modal) => {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                const error = modal.querySelector('[data-modal-error]');
                if (error) {
                    error.scrollIntoView({ block: 'center' });
                }
            };

            const closeModal = (modal) => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
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

            document.querySelectorAll('[role="dialog"][data-open]').forEach(openModal);

            const workspace = document.querySelector('[data-form-workspace]');
            const tabRoot = document.querySelector('[data-form-tabs]');

            if (workspace && tabRoot) {
                const tabs = [...tabRoot.querySelectorAll('[data-tab]')];
                const panels = [...workspace.querySelectorAll('[data-tab-panel]')];
                const storageKey = 'admin.forms.tab.' + location.pathname;

                const activate = (name, remember) => {
                    tabs.forEach((tab) => {
                        const active = tab.dataset.tab === name;
                        tab.classList.toggle('border-indigo-600', active);
                        tab.classList.toggle('text-indigo-600', active);
                        tab.classList.toggle('dark:border-indigo-400', active);
                        tab.classList.toggle('dark:text-indigo-400', active);
                        tab.classList.toggle('border-transparent', !active);
                        tab.classList.toggle('text-gray-500', !active);
                        tab.classList.toggle('dark:text-gray-400', !active);
                        tab.setAttribute('aria-selected', String(active));
                    });

                    panels.forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.tabPanel !== name);
                    });

                    if (remember) {
                        try {
                            localStorage.setItem(storageKey, name);
                        } catch (error) {
                            // ignore storage errors
                        }
                    }
                };

                tabs.forEach((tab) => {
                    tab.addEventListener('click', () => activate(tab.dataset.tab, true));
                });

                document.querySelectorAll('[data-tab-go]').forEach((link) => {
                    link.addEventListener('click', () => {
                        const name = link.dataset.tabGo;
                        activate(name, true);

                        const tab = tabRoot.querySelector('[data-tab="' + name + '"]');
                        if (tab) {
                            tab.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        }
                    });
                });

                const hasErrors = workspace.dataset.hasErrors === 'true';
                let initial = workspace.dataset.initialTab;

                if (!hasErrors) {
                    try {
                        const stored = localStorage.getItem(storageKey);
                        if (stored && tabs.some((tab) => tab.dataset.tab === stored)) {
                            initial = stored;
                        }
                    } catch (error) {
                        // ignore storage errors
                    }
                }

                activate(initial, false);
            }
        })();
    </script>
</x-layouts.admin>