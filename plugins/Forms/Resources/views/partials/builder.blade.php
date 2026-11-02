@php($builderSteps = $steps ?? [])

<div x-data="formBuilder(@js($builderSteps), @js($autosaveUrl ?? null))">
    <input type="hidden" name="steps_json" :value="stepsJson">

    <div class="flex flex-wrap items-center justify-between gap-3 px-1 pb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <span x-text="steps.length"></span> {{ 'seção' }}<span x-show="steps.length !== 1">s</span>
            &middot;
            <span x-text="totalFields()"></span> {{ 'pergunta' }}<span x-show="totalFields() !== 1">s</span>
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <span x-show="autosaveState === 'pending'" style="display: none;" class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                <x-icon name="arrow-path" class="h-3.5 w-3.5" />
                Alterações não salvas
            </span>
            <span x-show="autosaveState === 'saving'" style="display: none;" class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                <x-icon name="arrow-path" class="h-3.5 w-3.5 animate-spin" />
                Salvando...
            </span>
            <span x-show="autosaveState === 'saved'" style="display: none;" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                Salvo automaticamente
            </span>
            <span x-show="autosaveState === 'error'" style="display: none;" class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 dark:text-red-400" :title="autosaveMessage">
                <x-icon name="exclamation-circle" class="h-3.5 w-3.5" />
                <span x-text="autosaveMessage || 'Não foi possível salvar'"></span>
            </span>

            <div class="flex items-center gap-1">
                <button type="button" @click="collapseAll(true)" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                    <x-icon name="chevron-up-down" class="h-4 w-4" />
                    Recolher tudo
                </button>
                <button type="button" @click="collapseAll(false)" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                    <x-icon name="chevron-down" class="h-4 w-4" />
                    Expandir tudo
                </button>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <template x-for="(step, si) in steps" :key="step.id">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                    <button
                        type="button"
                        @click="toggleCollapsed(si)"
                        :aria-expanded="isCollapsed(step) ? 'false' : 'true'"
                        :title="isCollapsed(step) ? 'Expandir seção' : 'Recolher seção'"
                        class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <span class="block transition-transform duration-150" :class="isCollapsed(step) ? '-rotate-90' : ''">
                            <x-icon name="chevron-down" class="h-4 w-4" />
                        </span>
                    </button>

                    <span
                        class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                        x-text="si + 1"
                    ></span>

                    <input
                        type="text"
                        x-model="step.title"
                        placeholder="Título da seção"
                        class="min-w-0 flex-1 border-b-2 border-transparent bg-transparent px-1 py-1 text-sm font-semibold text-gray-900 focus:border-indigo-500 focus:outline-none dark:text-gray-100 dark:focus:border-indigo-400"
                    >

                    <span class="inline-flex shrink-0 items-center gap-1 rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="squares-2x2" class="h-3.5 w-3.5" />
                        <span x-text="(step.fields || []).length"></span>
                    </span>

                    <div x-show="confirmRemoveStep === si" style="display: none;" class="flex shrink-0 items-center gap-1.5">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Excluir seção?</span>
                        <button type="button" @click="removeStep(si)" class="rounded-md bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-500">Excluir</button>
                        <button type="button" @click="cancelRemoveStep()" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">Cancelar</button>
                    </div>

                    <div x-show="confirmRemoveStep !== si" style="display: none;" class="flex items-center gap-0.5">
                        <button type="button" @click="moveStep(si, -1)" :disabled="si === 0" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Mover para cima">
                            <x-icon name="chevron-up" class="h-4 w-4" />
                        </button>
                        <button type="button" @click="moveStep(si, 1)" :disabled="si === steps.length - 1" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Mover para baixo">
                            <x-icon name="chevron-down" class="h-4 w-4" />
                        </button>
                        <button type="button" @click="duplicateStep(si)" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Duplicar seção">
                            <x-icon name="document-duplicate" class="h-4 w-4" />
                        </button>
                        <button type="button" @click="(step.fields || []).length > 0 ? requestRemoveStep(si) : removeStep(si)" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400" title="Excluir seção">
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div x-show="!isCollapsed(step)" class="space-y-3 px-4 py-4">
                    <template x-for="(field, fi) in step.fields" :key="field.id">
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <span class="hidden w-6 shrink-0 text-center text-xs font-semibold text-gray-400 dark:text-gray-500 sm:block" x-text="(fi + 1) + '.'"></span>

                                <input
                                    type="text"
                                    x-model="field.label"
                                    placeholder="Pergunta"
                                    class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900"
                                >

                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400" x-text="typeBadge(field.type)"></span>

                                    <select
                                        x-model="field.type"
                                        @change="onTypeChange(field)"
                                        class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                    >
                                        <template x-for="type in types" :key="type.value">
                                            <option :value="type.value" x-text="type.label"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <input
                                type="text"
                                x-model="field.help"
                                placeholder="Texto de ajuda (opcional)"
                                class="mt-2 block w-full rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-600 focus:border-indigo-500 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
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
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                        <input type="checkbox" x-model="field.required" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                                        Obrigatória
                                    </label>
                                    <span
                                        x-show="field.required"
                                        style="display: none;"
                                        class="inline-flex items-center rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"
                                    >
                                        Obrigatória
                                    </span>
                                </div>

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

        <p x-show="steps.length === 0" style="display: none;" class="flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            <x-icon name="squares-2x2" class="h-4 w-4" />
            Nenhuma seção ainda. Adicione a primeira seção para começar.
        </p>

        <button type="button" @click="addStep()" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-400 dark:hover:border-indigo-500 dark:hover:text-indigo-400">
            <x-icon name="plus" class="h-4 w-4" />
            Adicionar seção
        </button>
    </div>
