@php($builderSteps = $form->steps ?? [])

<div x-data="formBuilder(@js($builderSteps))">
    <form method="POST" action="{{ route('admin.forms.steps.update', $form) }}">
        @csrf
        @method('PATCH')

        <input type="hidden" name="steps_json" :value="stepsJson">

        <div class="space-y-4">
            <template x-for="(step, si) in steps" :key="step.id">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                        <x-icon name="squares-2x2" class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" />

                        <input
                            type="text"
                            x-model="step.title"
                            placeholder="Título da seção"
                            class="min-w-0 flex-1 border-0 border-b-2 border-transparent bg-transparent px-1 py-1 text-sm font-semibold text-gray-900 focus:border-indigo-500 focus:outline-none dark:text-gray-100"
                        >

                        <div class="flex items-center gap-0.5">
                            <button type="button" @click="moveStep(si, -1)" :disabled="si === 0" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Mover para cima">
                                <x-icon name="chevron-up" class="h-4 w-4" />
                            </button>
                            <button type="button" @click="moveStep(si, 1)" :disabled="si === steps.length - 1" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Mover para baixo">
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </button>
                            <button type="button" @click="duplicateStep(si)" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Duplicar seção">
                                <x-icon name="document-duplicate" class="h-4 w-4" />
                            </button>
                            <button type="button" @click="removeStep(si)" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400" title="Excluir seção">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3 px-4 py-4">
                        <template x-for="(field, fi) in step.fields" :key="field.id">
                            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <input
                                        type="text"
                                        x-model="field.label"
                                        placeholder="Pergunta"
                                        class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                    >

                                    <select
                                        x-model="field.type"
                                        @change="onTypeChange(field)"
                                        class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                    >
                                        <template x-for="type in types" :key="type.value">
                                            <option :value="type.value" x-text="type.label"></option>
                                        </template>
                                    </select>
                                </div>

                                <input
                                    type="text"
                                    x-model="field.help"
                                    placeholder="Texto de ajuda (opcional)"
                                    class="mt-2 block w-full rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-600 shadow-sm focus:border-indigo-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                >

                                <div x-show="isChoice(field.type)" style="display: none;" class="mt-3 space-y-2">
                                    <template x-for="(option, oi) in (field.options || [])" :key="oi">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 shrink-0 text-center text-sm text-gray-400 dark:text-gray-500" x-text="choiceGlyph(field.type)"></span>
                                            <input
                                                type="text"
                                                x-model="field.options[oi]"
                                                placeholder="Opção"
                                                class="min-w-0 flex-1 rounded-md border border-gray-200 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900"
                                            >
                                            <button type="button" @click="removeOption(field, oi)" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Remover opção">
                                                <x-icon name="x-mark" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </template>

                                    <button type="button" @click="addOption(field)" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                        <x-icon name="plus" class="h-4 w-4" />
                                        Adicionar opção
                                    </button>
                                </div>

                                <div x-show="field.type === 'scale'" style="display: none;" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <label class="block">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Mínimo</span>
                                        <select x-model.number="field.scaleMin" class="mt-1 block w-full rounded-md border border-gray-200 px-2 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            <option value="0">0</option>
                                            <option value="1">1</option>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Máximo</span>
                                        <select x-model.number="field.scaleMax" class="mt-1 block w-full rounded-md border border-gray-200 px-2 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            <template x-for="value in [2,3,4,5,6,7,8,9,10]" :key="value">
                                                <option :value="value" x-text="value"></option>
                                            </template>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Rótulo mínimo</span>
                                        <input type="text" x-model="field.scaleMinLabel" placeholder="Ex.: Discordo" class="mt-1 block w-full rounded-md border border-gray-200 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900">
                                    </label>
                                    <label class="block">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Rótulo máximo</span>
                                        <input type="text" x-model="field.scaleMaxLabel" placeholder="Ex.: Concordo" class="mt-1 block w-full rounded-md border border-gray-200 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900">
                                    </label>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                        <input type="checkbox" x-model="field.required" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                                        Obrigatória
                                    </label>

                                    <div class="flex items-center gap-0.5">
                                        <button type="button" @click="moveField(si, fi, -1)" :disabled="fi === 0" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Mover para cima">
                                            <x-icon name="chevron-up" class="h-4 w-4" />
                                        </button>
                                        <button type="button" @click="moveField(si, fi, 1)" :disabled="fi === step.fields.length - 1" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Mover para baixo">
                                            <x-icon name="chevron-down" class="h-4 w-4" />
                                        </button>
                                        <button type="button" @click="duplicateField(si, fi)" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Duplicar pergunta">
                                            <x-icon name="document-duplicate" class="h-4 w-4" />
                                        </button>
                                        <button type="button" @click="removeField(si, fi)" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400" title="Excluir pergunta">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addField(si)" class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 hover:border-indigo-400 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:text-indigo-400">
                            <x-icon name="plus" class="h-4 w-4" />
                            Adicionar pergunta
                        </button>
                    </div>
                </div>
            </template>

            <p x-show="steps.length === 0" style="display: none;" class="flex items-center gap-2 rounded-xl border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <x-icon name="squares-2x2" class="h-4 w-4" />
                Nenhuma seção ainda. Adicione a primeira seção para começar.
            </p>

            <button type="button" @click="addStep()" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                <x-icon name="plus" class="h-4 w-4" />
                Adicionar seção
            </button>
        </div>

        <div class="mt-6 flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="check" class="h-4 w-4" />
                Salvar estrutura
            </button>
        </div>
    </form>
