@props([
    'action',
    'method' => 'POST',
    'followUp' => null,
    'users',
    'statuses',
    'priorities',
    'submitLabel' => 'Salvar follow-up',
])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf

    @if ($method !== 'POST')
        @method($method)
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
            <label for="status" class="{{ $labelClass }}">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="{{ $inputClass }}">
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

        <div>
            <label for="assigned_to" class="{{ $labelClass }}">Responsável</label>
            <select id="assigned_to" name="assigned_to" class="{{ $inputClass }}">
                <option value="">Não atribuído</option>
                @foreach ($users as $user)
                    <option value="{{ $user->getKey() }}" @selected((string) old('assigned_to', $followUp?->assigned_to) === (string) $user->getKey())>
                        {{ $user->full_name }}
                    </option>
                @endforeach
            </select>
            @error('assigned_to')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>
        <x-ui.action :href="$followUp ? route('admin.follow-ups.show', $followUp) : route('admin.follow-ups.index')" variant="secondary">Cancelar</x-ui.action>
    </div>
</form>