</div>

<script>
    window.formBuilder = window.formBuilder || function (initialSteps, autosaveUrl) {
        return {
            steps: [],
            collapsed: {},
            confirmRemoveStep: null,
            autosaveUrl: autosaveUrl || null,
            autosaveState: 'idle',
            autosaveMessage: '',
            autosaveTimer: null,
            autosaveInFlight: false,
            lastSavedJson: null,
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
                { value: 'file', label: 'Arquivo' },
            ],

            typeBadges: {
                text: 'Texto',
                textarea: 'Parágrafo',
                number: 'Número',
                date: 'Data',
                time: 'Hora',
                radio: 'Múltipla',
                checkbox: 'Caixas',
                dropdown: 'Lista',
                scale: 'Escala',
                image: 'Foto',
                file: 'Arquivo',
            },

            init() {
                this.steps = (initialSteps || []).map((step, index) => ({
                    id: step.id || 'S' + (index + 1),
                    title: step.title || '',
                    fields: (step.fields || []).map((field) => this.hydrateField(field)),
                }));

                this.lastSavedJson = this.stepsJson;

                if (this.autosaveUrl) {
                    this.$watch('stepsJson', () => this.scheduleAutosave());
                }
            },

            get stepsJson() {
                return JSON.stringify(this.steps);
            },

            scheduleAutosave() {
                if (! this.autosaveUrl) {
                    return;
                }

                clearTimeout(this.autosaveTimer);
                this.autosaveState = 'pending';
                this.autosaveMessage = '';
                this.autosaveTimer = setTimeout(() => this.autosave(), 800);
            },

            async autosave() {
                clearTimeout(this.autosaveTimer);

                if (! this.autosaveUrl || this.autosaveInFlight) {
                    return;
                }

                const payload = this.stepsJson;

                if (payload === this.lastSavedJson) {
                    this.autosaveState = 'saved';

                    return;
                }

                this.autosaveInFlight = true;
                this.autosaveState = 'saving';
                this.autosaveMessage = '';

                try {
                    const response = await fetch(this.autosaveUrl, {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken(),
                        },
                        body: JSON.stringify({ steps_json: payload }),
                    });

                    if (! response.ok) {
                        throw new Error('autosave-failed');
                    }

                    this.lastSavedJson = payload;

                    if (payload !== this.stepsJson) {
                        this.scheduleAutosave();
                    } else {
                        this.autosaveState = 'saved';
                    }
                } catch (error) {
                    this.autosaveState = 'error';
                    this.autosaveMessage = 'Não foi possível salvar';
                } finally {
                    this.autosaveInFlight = false;
                }
            },

            csrfToken() {
                const form = this.$el.closest('form');
                const input = form ? form.querySelector('input[name="_token"]') : null;

                return input ? input.value : '';
            },

            totalFields() {
                return this.steps.reduce((sum, step) => sum + (step.fields || []).length, 0);
            },

            isCollapsed(step) {
                return !!this.collapsed[step.id];
            },

            toggleCollapsed(stepIndex) {
                const step = this.steps[stepIndex];
                this.collapsed[step.id] = !this.collapsed[step.id];
            },

            collapseAll(collapsed) {
                this.steps.forEach((step) => {
                    this.collapsed[step.id] = collapsed;
                });
            },

            requestRemoveStep(stepIndex) {
                this.confirmRemoveStep = stepIndex;
            },

            cancelRemoveStep() {
                this.confirmRemoveStep = null;
            },

            typeBadge(type) {
                return this.typeBadges[type] || 'Texto';
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
                this.confirmRemoveStep = null;
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