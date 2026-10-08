@php($project = $project ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $project?->name) }}"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
        <textarea
            name="description"
            id="description"
            rows="3"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >{{ old('description', $project?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de início</label>
        <input
            type="date"
            name="start_date"
            id="start_date"
            value="{{ old('start_date', $project?->start_date?->format('Y-m-d')) }}"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('start_date')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select
            name="status"
            id="status"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach (\App\Enums\ProjectStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $project?->status?->value ?? 'active') === $status->value)>
                    @switch ($status->value)
                        @case('active') Ativo @break
                        @case('completed') Concluído @break
                        @default Arquivado
                    @endswitch
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridade</label>
        <input
            type="number"
            name="priority"
            id="priority"
            min="0"
            max="1000"
            value="{{ old('priority', $project?->priority ?? 500) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('priority')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Orçamento</label>
        <div class="mt-1 flex rounded-md shadow-sm">
            <input
                type="number"
                name="budget"
                id="budget"
                min="0"
                step="0.01"
                value="{{ old('budget', $project?->budget) }}"
                class="block w-full rounded-l-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            <input
                type="text"
                name="currency"
                id="currency"
                maxlength="3"
                size="3"
                value="{{ old('currency', $project?->currency ?? 'BRL') }}"
                aria-label="Moeda"
                class="block w-20 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
        </div>
        @error('budget')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @error('currency')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
