@props(['action', 'method' => 'POST', 'checkIn' => null, 'statuses', 'submitLabel' => 'Salvar acompanhamento'])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="{{ $labelClass }}">Título <span class="text-red-500">*</span></label>
            <input id="title" name="title" type="text" value="{{ old('title', $checkIn?->title) }}" required maxlength="150" placeholder="Ex.: Reunião semanal" class="{{ $inputClass }}">
            @error('title')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="reference_date" class="{{ $labelClass }}">Data de referência <span class="text-red-500">*</span></label>
            <input id="reference_date" name="reference_date" type="date" value="{{ old('reference_date', $checkIn?->reference_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="{{ $inputClass }}">
            @error('reference_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="{{ $labelClass }}">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="{{ $inputClass }}">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $checkIn?->status?->value ?? 'em_dia') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="health_score" class="{{ $labelClass }}">Saúde da equipe (1 a 5) <span class="text-red-500">*</span></label>
            <input id="health_score" name="health_score" type="number" min="1" max="5" value="{{ old('health_score', $checkIn?->health_score ?? 3) }}" required class="{{ $inputClass }}">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">1 = crítica, 5 = excelente.</p>
            @error('health_score')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="summary" class="{{ $labelClass }}">Resumo <span class="text-red-500">*</span></label>
            <textarea id="summary" name="summary" rows="3" required maxlength="5000" placeholder="Como está a equipe, entregas, riscos..." class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('summary', $checkIn?->summary) }}</textarea>
            @error('summary')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="next_steps" class="{{ $labelClass }}">Próximos passos</label>
            <textarea id="next_steps" name="next_steps" rows="2" maxlength="5000" placeholder="Ações combinadas..." class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('next_steps', $checkIn?->next_steps) }}</textarea>
            @error('next_steps')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>
        <x-ui.action :href="route('admin.teams.show', $checkIn?->team ?? request()->route('team'))" variant="secondary">Cancelar</x-ui.action>
    </div>
</form>
