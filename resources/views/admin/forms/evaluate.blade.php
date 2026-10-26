<x-layouts.admin :title="'Avaliação '.$form->code">
    @php
        $sections = is_array($steps) ? $steps : [];
        $sectionCount = count($sections);
        $parecerIndex = $sectionCount;
        $wizardTotal = $sectionCount + 1;
        $totalFields = collect($sections)->sum(fn (array $step) => count($step['fields'] ?? []));
        $imageDecisionsSeed = collect($sections)
            ->flatMap(fn (array $step) => $step['fields'] ?? [])
            ->filter(fn (array $field) => ! empty($field['images']) && ! empty($field['imageDecisions']))
            ->mapWithKeys(fn (array $field) => [$field['id'] => $field['imageDecisions']])
            ->all();
        $navItems = collect($sections)->map(fn (array $step, int $index) => [
            'index' => $index,
            'title' => ($step['title'] ?? null) ?: 'Seção '.($index + 1),
            'meta' => count($step['fields'] ?? []).' '.(count($step['fields'] ?? []) === 1 ? 'pergunta' : 'perguntas'),
        ])->values()->all();
        $navItems[] = ['index' => $parecerIndex, 'title' => 'Parecer', 'meta' => 'Resultado e observações'];
        $initialStatus = old('status', in_array($form->status, ['Aprovado', 'Reprovado'], true) ? $form->status : '');
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="document-check" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Avaliação da resposta
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $form->code }} &middot; {{ $form->name }}
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-form-status :status="$form->status" />
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $form->type }}</span>
                @if ($form->user)
                    <span class="text-sm text-gray-500 dark:text-gray-400">Por {{ $form->user->name }}</span>
                @endif
                @if ($form->project)
                    <a href="{{ route('admin.projects.show', $form->project) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        [{{ $form->project->code }}] {{ $form->project->name }}
                    </a>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('view', $form)
                <a
                    href="{{ route('admin.forms.show', $form) }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <x-icon name="eye" class="h-4 w-4" />
                    Ver formulário
                </a>
            @endcan
            <a
                href="{{ route('admin.forms.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <x-icon name="exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="font-medium">Não foi possível registrar a avaliação. Corrija os itens destacados e tente novamente.</p>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.forms.evaluations.store', $form) }}"
        class="mt-6"
        x-data="{
            step: 0,
            total: {{ $wizardTotal }},
            titles: @js(collect($navItems)->pluck('title')),
            lightbox: null,
            touchStartX: 0,
            status: @js($initialStatus),
            notes: @js(old('notes', '')),
            rejectModal: false,
            rejectReason: '',
            imageDecisions: @js((object) $imageDecisionsSeed),
            csrfToken: @js(csrf_token()),
            saveUrl: @js(route('admin.forms.evaluations.autosave', $form)),
            saving: false,
            savedAt: null,
            saveError: false,
            saveTimer: null,
            pendingSave: false,
            scheduleSave(delay = 700) {
                clearTimeout(this.saveTimer);
                this.saveTimer = setTimeout(() => this.save(), delay);
            },
            async save() {
                if (this.saving) {
                    this.pendingSave = true;
                    return;
                }
                this.saving = true;
                this.saveError = false;
                try {
                    const response = await fetch(this.saveUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new FormData(this.$root),
                    });
                    if (! response.ok) {
                        throw new Error('Falha ao salvar a avaliação.');
                    }
                    const data = await response.json();
                    this.savedAt = data.saved_at;
                    const history = document.querySelector('[data-evaluations-history]');
                    if (data.evaluation_html && history) {
                        history.querySelector('[data-empty-evaluations]')?.remove();
                        history.insertAdjacentHTML('afterbegin', data.evaluation_html);
                    }
                } catch (error) {
                    this.saveError = true;
                } finally {
                    this.saving = false;
                    if (this.pendingSave) {
                        this.pendingSave = false;
                        this.save();
                    }
                }
            },
            openReject() {
                this.rejectReason = this.notes || '';
                this.rejectModal = true;
            },
            confirmReject() {
                if (this.rejectReason.trim() === '') {
                    return;
                }
                this.notes = this.rejectReason;
                this.status = 'Reprovado';
                this.rejectModal = false;
                this.$nextTick(() => this.save());
            },
            cancelReject() {
                this.rejectModal = false;
            },
            get isLast() {
                return this.step >= this.total - 1;
            },
            get progress() {
                return Math.round(((this.step + 1) / this.total) * 100);
            },
            goTo(index) {
                if (!(index >= 0) || !(index <= this.total - 1)) {
                    return;
                }
                this.step = index;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            next() {
                this.goTo(this.step + 1);
            },
            prev() {
                this.goTo(this.step - 1);
            },
            openImage(fieldId, images, index) {
                this.lightbox = { fieldId: fieldId, images: images, index: index };
            },
            close() {
                this.lightbox = null;
            },
            go(offset) {
                if (! this.lightbox) {
                    return;
                }
                const target = this.lightbox.index + offset;
                if (target >= 0 && target <= this.lightbox.images.length - 1) {
                    this.lightbox.index = target;
                }
            },
            imageStatus(fieldId, index) {
                return (this.imageDecisions[fieldId] && this.imageDecisions[fieldId][index]) || null;
            },
            setImage(fieldId, index, status) {
                if (! this.imageDecisions[fieldId]) {
                    this.imageDecisions[fieldId] = {};
                }
                this.imageDecisions[fieldId][index] = status;
                this.scheduleSave(150);
            },
            decide(status) {
                if (this.lightbox) {
                    this.setImage(this.lightbox.fieldId, this.lightbox.index, status);
                }
            },
            swipe(event) {
                const delta = event.changedTouches[0].clientX - this.touchStartX;
                if (Math.abs(delta) >= 40) {
                    this.go(delta <= -1 ? 1 : -1);
                }
            },
        }"
        @keydown.escape.window="close(); cancelReject()"
        @keydown.arrow-right.window="! rejectModal && go(1)"
        @keydown.arrow-left.window="! rejectModal && go(-1)"
    >
        @csrf

        <input type="hidden" name="image_decisions_json" :value="JSON.stringify(imageDecisions)">

        {{-- Mobile stepper --}}
        <div class="lg:hidden">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Etapa <span x-text="step + 1"></span> de {{ $wizardTotal }}
                </p>
                <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-300" x-text="titles[step]"></p>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                <div class="h-full rounded-full bg-gray-900 transition-all duration-300 dark:bg-white" :style="'width: ' + progress + '%'"></div>
            </div>
            <div class="mt-3 flex gap-1.5 overflow-x-auto pb-1">
                @foreach ($navItems as $item)
                    <button
                        type="button"
                        @click="goTo({{ $item['index'] }})"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold transition"
                        :class="step === {{ $item['index'] }}
                            ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                            : (step > {{ $item['index'] }} ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400')"
                    >
                        {{ $item['index'] + 1 }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-6 lg:mt-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            {{-- Desktop sidebar stepper --}}
            <aside class="hidden lg:block">
                <div class="sticky top-6">
                    <p class="px-1 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Seções
                    </p>
                    <div class="mt-2 flex flex-col gap-1">
                        @foreach ($navItems as $item)
                            <button
                                type="button"
                                @click="goTo({{ $item['index'] }})"
                                class="flex items-start gap-3 rounded-lg px-3 py-2.5 text-left transition"
                                :class="step === {{ $item['index'] }}
                                    ? 'bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-800'
                                    : 'hover:bg-gray-100 dark:hover:bg-gray-800/60'"
                            >
                                <span
                                    class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold transition"
                                    :class="step === {{ $item['index'] }}
                                        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                        : (step > {{ $item['index'] }} ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300')"
                                >
                                    {{ $item['index'] + 1 }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item['title'] }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $item['meta'] }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-4 rounded-lg border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Progresso</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-gray-900 transition-all duration-300 dark:bg-white" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Step panels --}}
            <div class="min-w-0 space-y-4">
                @forelse ($sections as $index => $step)
                    <section
                        x-show="step === {{ $index }}"
                        x-transition.opacity
                        @if ($index !== 0) style="display: none;" @endif
                        class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $index + 1 }}
                                </span>
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ($step['title'] ?? null) ?: 'Seção' }}</h2>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ count($step['fields'] ?? []) }} {{ count($step['fields'] ?? []) === 1 ? 'pergunta' : 'perguntas' }}</span>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse (($step['fields'] ?? []) as $field)
                                @php($fieldId = $field['id'] ?? null)
                                @php($currentStatus = $field['status'] ?? null)
                                @php($decisionValue = old($fieldId ? "decisions.{$fieldId}" : 'decisions', in_array($currentStatus, ['aprovado', 'reprovado', 'nao_aplicavel'], true) ? $currentStatus : ''))

                                <div class="px-5 py-5 sm:px-6">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-6">
                                        <div class="min-w-0 flex-1 space-y-3">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ($field['label'] ?? null) ?: ($fieldId ?? 'Campo') }}</p>
                                                    <x-form-field-status :status="$currentStatus" />
                                                </div>
                                                @if (!empty($fieldId))
                                                    <p class="mt-0.5 font-mono text-xs text-gray-400 dark:text-gray-500">{{ $fieldId }} &middot; {{ $field['type'] ?? 'text' }}</p>
                                                @endif
                                            </div>

                                            @if (($field['type'] ?? null) === 'image')
                                                @if (!empty($field['images']))
                                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                                                        @foreach ($field['images'] as $image)
                                                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                                                                <button
                                                                    type="button"
                                                                    title="Ampliar imagem"
                                                                    class="group relative block w-full cursor-zoom-in"
                                                                    @click="openImage(@js($fieldId), @js($field['images']), {{ $loop->index }})"
                                                                >
                                                                    <img
                                                                        src="{{ $image }}"
                                                                        alt="{{ $field['label'] ?? 'Foto' }}"
                                                                        loading="lazy"
                                                                        class="h-32 w-full object-cover transition group-hover:opacity-80"
                                                                    >
                                                                    <span
                                                                        x-show="imageStatus(@js($fieldId), {{ $loop->index }})"
                                                                        style="display: none;"
                                                                        :class="imageStatus(@js($fieldId), {{ $loop->index }}) === 'aprovado' ? 'bg-green-600' : 'bg-red-600'"
                                                                        x-text="imageStatus(@js($fieldId), {{ $loop->index }}) === 'aprovado' ? 'Aprovada' : 'Reprovada'"
                                                                        class="absolute left-2 top-2 rounded-full px-2 py-0.5 text-xs font-medium text-white shadow"
                                                                    ></span>
                                                                </button>

                                                                <div class="flex flex-col gap-1 border-t border-gray-100 p-2 dark:border-gray-800">
                                                                    <button
                                                                        type="button"
                                                                        title="Aprovar imagem"
                                                                        aria-label="Aprovar imagem"
                                                                        @click="setImage(@js($fieldId), {{ $loop->index }}, 'aprovado')"
                                                                        :class="imageStatus(@js($fieldId), {{ $loop->index }}) === 'aprovado' ? 'border-green-600 bg-green-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-green-50 hover:text-green-700 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-green-950/40'"
                                                                        class="inline-flex items-center justify-center gap-1 rounded-md border px-2 py-1.5 text-xs font-medium transition"
                                                                    >
                                                                        <x-icon name="check-circle" class="h-4 w-4" />
                                                                        Aprovado
                                                                    </button>

                                                                    <button
                                                                        type="button"
                                                                        title="Reprovar imagem"
                                                                        aria-label="Reprovar imagem"
                                                                        @click="setImage(@js($fieldId), {{ $loop->index }}, 'reprovado')"
                                                                        :class="imageStatus(@js($fieldId), {{ $loop->index }}) === 'reprovado' ? 'border-red-600 bg-red-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-red-950/40'"
                                                                        class="inline-flex items-center justify-center gap-1 rounded-md border px-2 py-1.5 text-xs font-medium transition"
                                                                    >
                                                                        <x-icon name="x-circle" class="h-4 w-4" />
                                                                        Reprovado
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                        <x-icon name="photo" class="h-4 w-4" />
                                                        Nenhuma foto enviada.
                                                    </p>
                                                @endif
                                            @elseif (($field['type'] ?? null) === 'file')
                                                @if (!empty($field['files']))
                                                    <div class="flex flex-col gap-2">
                                                        @foreach ($field['files'] as $index => $file)
                                                            @php($name = $field['fileNames'][$index] ?? basename(parse_url($file, PHP_URL_PATH) ?: $file))
                                                            <a
                                                                href="{{ $file }}"
                                                                target="_blank"
                                                                rel="noopener"
                                                                class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-200 dark:hover:bg-gray-800/60"
                                                            >
                                                                <x-file-type-icon :name="$name" size="xs" />
                                                                <span class="min-w-0 flex-1 truncate">{{ $name }}</span>
                                                                <x-icon name="arrow-down-tray" class="h-4 w-4 shrink-0 text-gray-400" />
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                        <x-icon name="paper-clip" class="h-4 w-4" />
                                                        Nenhum arquivo enviado.
                                                    </p>
                                                @endif
                                            @else
                                                <div class="whitespace-pre-wrap rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-200">
                                                    {{ ($field['value'] ?? null) ?: '—' }}
                                                </div>
                                            @endif
                                        </div>

                                        @if ($fieldId !== null)
                                            <div class="shrink-0 lg:w-64">
                                                <span class="block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Avaliação do campo</span>
                                                <div class="mt-1.5 flex overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700">
                                                    @foreach (['nao_aplicavel' => 'Não aplicável', 'aprovado' => 'Aprovar', 'reprovado' => 'Reprovar'] as $value => $optionLabel)
                                                        <label class="relative flex flex-1 cursor-pointer border-l border-gray-300 first:border-l-0 dark:border-gray-700">
                                                            <input
                                                                type="radio"
                                                                name="decisions[{{ $fieldId }}]"
                                                                value="{{ $value }}"
                                                                class="peer sr-only"
                                                                @checked($decisionValue === $value)
                                                                @change="scheduleSave(250)"
                                                            >
                                                            <span class="flex h-full w-full items-center justify-center px-1.5 py-2 text-center text-xs font-medium leading-tight transition peer-focus-visible:ring-2 peer-focus-visible:ring-inset peer-focus-visible:ring-indigo-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/60 {{ $value === 'aprovado' ? 'peer-checked:bg-green-600 peer-checked:text-white' : ($value === 'reprovado' ? 'peer-checked:bg-red-600 peer-checked:text-white' : 'peer-checked:bg-gray-300 peer-checked:text-gray-900 dark:peer-checked:bg-gray-600 dark:peer-checked:text-white') }}">
                                                                {{ $optionLabel }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @error("decisions.{$fieldId}")
                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Esta seção não possui perguntas.</p>
                            @endforelse
                        </div>
                    </section>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Este formulário ainda não possui uma estrutura para avaliar.
                    </div>
                @endforelse

                {{-- Parecer step --}}
                <section
                    x-show="step === {{ $parecerIndex }}"
                    x-transition.opacity
                    @if ($parecerIndex !== 0) style="display: none;" @endif
                    class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <x-icon name="chat-bubble-oval-left-ellipsis" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Parecer da avaliação</h2>
                    </div>

                    <div class="space-y-6 px-5 py-5 sm:px-6">
                        @if ($totalFields > 0)
                            <p class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                <x-icon name="clipboard-document-list" class="h-4 w-4" />
                                Você avaliou {{ $totalFields }} {{ $totalFields === 1 ? 'pergunta' : 'perguntas' }} em {{ count($sections) }} {{ count($sections) === 1 ? 'seção' : 'seções' }}.
                            </p>
                        @endif

                        <div>
                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resultado geral</span>
                            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition"
                                    :class="status === 'Aprovado' ? 'border-green-500 bg-green-50 dark:border-green-700 dark:bg-green-950/40' : 'border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/60'"
                                >
                                    <input type="radio" name="status" value="Aprovado" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" :checked="status === 'Aprovado'" @change="status = 'Aprovado'; scheduleSave(250)">
                                    <x-icon name="check-circle" class="h-5 w-5 text-green-600 dark:text-green-400" />
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Aprovar formulário</span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition"
                                    :class="status === 'Reprovado' ? 'border-red-500 bg-red-50 dark:border-red-800 dark:bg-red-950/40' : 'border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/60'"
                                    @click.prevent="openReject()"
                                >
                                    <input type="radio" name="status" value="Reprovado" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" :checked="status === 'Reprovado'" @click.prevent="openReject()">
                                    <x-icon name="x-circle" class="h-5 w-5 text-red-600 dark:text-red-400" />
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Reprovar formulário</span>
                                </label>
                            </div>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p x-show="status === 'Reprovado' && notes.trim() !== ''" style="display: none;" class="mt-2 flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400">
                                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                Reprovação justificada no parecer.
                            </p>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parecer / observações</label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="5"
                                x-model="notes"
                                @input="scheduleSave()"
                                placeholder="Descreva o parecer da avaliação."
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                            >{{ old('notes') }}</textarea>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Obrigatório quando o formulário for reprovado.</p>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>
        </div>

        {{-- Action bar --}}
        <div class="sticky bottom-0 z-20 -mx-4 mt-6 border-t border-gray-200 bg-white/85 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/85">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-3">
                <a
                    href="{{ route('admin.forms.index') }}"
                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Cancelar
                </a>

                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1.5 text-xs" aria-live="polite">
                        <span x-show="saving" class="text-gray-400 dark:text-gray-500">Salvando…</span>
                        <span x-show="! saving && ! saveError && savedAt" style="display: none;" class="text-green-600 dark:text-green-400">
                            Salvo às <span x-text="savedAt"></span>
                        </span>
                        <span x-show="saveError" style="display: none;" class="text-red-600 dark:text-red-400">Erro ao salvar</span>
                    </span>

                    <span class="hidden text-sm text-gray-500 dark:text-gray-400 sm:inline">
                        Etapa <span x-text="step + 1"></span> de {{ $wizardTotal }}
                    </span>

                    <button
                        type="button"
                        @click="prev()"
                        x-show="step > 0"
                        style="display: none;"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <x-icon name="chevron-left" class="h-4 w-4" />
                        Anterior
                    </button>

                    <button
                        type="button"
                        @click="next()"
                        x-show="! isLast"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        Próximo
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </button>

                    <button
                        type="submit"
                        x-show="isLast"
                        @click="if (status === 'Reprovado' && notes.trim() === '') { $event.preventDefault(); openReject(); }"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Registrar avaliação
                    </button>
                </div>
            </div>
        </div>

        {{-- Rejection justification modal --}}
        <div
            x-show="rejectModal"
            x-transition.opacity
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reject-modal-title"
            @click.self="cancelReject()"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/50">
                        <x-icon name="x-circle" class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </span>
                    <div class="min-w-0">
                        <h2 id="reject-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Justificar a reprovação</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informe o motivo da reprovação. O texto será salvo no parecer da avaliação.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="reject_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Justificativa</label>
                    <textarea
                        id="reject_reason"
                        x-model="rejectReason"
                        rows="4"
                        placeholder="Descreva o motivo da reprovação."
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:outline-none focus:ring-red-500 dark:border-gray-700 dark:bg-gray-900"
                    ></textarea>
                    <p x-show="rejectReason.trim() === ''" class="mt-1 text-xs text-gray-400 dark:text-gray-500">A justificativa é obrigatória.</p>
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        @click="cancelReject()"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        @click="confirmReject()"
                        :disabled="rejectReason.trim() === ''"
                        class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Confirmar reprovação
                    </button>
                </div>
            </div>
        </div>

        <div
            x-show="lightbox"
            x-transition.opacity
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/80 p-4"
            role="dialog"
            aria-modal="true"
            aria-label="Pré-visualização da imagem"
            @click.self="close()"
            @touchstart="touchStartX = $event.changedTouches[0].clientX"
            @touchend="swipe($event)"
        >
            <button
                type="button"
                class="absolute right-4 top-4 z-10 rounded-md bg-white/10 p-2 text-white hover:bg-white/20"
                aria-label="Fechar pré-visualização"
                @click="close()"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>

            <button
                type="button"
                x-show="lightbox && lightbox.index !== 0"
                class="absolute left-4 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20"
                aria-label="Imagem anterior"
                @click="go(-1)"
            >
                <x-icon name="chevron-left" class="h-6 w-6" />
            </button>

            <button
                type="button"
                x-show="lightbox && lightbox.index !== lightbox.images.length - 1"
                class="absolute right-4 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20"
                aria-label="Próxima imagem"
                @click="go(1)"
            >
                <x-icon name="chevron-right" class="h-6 w-6" />
            </button>

            <img
                :src="lightbox ? lightbox.images[lightbox.index] : null"
                alt="Pré-visualização da imagem"
                class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl"
                @click.stop
            >

            <div class="absolute bottom-4 left-1/2 z-10 flex -translate-x-1/2 items-center gap-2 rounded-full bg-black/50 px-2 py-1.5 backdrop-blur">
                <button
                    type="button"
                    @click="decide('aprovado')"
                    :class="lightbox && imageStatus(lightbox.fieldId, lightbox.index) === 'aprovado' ? 'bg-green-600 text-white' : 'bg-white/10 text-white hover:bg-white/20'"
                    class="inline-flex items-center justify-center rounded-full p-2.5"
                    title="Aprovar imagem"
                    aria-label="Aprovar imagem"
                >
                    <x-icon name="check-circle" class="h-5 w-5" />
                </button>

                <button
                    type="button"
                    @click="decide('reprovado')"
                    :class="lightbox && imageStatus(lightbox.fieldId, lightbox.index) === 'reprovado' ? 'bg-red-600 text-white' : 'bg-white/10 text-white hover:bg-white/20'"
                    class="inline-flex items-center justify-center rounded-full p-2.5"
                    title="Reprovar imagem"
                    aria-label="Reprovar imagem"
                >
                    <x-icon name="x-circle" class="h-5 w-5" />
                </button>

                <span
                    x-show="lightbox && lightbox.images.length > 1"
                    x-text="lightbox ? (lightbox.index + 1) + ' / ' + lightbox.images.length : ''"
                    class="px-1 text-sm font-medium text-white"
                ></span>
            </div>
        </div>
    </form>

    <div class="mt-8">
        <div class="mb-3">
            <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                <x-icon name="chat-bubble-oval-left-ellipsis" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Histórico de avaliações
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registro de quem avaliou, quando e o parecer.</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="divide-y divide-gray-100 dark:divide-gray-800" data-evaluations-history>
                @forelse ($evaluations as $evaluation)
                    @include('admin.forms.partials.evaluation-item', ['evaluation' => $evaluation])
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400" data-empty-evaluations>Nenhuma avaliação registrada até o momento.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
