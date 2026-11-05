@props([
    'action',
    'method' => 'POST',
    'followUp' => null,
    'assignees',
    'clients',
    'statuses',
    'priorities',
    'submitLabel' => 'Salvar follow-up',
    'modalMode' => false,
])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    $clientOptions = $clients->map(fn ($client): array => [
        'id' => $client->getKey(),
        'name' => $client->name,
        'projects' => $client->projects->map(fn ($project): array => [
            'id' => $project->getKey(),
            'name' => $project->name,
        ])->values()->all(),
    ])->values()->all();
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-5"
    x-data="{
        status: @js(old('status', $followUp?->status?->value ?? 'pendente')),
        clientId: @js((string) old('client_id', $followUp?->client_id ?? '')),
        projectId: @js((string) old('project_id', $followUp?->project_id ?? '')),
        clients: @js($clientOptions),
        get projects() {
            const client = this.clients.find((item) => String(item.id) === String(this.clientId));

            return client ? client.projects : [];
        },
    }"
>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($modalMode)
        <input type="hidden" name="modal" value="create">
    @endif

    <div>
        <label for="title" class="{{ $labelClass }}">Título <span class="text-red-500">*</span></label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $followUp?->title) }}"
            required
            maxlength="150"
            placeholder="Ex.: Retornar contato com o fornecedor"
            class="{{ $inputClass }}"
        >
        @error('title')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="code" class="{{ $labelClass }}">Código <span class="font-normal text-gray-400">(opcional)</span></label>
        <input
            id="code"
            name="code"
            type="text"
            value="{{ old('code', $followUp?->code) }}"
            maxlength="50"
            placeholder="Ex.: FU-2026-001"
            class="{{ $inputClass }}"
        >
        @error('code')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="{{ $labelClass }}">Descrição</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            maxlength="5000"
            placeholder="Contexto, próximos passos e observações..."
            class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
        >{{ old('description', $followUp?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="client_id" class="{{ $labelClass }}">Cliente</label>
            <select
                id="client_id"
                name="client_id"
                x-model="clientId"
                x-on:change="projectId = ''"
                class="{{ $inputClass }}"
            >
                <option value="">Sem cliente</option>
                <template x-for="client in clients" :key="client.id">
                    <option :value="client.id" x-text="client.name" :selected="String(client.id) === String(clientId)"></option>
                </template>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cadastre clientes e projetos nas configurações do plugin.</p>
            @error('client_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="project_id" class="{{ $labelClass }}">Projeto</label>
            <select
                id="project_id"
                name="project_id"
                x-model="projectId"
                x-bind:disabled="! clientId"
                class="{{ $inputClass }} disabled:cursor-not-allowed disabled:opacity-60"
            >
                <option value="">Sem projeto</option>
                <template x-for="project in projects" :key="project.id">
                    <option :value="project.id" x-text="project.name" :selected="String(project.id) === String(projectId)"></option>
                </template>
            </select>
            @error('project_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="status" class="{{ $labelClass }}">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" x-model="status" required class="{{ $inputClass }}">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $followUp?->status?->value ?? 'pendente') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="priority" class="{{ $labelClass }}">Prioridade <span class="text-red-500">*</span></label>
            <select id="priority" name="priority" required class="{{ $inputClass }}">
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', $followUp?->priority?->value ?? 'media') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('priority')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="due_date" class="{{ $labelClass }}">Prazo</label>
            <input
                id="due_date"
                name="due_date"
                type="date"
                value="{{ old('due_date', $followUp?->due_date?->format('Y-m-d')) }}"
                class="{{ $inputClass }}"
            >
            @error('due_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div x-show="status === 'concluido'" x-cloak>
            <label for="completed_at" class="{{ $labelClass }}">Data de conclusão</label>
            <input
                id="completed_at"
                name="completed_at"
                type="date"
                value="{{ old('completed_at', $followUp?->completed_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="{{ $inputClass }}"
            >
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Deixe a data atual ou informe outra data de conclusão.</p>
            @error('completed_at')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="assignee_id" class="{{ $labelClass }}">Responsável</label>
            <select id="assignee_id" name="assignee_id" class="{{ $inputClass }}">
                <option value="">Não atribuído</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->getKey() }}" @selected((string) old('assignee_id', $followUp?->assignee_id) === (string) $assignee->getKey())>
                        {{ $assignee->name }}
                    </option>
                @endforeach
            </select>
            @error('assignee_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>

        @if ($modalMode)
            <x-ui.action type="button" variant="secondary" x-on:click="$dispatch('close-follow-up-create')">Cancelar</x-ui.action>
        @else
            <x-ui.action :href="$followUp ? route('admin.follow-ups.show', $followUp) : route('admin.follow-ups.index')" variant="secondary">Cancelar</x-ui.action>
        @endif
    </div>
</form>