</div>

<script>
    window.formBuilder = function (initialSteps) {
        return {
            steps: [],
            types: [
                { value: 'text', label: 'Texto curto' },
                { value: 'textarea', label: 'Parágrafo' },
                { value: 'number', label: 'Número' },
                { value: 'date', label: 'Data' },
                { value: 'time', label: 'Hora' },
                { value: 'radio', label: 'Múltipla escolha' },
                { value: 'checkbox', label: 'Caixas de seleção' },
                { value: 'dropdown', label: 'Lista suspensa' },
                { value: 'scale', label: 'Escala linear' },
                { value: 'image', label: 'Imagem / foto' },
            ],

            init() {
                this.steps = (initialSteps || []).map((step, index) => ({
                    id: step.id || 'S' + (index + 1),
                    title: step.title || '',
                    fields: (step.fields || []).map((field) => this.hydrateField(field)),
                }));
            },

            get stepsJson() {
                return JSON.stringify(this.steps);
            },

            hydrateField(field) {
                const hydrated = Object.assign({}, field);
                hydrated.type = hydrated.type || 'text';
                hydrated.label = hydrated.label || '';
                hydrated.required = !!hydrated.required;

                if (this.isChoice(hydrated.type)) {
                    hydrated.options = Array.isArray(hydrated.options) ? hydrated.options.slice() : [];
                }

                if (hydrated.type === 'scale') {
                    hydrated.scaleMin = hydrated.scaleMin != null ? hydrated.scaleMin : 1;
                    hydrated.scaleMax = hydrated.scaleMax != null ? hydrated.scaleMax : 5;
                    hydrated.scaleMinLabel = hydrated.scaleMinLabel || '';
                    hydrated.scaleMaxLabel = hydrated.scaleMaxLabel || '';
                }

                return hydrated;
            },

            isChoice(type) {
                return ['radio', 'checkbox', 'dropdown'].indexOf(type) !== -1;
            },

            choiceGlyph(type) {
                if (type === 'checkbox') {
                    return '▢';
                }

                if (type === 'dropdown') {
                    return '▾';
                }

                return '○';
            },

            nextStepId() {
                const used = this.steps.map((step) => step.id);
                let number = 1;

                while (used.indexOf('S' + number) !== -1) {
                    number++;
                }

                return 'S' + number;
            },

            nextFieldId(stepIndex) {
                const step = this.steps[stepIndex];
                const used = (step.fields || []).map((field) => field.id);
                const stepNumber = String(step.id || '').replace(/\D/g, '') || (stepIndex + 1);
                let number = 1;

                while (used.indexOf('F' + stepNumber + '-' + number) !== -1) {
                    number++;
                }

                return 'F' + stepNumber + '-' + number;
            },

            addStep() {
                this.steps.push({ id: this.nextStepId(), title: '', fields: [] });
            },

            removeStep(index) {
                this.steps.splice(index, 1);
            },

            duplicateStep(index) {
                const clone = JSON.parse(JSON.stringify(this.steps[index]));
                const id = this.nextStepId();
                clone.id = id;
                clone.title = (clone.title || 'Seção') + ' (cópia)';
                clone.fields = (clone.fields || []).map((field, fieldIndex) => {
                    field.id = 'F' + id.replace(/\D/g, '') + '-' + (fieldIndex + 1);

                    return field;
                });
                this.steps.splice(index + 1, 0, clone);
            },

            moveStep(index, direction) {
                const target = index + direction;

                if (target < 0 || target >= this.steps.length) {
                    return;
                }

                const [item] = this.steps.splice(index, 1);
                this.steps.splice(target, 0, item);
            },

            addField(stepIndex) {
                const step = this.steps[stepIndex];

                if (!Array.isArray(step.fields)) {
                    step.fields = [];
                }

                step.fields.push({ id: this.nextFieldId(stepIndex), type: 'text', label: '', required: false });
            },

            removeField(stepIndex, fieldIndex) {
                this.steps[stepIndex].fields.splice(fieldIndex, 1);
            },

            duplicateField(stepIndex, fieldIndex) {
                const clone = JSON.parse(JSON.stringify(this.steps[stepIndex].fields[fieldIndex]));
                clone.id = this.nextFieldId(stepIndex);
                this.steps[stepIndex].fields.splice(fieldIndex + 1, 0, clone);
            },

            moveField(stepIndex, fieldIndex, direction) {
                const fields = this.steps[stepIndex].fields;
                const target = fieldIndex + direction;

                if (target < 0 || target >= fields.length) {
                    return;
                }

                const [item] = fields.splice(fieldIndex, 1);
                fields.splice(target, 0, item);
            },

            onTypeChange(field) {
                if (this.isChoice(field.type)) {
                    if (!Array.isArray(field.options) || field.options.length === 0) {
                        field.options = [''];
                    }
                }

                if (field.type === 'scale') {
                    if (field.scaleMin == null) {
                        field.scaleMin = 1;
                    }

                    if (field.scaleMax == null) {
                        field.scaleMax = 5;
                    }

                    if (field.scaleMinLabel == null) {
                        field.scaleMinLabel = '';
                    }

                    if (field.scaleMaxLabel == null) {
                        field.scaleMaxLabel = '';
                    }
                }
            },

            addOption(field) {
                if (!Array.isArray(field.options)) {
                    field.options = [];
                }

                field.options.push('');
            },

            removeOption(field, optionIndex) {
                field.options.splice(optionIndex, 1);
            },
        };
    };
</script>
