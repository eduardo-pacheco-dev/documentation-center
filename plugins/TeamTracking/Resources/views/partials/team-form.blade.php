@props(['action', 'method' => 'POST', 'team' => null, 'users', 'statuses', 'submitLabel' => 'Salvar equipe'])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div>
        <label for="name" class="{{ $labelClass }}">Nome <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $team?->name) }}" required maxlength="120" placeholder="Ex.: Equipe de Operações" class="{{ $inputClass }}">
        @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="{{ $labelClass }}">Descrição</label>
        <textarea id="description" name="description" rows="3" maxlength="2000" placeholder="Objetivo e escopo da equipe..." class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('description', $team?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="area" class="{{ $labelClass }}">Área</label>
            <input id="area" name="area" type="text" value="{{ old('area', $team?->area) }}" maxlength="100" placeholder="Ex.: Operações" class="{{ $inputClass }}">
            @error('area')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="leader_id" class="{{ $labelClass }}">Líder</label>
            <select id="leader_id" name="leader_id" class="{{ $inputClass }}">
                <option value="">Sem líder</option>
                @foreach ($users as $user)
                    <option value="{{ $user->getKey() }}" @selected((string) old('leader_id', $team?->leader_id) === (string) $user->getKey())>{{ $user->full_name }}</option>
                @endforeach
            </select>
            @error('leader_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="{{ $labelClass }}">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="{{ $inputClass }}">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $team?->status?->value ?? 'ativa') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="started_at" class="{{ $labelClass }}">Início</label>
            <input id="started_at" name="started_at" type="date" value="{{ old('started_at', $team?->started_at?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            @error('started_at')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>
        <x-ui.action :href="$team ? route('admin.teams.show', $team) : route('admin.teams.index')" variant="secondary">Cancelar</x-ui.action>
    </div>
</form>
