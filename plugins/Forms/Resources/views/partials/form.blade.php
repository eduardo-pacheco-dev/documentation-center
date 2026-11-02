@php
    $form = $form ?? null;
    $templates = $templates ?? collect();
    $defaultTemplateId = $defaultTemplateId ?? null;
    $defaultStatus = $form?->status ?? (in_array('Pendente', $statuses, true) ? 'Pendente' : ($statuses[0] ?? 'Pendente'));
    $multipleResponses = (bool) old('allows_multiple_responses', $form?->allows_multiple_responses ?? false);
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    @if ($form === null && $templates->isNotEmpty())
        <div class="sm:col-span-2">
            <label for="template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template (opcional)</label>
            <select
                name="template_id"
                id="template_id"
                data-template-select
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="">Começar do zero</option>
                @foreach ($templates as $template)
                    <option
                        value="{{ $template->id }}"
                        data-template-type="{{ $template->type }}"
                        @selected((string) old('template_id', $defaultTemplateId) === (string) $template->id)
                    >
                        {{ $template->name }}@if ($template->type) &mdash; {{ $template->type }}@endif
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">As seções e perguntas do template serão copiadas para o novo formulário.</p>
            @error('template_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="sm:col-span-2">
        <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Projeto
            <span class="font-normal text-gray-400 dark:text-gray-500">(opcional)</span>
        </label>
        <select
            name="project_id"
            id="project_id"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="">Sem projeto</option>
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
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800 dark:disabled:text-gray-400"
        >
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('type', $form?->type ?? ($types[0] ?? 'Registro Fotográfico')) === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <input type="hidden" name="type" id="type-template-value" value="" disabled>
        <p id="type-template-hint" class="mt-1 hidden text-xs text-gray-500 dark:text-gray-400">Definido pelo template selecionado.</p>
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
                <option value="{{ $status }}" @selected(old('status', $defaultStatus) === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="allows_multiple_responses" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Respostas</label>
        <select
            name="allows_multiple_responses"
            id="allows_multiple_responses"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="0" @selected(! $multipleResponses)>Resposta única</option>
            <option value="1" @selected($multipleResponses)>Múltiplas respostas</option>
        </select>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Com resposta única, o mesmo registro é atualizado a cada novo envio. Com múltiplas respostas, cada envio cria uma resposta separada no histórico.
        </p>
        @error('allows_multiple_responses')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

@if ($form === null && $templates->isNotEmpty())
    <script>
        (function () {
            const templateSelect = document.getElementById('template_id');
            const typeSelect = document.getElementById('type');
            const typeValue = document.getElementById('type-template-value');
            const typeHint = document.getElementById('type-template-hint');

            if (!templateSelect || !typeSelect || !typeValue) {
                return;
            }

            const syncType = () => {
                const type = templateSelect.selectedOptions[0]?.dataset.templateType || '';

                if (type === '') {
                    typeSelect.disabled = false;
                    typeValue.disabled = true;
                    typeValue.value = '';
                    typeHint?.classList.add('hidden');

                    return;
                }

                if (!Array.from(typeSelect.options).some((option) => option.value === type)) {
                    typeSelect.add(new Option(type, type));
                }

                typeSelect.value = type;
                typeSelect.disabled = true;
                typeValue.value = type;
                typeValue.disabled = false;
                typeHint?.classList.remove('hidden');
            };

            templateSelect.addEventListener('change', syncType);
            syncType();
        })();
    </script>
@endif
