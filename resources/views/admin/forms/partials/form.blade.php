@php
    $form = $form ?? null;
    $initialSteps = old('steps_json') !== null
        ? (json_decode(old('steps_json'), true) ?: [])
        : ($form?->steps ?? []);
    $fieldTypes = ['text', 'textarea', 'number', 'date', 'image'];
    $fieldStatuses = ['aprovado', 'reprovado', 'pendente', 'enviado', 'nao_aplicavel'];
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

<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <div>
            <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                <x-icon name="list-steps" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Etapas e campos
            </h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Defina as etapas e campos que o app móvel deverá preencher. As respostas ficam no campo "valor" de cada campo.</p>
        </div>

        <button
            type="button"
            data-steps-add
            class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Adicionar etapa
        </button>
    </div>

    <div class="space-y-4 p-5">
        <div id="steps-builder" class="space-y-4" data-steps-builder></div>

        <input type="hidden" name="steps_json" id="steps_json" value="{{ old('steps_json', json_encode($initialSteps)) }}">
    </div>
</div>

<script>
    (function () {
        const ESCAPED_FIELD_BIND = (i) => `steps.${i}`;

        const steps = {!! json_encode($initialSteps) !!};

        const builder = document.querySelector('[data-steps-builder]');
        const hidden = document.getElementById('steps_json');

        const bind = (el, path) => el.setAttribute('data-bind', path);
        const set = (obj, path, value) => {
            const keys = path.split('.');
            let cursor = obj;
            for (let i = 0; i < keys.length - 1; i++) {
                const key = keys[i];
                if (cursor[key] === undefined || cursor[key] === null) {
                    cursor[key] = /^\d+$/.test(keys[i + 1]) ? [] : {};
                }
                cursor = cursor[key];
            }
            cursor[keys[keys.length - 1]] = value;
        };

        const fieldControl = (stepIndex, fieldIndex, value) => {
            const type = value.type || 'text';

            if (type === 'textarea') {
                return `<textarea data-bind="steps.${stepIndex}.fields.${fieldIndex}.value" rows="2" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">${(value.value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;')}</textarea>`;
            }

            if (type === 'image') {
                const images = Array.isArray(value.images) ? value.images.filter(Boolean) : [];
                return `<textarea data-bind-value="steps.${stepIndex}.fields.${fieldIndex}.images" data-bind-images rows="2" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500" placeholder="Uma URL por linha">${images.join('\n').replace(/&/g, '&amp;').replace(/</g, '&lt;')}</textarea>`;
            }

            return `<input type="${type}" data-bind="steps.${stepIndex}.fields.${fieldIndex}.value" value="${(value.value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;')}" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">`;
        };

        const render = () => {
            if (!steps.length) {
                builder.innerHTML = '<p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma etapa definida. Clique em "Adicionar etapa" para começar.</p>';
                sync();
                return;
            }

            builder.innerHTML = steps.map((step, i) => `
                <div data-step-card class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <div class="flex items-center justify-between gap-3">
                        <div class="grid flex-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">ID da etapa</label>
                                <input type="text" data-bind="steps.${i}.id" value="${(step.id ?? '').replace(/"/g, '&quot;')}" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2.5 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Título</label>
                                <input type="text" data-bind="steps.${i}.title" value="${(step.title ?? '').replace(/"/g, '&quot;')}" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2.5 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="button" data-steps-add-field="${i}" class="inline-flex items-center gap-1 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                                <x-icon name="plus" class="h-3.5 w-3.5" />
                                Campo
                            </button>
                            <button type="button" data-steps-remove="${i}" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400">
                                <span class="sr-only">Remover etapa</span>
                                <x-icon name="x-mark" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 space-y-3" data-step-fields="${i}">
                        ${(step.fields || []).map((field, j) => `
                            <div data-field-card class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="grid flex-1 gap-3 lg:grid-cols-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">ID</label>
                                            <input type="text" data-bind="steps.${i}.fields.${j}.id" value="${(field.id ?? '').replace(/"/g, '&quot;')}" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2.5 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tipo</label>
                                            <select data-bind="steps.${i}.fields.${j}.type" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2.5 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                                ${['text', 'textarea', 'number', 'date', 'image'].map((t) => `<option value="${t}" ${(field.type || 'text') === t ? 'selected' : ''}>${t}</option>`).join('')}
                                            </select>
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Rótulo</label>
                                            <input type="text" data-bind="steps.${i}.fields.${j}.label" value="${(field.label ?? '').replace(/"/g, '&quot;')}" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2.5 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <button type="button" data-steps-remove-field="${i}.${j}" class="rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400">
                                        <span class="sr-only">Remover campo</span>
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>

                                <div class="mt-2 grid gap-3 sm:grid-cols-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Valor</label>
                                        ${fieldControl(i, j, field)}
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                                        <select data-bind="steps.${i}.fields.${j}.status" class="mt-0.5 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2.5 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                            <option value="">—</option>
                                            ${['aprovado', 'reprovado', 'pendente', 'enviado', 'nao_aplicavel'].map((s) => `<option value="${s}" ${(field.status || '') === s ? 'selected' : ''}>${s}</option>`).join('')}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        `).join('')}

                        ${(step.fields || []).length === 0 ? '<p class="text-xs text-gray-500 dark:text-gray-400">Nenhum campo nesta etapa.</p>' : ''}
                    </div>
                </div>
            `).join('');

            sync();
        };

        const sync = () => {
            hidden.value = JSON.stringify(steps);
        };

        builder.addEventListener('input', (event) => {
            const el = event.target;

            if (el.matches('[data-bind]')) {
                set(steps, el.dataset.bind, el.value);
            }

            sync();
        });

        builder.addEventListener('change', (event) => {
            const el = event.target;

            if (el.matches('[data-bind]')) {
                set(steps, el.dataset.bind, el.value);
            }

            if (el.matches('[data-bind-images]')) {
                const images = el.value.split('\n').map((line) => line.trim()).filter(Boolean);
                set(steps, el.dataset.bindValue, images);
            }

            sync();
        });

        builder.addEventListener('click', (event) => {
            const addField = event.target.closest('[data-steps-add-field]');
            const removeField = event.target.closest('[data-steps-remove-field]');
            const remove = event.target.closest('[data-steps-remove]');

            if (addField) {
                const i = Number(addField.dataset.stepsAddField);
                if (!Array.isArray(steps[i].fields)) {
                    steps[i].fields = [];
                }
                steps[i].fields.push({ id: '', type: 'text', label: '', value: '', status: '' });
                render();
                return;
            }

            if (removeField) {
                const [i, j] = removeField.dataset.stepsRemoveField.split('.').map(Number);
                steps[i].fields.splice(j, 1);
                render();
                return;
            }

            if (remove) {
                steps.splice(Number(remove.dataset.stepsRemove), 1);
                render();
            }
        });

        document.querySelectorAll('[data-steps-add]').forEach((button) => {
            button.addEventListener('click', () => {
                steps.push({ id: '', title: '', fields: [{ id: '', type: 'text', label: '', value: '', status: '' }] });
                render();
            });
        });

        render();
    })();
</script>