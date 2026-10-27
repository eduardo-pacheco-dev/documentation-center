<x-layouts.public :title="'Preencher '.$form->code" :with-action-bar="! empty($steps)">
    @php
        $questionFields = collect($steps)->flatMap(fn (array $step) => $step['fields'] ?? []);
        $totalQuestions = $questionFields->count();
        $answeredQuestions = $questionFields->filter(
            fn (array $field) => filled($field['value'] ?? null)
                || ! empty($field['images'] ?? [])
                || ! empty($field['files'] ?? []),
        )->count();
        $initialPercent = $totalQuestions > 0
            ? (int) round(($answeredQuestions / $totalQuestions) * 100)
            : 0;

        $sectionCount = count($steps);
        $initialStep = 0;

        foreach ($steps as $index => $step) {
            foreach ($step['fields'] ?? [] as $field) {
                $fieldId = (string) ($field['id'] ?? '');

                if ($fieldId !== '' && ($errors->has('answers.'.$fieldId) || $errors->has('camera_answers.'.$fieldId))) {
                    $initialStep = $index;
                    break 2;
                }
            }
        }

        $initialStepTitle = ($steps[$initialStep]['title'] ?? null) ?: 'Seção '.($initialStep + 1);
    @endphp

    <div class="space-y-4">
        <header class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start gap-3 p-4 sm:p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm dark:bg-indigo-500">
                    <x-icon name="clipboard-document-list" class="h-5 w-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                        Formulário externo
                    </span>

                    <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                        {{ $form->code }}
                    </p>
                    <h1 class="mt-1 text-lg font-semibold leading-snug tracking-tight text-gray-900 dark:text-gray-100">
                        {{ $form->name }}
                    </h1>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="document-text" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                            {{ $form->type }}
                        </span>

                        @if ($form->project)
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <x-icon name="briefcase" class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" />
                                <span class="truncate">[{{ $form->project->code }}] {{ $form->project->name }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($totalQuestions > 0)
                <div
                    class="border-t border-gray-100 px-4 py-3.5 dark:border-gray-800"
                    data-form-progress
                    role="progressbar"
                    aria-label="Progresso do envio"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $initialPercent }}"
                >
                    <div class="flex items-center justify-between gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="chart-bar" class="h-3.5 w-3.5" />
                            Progresso do envio
                        </span>
                        <span class="tabular-nums">
                            <span data-progress-count class="font-semibold text-gray-700 dark:text-gray-200">{{ $answeredQuestions }} de {{ $totalQuestions }}</span>
                            <span class="mx-0.5 text-gray-300 dark:text-gray-600">&middot;</span>
                            <span data-progress-percent class="font-semibold text-gray-700 dark:text-gray-200">{{ $initialPercent }}%</span>
                        </span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            data-progress-bar
                            class="h-full rounded-full bg-indigo-600 transition-all duration-300 dark:bg-indigo-500"
                            style="width: {{ $initialPercent }}%"
                        ></div>
                    </div>
                </div>
            @endif
        </header>

        @if (session('status'))
            <div role="status" class="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if (request()->boolean('upload_error'))
            <div role="alert" class="flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Os arquivos enviados são maiores do que o servidor aceita. Reduza o tamanho das imagens ou documentos e envie novamente.</p>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Não foi possível enviar as respostas. Corrija os itens destacados e tente novamente. As imagens e os arquivos já enviados foram mantidos.</p>
            </div>
        @endif

        @if ($response)
            <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
                <x-icon name="arrow-path" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Este formulário aceita uma única resposta. Ao enviar novamente, o registro existente será atualizado.</p>
            </div>
        @endif

        @if (empty($steps))
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-900">
                <x-icon name="clipboard-document-list" class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Este formulário ainda não possui perguntas.
                </p>
            </div>
        @else
            <form
                method="POST"
                action="{{ URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]) }}"
                enctype="multipart/form-data"
                class="space-y-4"
                data-form-progress-form
                data-form-steps
                data-form-initial-step="{{ $initialStep }}"
                data-form-autosave-url="{{ URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]) }}"
            >
                @csrf

                <div data-form-step-nav class="sticky top-2 z-20 rounded-2xl border border-gray-200/70 bg-white/95 p-3 shadow-sm backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/95">
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                            <x-icon name="rectangle-stack" class="h-3.5 w-3.5" />
                            Etapa <span data-form-step-current class="font-semibold text-gray-900 dark:text-gray-100">{{ $initialStep + 1 }}</span> de {{ $sectionCount }}
                        </span>
                        <span data-form-step-title class="min-w-0 truncate text-right text-xs font-semibold text-gray-900 dark:text-gray-100">{{ $initialStepTitle }}</span>
                    </div>

                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            data-form-step-bar
                            class="h-full rounded-full bg-indigo-600 transition-all duration-300 dark:bg-indigo-500"
                            style="width: {{ $sectionCount > 0 ? (int) round((($initialStep + 1) / $sectionCount) * 100) : 0 }}%"
                        ></div>
                    </div>

                    <div class="-mb-0.5 mt-3 flex gap-1.5 overflow-x-auto pb-0.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        @for ($index = 0; $index < $sectionCount; $index++)
                            @php($sectionTitle = ($steps[$index]['title'] ?? null) ?: 'Seção '.($index + 1))
                            <button
                                type="button"
                                data-form-step-go="{{ $index }}"
                                data-state="{{ $index === $initialStep ? 'current' : ($index < $initialStep ? 'complete' : 'upcoming') }}"
                                @if ($index === $initialStep) aria-current="step" @endif
                                aria-label="Ir para a etapa {{ $index + 1 }}: {{ $sectionTitle }}"
                                class="inline-flex min-h-9 shrink-0 items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 data-[state=current]:border-gray-900 data-[state=current]:bg-gray-900 data-[state=current]:text-white dark:data-[state=current]:border-white dark:data-[state=current]:bg-white dark:data-[state=current]:text-gray-900 dark:focus-visible:ring-offset-gray-900 data-[state=complete]:border-emerald-200 data-[state=complete]:bg-emerald-50 data-[state=complete]:text-emerald-700 dark:data-[state=complete]:border-emerald-500/30 dark:data-[state=complete]:bg-emerald-500/10 dark:data-[state=complete]:text-emerald-300 data-[state=upcoming]:border-gray-200 data-[state=upcoming]:bg-white data-[state=upcoming]:text-gray-600 dark:data-[state=upcoming]:border-gray-800 dark:data-[state=upcoming]:bg-gray-900 dark:data-[state=upcoming]:text-gray-400"
                            >
                                <span class="text-[10px] font-bold tabular-nums opacity-60">{{ $index + 1 }}</span>
                                <span class="max-w-32 truncate">{{ $sectionTitle }}</span>
                            </button>
                        @endfor
                    </div>
                </div>

                <p
                    data-draft-autosave-status
                    hidden
                    role="status"
                    aria-live="polite"
                    class="px-1 text-xs font-medium text-gray-500 dark:text-gray-400"
                ></p>

                @foreach ($steps as $stepIndex => $step)
                    <section
                        data-form-step="{{ $stepIndex }}"
                        @if ($stepIndex !== $initialStep) hidden @endif
                        class="scroll-mt-48 rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="flex items-start gap-3 border-b border-gray-100 px-4 py-4 dark:border-gray-800 sm:px-5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-sm font-semibold tabular-nums text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $stepIndex + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    Etapa {{ $stepIndex + 1 }} de {{ $sectionCount }}
                                </p>
                                <h2 data-form-step-heading class="mt-0.5 text-base font-semibold leading-snug text-gray-900 dark:text-gray-100">
                                    {{ ($step['title'] ?? null) ?: 'Seção '.($stepIndex + 1) }}
                                </h2>
                            </div>
                        </div>

                        <div class="space-y-6 px-4 py-5 sm:px-5">
                            @forelse ($step['fields'] ?? [] as $field)
                                @include('public.forms.partials.field', ['field' => $field])
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma pergunta nesta seção.</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach

                <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200/80 bg-white/95 pt-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] shadow-[0_-4px_16px_-8px_rgba(0,0,0,0.15)] backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/95">
                    <div class="mx-auto flex w-full max-w-2xl items-center gap-2 px-4 sm:px-6">
                        <button
                            type="button"
                            data-form-step-prev
                            @if ($initialStep === 0) hidden @endif
                            class="inline-flex min-h-12 flex-1 items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 dark:active:bg-gray-700 dark:focus-visible:ring-offset-gray-900"
                        >
                            <x-icon name="chevron-left" class="h-4 w-4" />
                            Anterior
                        </button>

                        <button
                            type="button"
                            data-form-step-next
                            @if ($initialStep >= $sectionCount - 1) hidden @endif
                            class="inline-flex min-h-12 flex-1 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:active:bg-indigo-600 dark:focus-visible:ring-offset-gray-900"
                        >
                            Próximo
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </button>

                        <button
                            type="submit"
                            data-form-step-submit
                            @if ($initialStep < $sectionCount - 1) hidden @endif
                            class="inline-flex min-h-12 flex-1 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:active:bg-indigo-600 dark:focus-visible:ring-offset-gray-900"
                        >
                            <x-icon name="paper-airplane" class="h-4 w-4" />
                            {{ $response ? 'Salvar' : 'Enviar respostas' }}
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <div
        data-image-modal
        class="fixed inset-0 z-[100] hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="image-preview-title"
    >
        <div class="absolute inset-0 bg-gray-950/90 backdrop-blur-sm" data-image-modal-close></div>

        <div class="pointer-events-none absolute inset-0 flex flex-col">
            <div class="pointer-events-auto flex items-start justify-between gap-4 p-4 pt-[calc(env(safe-area-inset-top)+1rem)] sm:p-6">
                <div class="min-w-0 text-white">
                    <h2 id="image-preview-title" data-image-modal-title class="truncate text-sm font-semibold"></h2>
                    <p data-image-modal-counter class="mt-0.5 text-xs text-gray-300"></p>
                </div>

                <button
                    type="button"
                    data-image-modal-close
                    aria-label="Fechar pré-visualização"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                >
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="relative flex min-h-0 flex-1 items-center justify-center px-4 pb-4 sm:px-16">
                <button
                    type="button"
                    data-image-modal-prev
                    aria-label="Imagem anterior"
                    hidden
                    class="pointer-events-auto absolute left-2 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60 sm:left-4"
                >
                    <x-icon name="chevron-left" class="h-6 w-6" />
                </button>

                <img
                    data-image-modal-image
                    alt=""
                    class="pointer-events-auto max-h-full max-w-full rounded-lg object-contain shadow-2xl"
                >

                <button
                    type="button"
                    data-image-modal-next
                    aria-label="Próxima imagem"
                    hidden
                    class="pointer-events-auto absolute right-2 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60 sm:right-4"
                >
                    <x-icon name="chevron-right" class="h-6 w-6" />
                </button>
            </div>

            <div class="pointer-events-auto flex justify-center p-4 pb-[calc(env(safe-area-inset-bottom)+1rem)] sm:p-6">
                <a
                    data-image-modal-original
                    href="#"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white/10 px-4 text-sm font-medium text-white transition hover:bg-white/20"
                >
                    Abrir original
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
