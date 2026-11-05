@props(['assignees', 'clients', 'statuses', 'priorities', 'followUp' => null])

@php
    $clientOptions = $clients->map(fn ($client): array => [
        'id' => $client->getKey(),
        'name' => $client->name,
        'projects' => $client->projects->map(fn ($project): array => [
            'id' => $project->getKey(),
            'name' => $project->name,
        ])->values()->all(),
    ])->values()->all();

    $fields = [
        'id' => old('follow_up_id', $followUp?->getKey()),
        'title' => old('title', $followUp?->title ?? ''),
        'code' => old('code', $followUp?->code ?? ''),
        'description' => old('description', $followUp?->description ?? ''),
        'client_id' => (string) old('client_id', $followUp?->client_id ?? ''),
        'project_id' => (string) old('project_id', $followUp?->project_id ?? ''),
        'status' => old('status', $followUp?->status?->value ?? 'pendente'),
        'priority' => old('priority', $followUp?->priority?->value ?? 'media'),
        'due_date' => old('due_date', $followUp?->due_date?->format('Y-m-d') ?? ''),
        'completed_at' => old('completed_at', $followUp?->completed_at?->format('Y-m-d') ?? ''),
        'assignee_id' => (string) old('assignee_id', $followUp?->assignee_id ?? ''),
    ];

    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';

    $openOnLoad = $followUp !== null;
@endphp

<div
    x-data="{
        open: {{ $openOnLoad ? 'true' : 'false' }},
        action: @js($followUp ? route('admin.follow-ups.update', $followUp) : ''),
        updateUrlTemplate: @js(route('admin.follow-ups.update', ['followUp' => '__ID__'])),
        fields: @js($fields),
        clients: @js($clientOptions),
        blank() {
            return {
                id: '',
                title: '',
                code: '',
                description: '',
                client_id: '',
                project_id: '',
                status: 'pendente',
                priority: 'media',
                due_date: '',
                completed_at: '',
                assignee_id: '',
            };
        },
        get projects() {
            const client = this.clients.find((item) => String(item.id) === String(this.fields.client_id));

            return client ? client.projects : [];
        },
        openEdit(payload) {
            this.fields = Object.assign(this.blank(), payload);
            this.action = this.updateUrlTemplate.replace('__ID__', String(payload.id));
            this.open = true;

            this.$nextTick(() => this.$refs.title?.focus());
        },
        close() {
            this.open = false;
        },
    }"
    x-on:open-follow-up-edit.window="openEdit($event.detail)"
    x-on:keydown.escape.window="open && close()"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[100] flex items-end justify-center overflow-hidden sm:items-center sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="follow-up-edit-title"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" x-on:click="close()"></div>

        <div class="relative flex h-[calc(100dvh-1rem)] max-h-[calc(100dvh-1rem)] w-full max-w-2xl flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl dark:bg-[#1c1c1b] sm:h-auto sm:max-h-[90vh] sm:rounded-3xl">
            <header class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-6 py-5 dark:border-white/10">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="pencil-square" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="follow-up-edit-title" class="text-base font-semibold text-gray-900 dark:text-white">Editar follow-up</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados e o andamento do acompanhamento.</p>
                    </div>
                </div>

                <button
                    type="button"
                    x-on:click="close()"
                    class="rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"
                    aria-label="Fechar edição"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                @if ($openOnLoad)
                    <div class="mb-5 flex items-start gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>Revise os campos destacados para salvar as alterações.</span>
                    </div>
                @endif

                <form id="follow-up-edit-form" method="POST" x-bind:action="action" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="modal" value="edit">
                    <input type="hidden" name="follow_up_id" x-bind:value="fields.id">

                    <div>
                        <label for="edit-title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Título <span class="text-red-500">*</span></label>
                        <input
                            id="edit-title"
                            name="title"
                            type="text"
                            x-ref="title"
                            x-model="fields.title"
                            required
                            maxlength="150"
                            placeholder="Ex.: Retornar contato com o fornecedor"
                            class="{{ $inputClass }}"
                        >
                        @error('title')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit-code" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Código <span class="font-normal text-gray-400">(opcional)</span></label>
                        <input
                            id="edit-code"
                            name="code"
                            type="text"
                            x-model="fields.code"
                            maxlength="50"
                            placeholder="Ex.: FU-2026-001"
                            class="{{ $inputClass }}"
                        >
                        @error('code')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit-description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descrição</label>
                        <textarea
                            id="edit-description"
                            name="description"
                            rows="4"
                            x-model="fields.description"
                            maxlength="5000"
                            placeholder="Contexto, próximos passos e observações..."
                            class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                        ></textarea>
                        @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="edit-client" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Cliente</label>
                            <select
                                id="edit-client"
                                name="client_id"
                                x-model="fields.client_id"
                                x-on:change="fields.project_id = ''"
                                class="{{ $inputClass }}"
                            >
                                <option value="">Sem cliente</option>
                                <template x-for="client in clients" :key="client.id">
                                    <option :value="String(client.id)" x-text="client.name" :selected="String(client.id) === String(fields.client_id)"></option>
                                </template>
                            </select>
                            @error('client_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="edit-project" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Projeto</label>
                            <select
                                id="edit-project"
                                name="project_id"
                                x-model="fields.project_id"
                                x-bind:disabled="! fields.client_id"
                                class="{{ $inputClass }} disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <option value="">Sem projeto</option>
                                <template x-for="project in projects" :key="project.id">
                                    <option :value="String(project.id)" x-text="project.name" :selected="String(project.id) === String(fields.project_id)"></option>
                                </template>
                            </select>
                            @error('project_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="edit-status" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Status <span class="text-red-500">*</span></label>
                            <select id="edit-status" name="status" x-model="fields.status" required class="{{ $inputClass }}">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="edit-priority" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Prioridade <span class="text-red-500">*</span></label>
                            <select id="edit-priority" name="priority" x-model="fields.priority" required class="{{ $inputClass }}">
                                @foreach ($priorities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('priority')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="edit-due-date" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Prazo</label>
                            <input id="edit-due-date" name="due_date" type="date" x-model="fields.due_date" class="{{ $inputClass }}">
                            @error('due_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="fields.status === 'concluido'" x-cloak>
                            <label for="edit-completed-at" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Data de conclusão</label>
                            <input id="edit-completed-at" name="completed_at" type="date" x-model="fields.completed_at" class="{{ $inputClass }}">
                            @error('completed_at')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="edit-assignee" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Responsável</label>
                            <select id="edit-assignee" name="assignee_id" x-model="fields.assignee_id" class="{{ $inputClass }}">
                                <option value="">Não atribuído</option>
                                @foreach ($assignees as $assignee)
                                    <option value="{{ $assignee->getKey() }}">{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                            @error('assignee_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </form>
            </div>

            <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-6 py-4 dark:border-white/10">
                <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                <x-ui.action type="submit" icon="check" form="follow-up-edit-form">Salvar alterações</x-ui.action>
            </footer>
        </div>
    </div>
</div>
