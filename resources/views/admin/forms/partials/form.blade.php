@php
    $form = $form ?? null;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Projeto</label>
        <select
            name="project_id"
            id="project_id"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="">Selecione o projeto...</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected(old('project_id', (string) ($form?->project_id ?: '')) === (string) $project->id)>
                    [{{ $project->code }}] {{ $project->name }}
                </option>
            @endforeach
        </select>
        @error('project_id')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do formulário</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $form?->name) }}"
            required
            placeholder="Ex.: Registro Fotográfico Completo - Estrutura"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
        <select
            name="type"
            id="type"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('type', $form?->type ?? 'Registro Fotográfico') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('type')
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
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $form?->status ?? 'Pendente') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
