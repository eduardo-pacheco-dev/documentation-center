<x-layouts.admin :title="'Avaliação '.$form->code">
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
                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
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
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-medium">Não foi possível registrar a avaliação. Corrija os itens destacados e tente novamente.</p>
        </div>
    @endif

    @php
        $imageDecisionsSeed = collect($steps)
            ->flatMap(fn (array $step) => $step['fields'] ?? [])
            ->filter(fn (array $field) => ! empty($field['images']) && ! empty($field['imageDecisions']))
            ->mapWithKeys(fn (array $field) => [$field['id'] => $field['imageDecisions']])
            ->all();
    @endphp

    <form
        method="POST"
        action="{{ route('admin.forms.evaluations.store', $form) }}"
        class="mt-6 space-y-6"
        x-data="{
            lightbox: null,
            imageDecisions: @js((object) $imageDecisionsSeed),
            touchStartX: 0,
            openImage(fieldId, images, index) { this.lightbox = { fieldId: fieldId, images: images, index: index }; },
            close() { this.lightbox = null; },
            go(step) {
                if (this.lightbox) {
                    const next = this.lightbox.index + step;
                    if (next >= 0 && next <= this.lightbox.images.length - 1) {
                        this.lightbox.index = next;
                    }
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
        @keydown.escape.window="close()"
        @keydown.arrow-right.window="go(1)"
        @keydown.arrow-left.window="go(-1)"
    >
        @csrf

        <input type="hidden" name="image_decisions_json" :value="JSON.stringify(imageDecisions)">

        <div class="space-y-4">
            @forelse ($steps as $step)
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <x-icon name="rectangle-stack" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $step['title'] ?: 'Seção' }}</h2>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse (($step['fields'] ?? []) as $field)
                            @php($fieldId = $field['id'] ?? null)
                            @php($currentStatus = $field['status'] ?? null)
                            @php($decisionValue = old($fieldId ? "decisions.{$fieldId}" : 'decisions', in_array($currentStatus, ['aprovado', 'reprovado'], true) ? $currentStatus : ''))

                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ ($field['label'] ?? null) ?: ($fieldId ?? 'Campo') }}</p>
                                        <x-form-field-status :status="$currentStatus" />
                                    </div>
                                    @if (!empty($fieldId))
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $fieldId }} &middot; {{ $field['type'] ?? 'text' }}</p>
                                    @endif

                                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                        @if (($field['type'] ?? null) === 'image')
                                            @if (!empty($field['images']))
                                                <div class="flex flex-wrap gap-3">
                                                    @foreach ($field['images'] as $image)
                                                        <div class="flex flex-col items-center gap-1">
                                                            <button
                                                                type="button"
                                                                title="Visualizar imagem"
                                                                class="group relative block cursor-zoom-in overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
                                                                @click="openImage(@js($fieldId), @js($field['images']), {{ $loop->index }})"
                                                            >
                                                                <img
                                                                    src="{{ $image }}"
                                                                    alt="{{ $field['label'] ?? 'Foto' }}"
                                                                    loading="lazy"
                                                                    class="h-24 w-24 object-cover transition group-hover:opacity-80"
                                                                >
                                                                <span
                                                                    x-show="imageStatus(@js($fieldId), {{ $loop->index }})"
                                                                    :class="imageStatus(@js($fieldId), {{ $loop->index }}) === 'aprovado' ? 'bg-green-600' : 'bg-red-600'"
                                                                    x-text="imageStatus(@js($fieldId), {{ $loop->index }}) === 'aprovado' ? 'Aprovada' : 'Reprovada'"
                                                                    class="absolute left-1 top-1 rounded-full px-2 py-0.5 text-xs font-medium text-white shadow"
                                                                ></span>
                                                            </button>

                                                            <div class="flex w-full flex-col gap-1">
                                                                <button
                                                                    type="button"
                                                                    title="Aprovar imagem"
                                                                    aria-label="Aprovar imagem"
                                                                    @click="setImage(@js($fieldId), {{ $loop->index }}, 'aprovado')"
                                                                    :class="imageStatus(@js($fieldId), {{ $loop->index }}) === 'aprovado' ? 'border-green-600 bg-green-600 text-white' : 'border-gray-300 text-gray-500 hover:bg-green-50 hover:text-green-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-green-950/40'"
                                                                    class="inline-flex items-center justify-center gap-1 rounded-md border px-2 py-1 text-xs font-medium"
                                                                >
                                                                    <x-icon name="check-circle" class="h-4 w-4" />
                                                                    Aprovado
                                                                </button>

                                                                <button
                                                                    type="button"
                                                                    title="Reprovar imagem"
                                                                    aria-label="Reprovar imagem"
                                                                    @click="setImage(@js($fieldId), {{ $loop->index }}, 'reprovado')"
                                                                    :class="imageStatus(@js($fieldId), {{ $loop->index }}) === 'reprovado' ? 'border-red-600 bg-red-600 text-white' : 'border-gray-300 text-gray-500 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-red-950/40'"
                                                                    class="inline-flex items-center justify-center gap-1 rounded-md border px-2 py-1 text-xs font-medium"
                                                                >
                                                                    <x-icon name="x-circle" class="h-4 w-4" />
                                                                    Reprovado
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">Nenhuma foto enviada.</span>
                                            @endif
                                        @else
                                            <span class="whitespace-pre-wrap">{{ ($field['value'] ?? null) ?: '—' }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($fieldId !== null)
                                    <div class="shrink-0">
                                        <label for="decision-{{ $fieldId }}" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Avaliação do campo</label>
                                        <select
                                            name="decisions[{{ $fieldId }}]"
                                            id="decision-{{ $fieldId }}"
                                            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:w-44"
                                        >
                                            <option value="" @selected($decisionValue === '')>Não avaliar</option>
                                            <option value="aprovado" @selected($decisionValue === 'aprovado')>Aprovar</option>
                                            <option value="reprovado" @selected($decisionValue === 'reprovado')>Reprovar</option>
                                        </select>
                                        @error("decisions.{$fieldId}")
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Esta seção não possui perguntas.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Este formulário ainda não possui uma estrutura para avaliar.
                </div>
            @endforelse
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Parecer da avaliação</h2>
            </div>

            <div class="space-y-5 px-5 py-5">
                <div>
                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resultado geral</span>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        @foreach (['Aprovado' => 'Aprovar formulário', 'Reprovado' => 'Reprovar formulário'] as $value => $label)
                            @php($checked = old('status', in_array($form->status, ['Aprovado', 'Reprovado'], true) ? $form->status : '') === $value)
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium {{ $checked ? ($value === 'Aprovado' ? 'border-green-300 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300' : 'border-red-300 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300') : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-200' }}">
                                <input type="radio" name="status" value="{{ $value }}" class="h-4 w-4" @checked($checked)>
                                <x-icon :name="$value === 'Aprovado' ? 'check-circle' : 'x-circle'" class="h-4 w-4" />
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parecer / observações</label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="4"
                        placeholder="Descreva o parecer da avaliação (opcional)."
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a
                href="{{ route('admin.forms.index') }}"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="check" class="h-4 w-4" />
                Registrar avaliação
            </button>
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
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($evaluations as $evaluation)
                    <div class="px-5 py-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-form-status :status="$evaluation->status" />
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $evaluation->user?->name ?? '—' }}</span>
                            <x-relative-time :value="$evaluation->created_at" class="text-xs text-gray-500 dark:text-gray-400" />
                        </div>
                        @if ($evaluation->notes)
                            <p class="mt-2 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">{{ $evaluation->notes }}</p>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Nenhuma avaliação registrada até o momento.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
