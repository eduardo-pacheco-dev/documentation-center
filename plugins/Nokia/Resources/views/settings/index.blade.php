<x-layouts.admin title="Configurações Nokia">
    @php
        $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
        $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    @endphp

    <x-ui.page-header
        category="Nokia"
        title="Configurações"
        description="Gerencie os ROMs que podem ser vinculados aos sites Nokia."
        icon="cog-6-tooth"
    >
        <x-ui.action :href="route('admin.nokia.sites.index')" variant="secondary" icon="chevron-left">
            Voltar para sites
        </x-ui.action>
    </x-ui.page-header>

    <section id="roms" class="mt-6 scroll-mt-6 rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="user-plus" class="h-5 w-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">ROMs</h2>
                        <x-ui.badge tone="indigo">{{ $roms->count() }}</x-ui.badge>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Os ROMs cadastrados ficam disponíveis no campo ROM ao criar ou editar um site.</p>
                </div>
            </div>

            <x-ui.action
                type="button"
                icon="plus"
                class="shrink-0"
                x-on:click="$dispatch('open-rom-modal')"
            >
                Novo ROM
            </x-ui.action>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-white/10">
            @if ($roms->isEmpty())
                <x-ui.empty-state
                    icon="user-plus"
                    title="Nenhum ROM cadastrado"
                    description="Clique em Novo ROM para cadastrar o primeiro."
                    tone="indigo"
                    shape="square"
                >
                    <x-ui.action
                        type="button"
                        size="sm"
                        icon="plus"
                        class="mt-4"
                        x-on:click="$dispatch('open-rom-modal')"
                    >
                        Novo ROM
                    </x-ui.action>
                </x-ui.empty-state>
            @else
                <ul class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 dark:divide-white/10 dark:border-white/10">
                    @foreach ($roms as $rom)
                        <li class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                <x-icon name="user" class="h-4 w-4" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $rom->fullName() }}</p>

                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="envelope" class="h-3.5 w-3.5" />
                                        {{ $rom->email }}
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="phone" class="h-3.5 w-3.5" />
                                        {{ $rom->phone }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <x-ui.action :href="route('admin.nokia.settings.roms.edit', $rom)" size="sm" variant="secondary" icon="pencil-square">
                                    Editar
                                </x-ui.action>

                                <form
                                    method="POST"
                                    action="{{ route('admin.nokia.settings.roms.destroy', $rom) }}"
                                    data-confirm="Excluir o ROM {{ $rom->fullName() }}? Os sites vinculados ficarão sem ROM."
                                    data-confirm-title="Excluir ROM"
                                    data-confirm-label="Excluir"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <x-ui.action type="submit" size="sm" variant="danger" icon="trash" aria-label="Excluir ROM {{ $rom->fullName() }}">
                                        Excluir
                                    </x-ui.action>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    <section id="tasks" class="mt-6 scroll-mt-6 rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="clipboard-document-list" class="h-5 w-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Tarefas</h2>
                        <x-ui.badge tone="green">{{ $tasks->count() }}</x-ui.badge>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">As tarefas cadastradas aparecem na página de cada site para serem marcadas como concluídas.</p>
                </div>
            </div>

            <x-ui.action
                type="button"
                icon="plus"
                class="shrink-0"
                x-on:click="$dispatch('open-task-modal')"
            >
                Nova tarefa
            </x-ui.action>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-white/10">
            <div>
                @if ($tasks->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center dark:border-white/10">
                        <span class="mx-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-white/[0.04] dark:text-gray-500">
                            <x-icon name="clipboard-document-list" class="h-5 w-5" />
                        </span>
                        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">Nenhuma tarefa cadastrada</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Clique em Nova tarefa para cadastrar a primeira.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 dark:divide-white/10 dark:border-white/10">
                        @foreach ($tasks as $task)
                            <li class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center">
                                <form method="POST" action="{{ route('admin.nokia.settings.tasks.update', $task) }}" class="flex min-w-0 flex-1 items-center gap-2">
                                    @csrf
                                    @method('PUT')

                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                        <x-icon name="clipboard-document-list" class="h-4 w-4" />
                                    </span>

                                    <label for="task-name-{{ $task->getKey() }}" class="sr-only">Nome da tarefa</label>
                                    <input
                                        id="task-name-{{ $task->getKey() }}"
                                        name="name"
                                        type="text"
                                        value="{{ $task->name }}"
                                        required
                                        class="h-10 min-w-0 flex-1 rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    >

                                    <x-ui.action type="submit" size="sm" variant="secondary" icon="check">Salvar</x-ui.action>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.nokia.settings.tasks.destroy', $task) }}"
                                    data-confirm="Excluir a tarefa {{ $task->name }}? Ela deixará de aparecer nos sites."
                                    data-confirm-title="Excluir tarefa"
                                    data-confirm-label="Excluir"
                                    class="shrink-0"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <x-ui.action type="submit" size="sm" variant="danger" icon="trash" aria-label="Excluir tarefa {{ $task->name }}">
                                        Excluir
                                    </x-ui.action>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    <div
        x-data="{
            open: @js($errors->has('name')),
            init() {
                if (this.open) {
                    this.$nextTick(() => this.show());
                }
            },
            show() {
                if (this.$refs.dialog.open) {
                    return;
                }

                this.$refs.dialog.showModal();
                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => this.$refs.dialog.querySelector('[data-autofocus]')?.focus());
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
        }"
        x-on:open-task-modal.window="show()"
    >
        <dialog
            id="create-task-modal"
            x-ref="dialog"
            x-on:close="onClose()"
            x-on:click="onBackdropClick($event)"
            class="m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-lg overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
            aria-labelledby="create-task-modal-title"
        >
            <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
                <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <x-icon name="clipboard-document-list" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <h2 id="create-task-modal-title" class="truncate text-base font-semibold sm:text-lg">Nova tarefa</h2>
                            <p class="mt-0.5 hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                                Cadastre a tarefa que aparecerá na página dos sites.
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

                <form method="POST" action="{{ route('admin.nokia.settings.tasks.store') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                        @if ($errors->has('name'))
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/60 dark:bg-red-950/40">
                                <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                                    <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                    Não foi possível cadastrar a tarefa
                                </p>
                            </div>
                        @endif

                        <div>
                            <label for="task-name" class="{{ $labelClass }}">Nome da tarefa</label>
                            <input
                                id="task-name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                data-autofocus
                                placeholder="Ex.: Validar documentação"
                                @error('name') aria-invalid="true" @enderror
                                class="{{ $inputClass }}"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                        <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                        <x-ui.action type="submit" icon="check">Cadastrar tarefa</x-ui.action>
                    </footer>
                </form>
            </div>
        </dialog>
    </div>

    <div
        x-data="{
            open: @js($errors->hasAny(['first_name', 'last_name', 'email', 'phone'])),
            init() {
                if (this.open) {
                    this.$nextTick(() => this.show());
                }
            },
            show() {
                if (this.$refs.dialog.open) {
                    return;
                }

                this.$refs.dialog.showModal();
                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => this.$refs.dialog.querySelector('[data-autofocus]')?.focus());
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
        }"
        x-on:open-rom-modal.window="show()"
    >
        <dialog
            id="create-rom-modal"
            x-ref="dialog"
            x-on:close="onClose()"
            x-on:click="onBackdropClick($event)"
            class="m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-lg overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
            aria-labelledby="create-rom-modal-title"
        >
            <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
                <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon name="user-plus" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <h2 id="create-rom-modal-title" class="truncate text-base font-semibold sm:text-lg">Novo ROM</h2>
                            <p class="mt-0.5 hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                                Cadastre o ROM com nome, sobrenome, e-mail e telefone.
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

                <form method="POST" action="{{ route('admin.nokia.settings.roms.store') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                        @if ($errors->any())
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/60 dark:bg-red-950/40">
                                <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                                    <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                    Não foi possível cadastrar o ROM
                                </p>
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="rom-first-name" class="{{ $labelClass }}">Nome</label>
                                <input
                                    id="rom-first-name"
                                    name="first_name"
                                    type="text"
                                    value="{{ old('first_name') }}"
                                    required
                                    data-autofocus
                                    @error('first_name') aria-invalid="true" @enderror
                                    class="{{ $inputClass }}"
                                >
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="rom-last-name" class="{{ $labelClass }}">Sobrenome</label>
                                <input
                                    id="rom-last-name"
                                    name="last_name"
                                    type="text"
                                    value="{{ old('last_name') }}"
                                    required
                                    @error('last_name') aria-invalid="true" @enderror
                                    class="{{ $inputClass }}"
                                >
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="rom-email" class="{{ $labelClass }}">E-mail</label>
                            <input
                                id="rom-email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                @error('email') aria-invalid="true" @enderror
                                class="{{ $inputClass }}"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="rom-phone" class="{{ $labelClass }}">Telefone</label>
                            <input
                                id="rom-phone"
                                name="phone"
                                type="text"
                                value="{{ old('phone') }}"
                                required
                                placeholder="(11) 99999-9999"
                                @error('phone') aria-invalid="true" @enderror
                                class="{{ $inputClass }}"
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                        <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                        <x-ui.action type="submit" icon="check">Cadastrar ROM</x-ui.action>
                    </footer>
                </form>
            </div>
        </dialog>
    </div>
</x-layouts.admin>
