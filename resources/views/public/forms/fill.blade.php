<x-layouts.public :title="'Preencher '.$form->code">
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
    @endphp

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <span class="inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
            Formulário externo
        </span>

        <h1 class="mt-3 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $form->code }} &middot; {{ $form->name }}</h1>

        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
            <span>{{ $form->type }}</span>
            @if ($form->project)
                <span>[{{ $form->project->code }}] {{ $form->project->name }}</span>
            @endif
        </div>

        @if ($totalQuestions > 0)
            <div
                class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/40"
                data-form-progress
                role="progressbar"
                aria-label="Progresso do envio"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $initialPercent }}"
            >
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-medium text-gray-600 dark:text-gray-300">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="chart-bar" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                        Progresso do envio
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                        <span data-progress-count>{{ $answeredQuestions }} de {{ $totalQuestions }}</span>
                        &middot;
                        <span data-progress-percent class="font-semibold text-gray-700 dark:text-gray-200">{{ $initialPercent }}%</span>
                    </span>
                </div>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div
                        data-progress-bar
                        class="h-full rounded-full bg-indigo-600 transition-all duration-300 dark:bg-indigo-500"
                        style="width: {{ $initialPercent }}%"
                    ></div>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="mt-4 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-300">
                <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Não foi possível enviar as respostas. Corrija os itens destacados e tente novamente.</p>
            </div>
        @endif

        @if ($response)
            <div class="mt-4 flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-300">
                <x-icon name="arrow-path" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Este formulário aceita uma única resposta. Ao enviar novamente, o registro existente será atualizado.</p>
            </div>
        @endif

        @if (empty($steps))
            <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                Este formulário ainda não possui perguntas.
            </p>
        @else
            <form
                method="POST"
                action="{{ URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]) }}"
                enctype="multipart/form-data"
                class="mt-6 space-y-5"
                data-form-progress-form
            >
                @csrf

                @foreach ($steps as $stepIndex => $step)
                    <section class="rounded-lg border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ ($step['title'] ?? null) ?: 'Seção '.($stepIndex + 1) }}
                            </h2>
                        </div>

                        <div class="space-y-5 px-4 py-4">
                            @forelse ($step['fields'] ?? [] as $field)
                                @include('public.forms.partials.field', ['field' => $field])
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma pergunta nesta seção.</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    <x-icon name="paper-airplane" class="h-4 w-4" />
                    {{ $response ? 'Atualizar respostas' : 'Enviar respostas' }}
                </button>
            </form>
        @endif
    </div>
</x-layouts.public>
