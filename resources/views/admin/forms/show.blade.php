@php
    $steps = $steps ?? [];

    $answeredFields = collect($steps)->flatMap(fn ($step) => collect($step['fields'] ?? [])->filter(
        fn ($field) => filled($field['value'] ?? null) || ! empty($field['images'] ?? []) || ! empty($field['files'] ?? []),
    ));
    $hasResponses = $answeredFields->isNotEmpty();
    $answeredCount = $answeredFields->count();

    $fileName = fn (array $field, string $file, int $index) => $field['fileNames'][$index]
        ?? basename(parse_url($file, PHP_URL_PATH) ?: $file);

    $typeLabels = [
        'text' => 'Texto curto',
        'textarea' => 'Parágrafo',
        'number' => 'Número',
        'date' => 'Data',
        'time' => 'Hora',
        'radio' => 'Múltipla escolha',
        'checkbox' => 'Caixas de seleção',
        'dropdown' => 'Lista suspensa',
        'scale' => 'Escala linear',
        'image' => 'Imagem / foto',
        'file' => 'Arquivo',
    ];
@endphp

<x-layouts.admin :title="$form->code">
    <div class="border-b border-gray-200 pb-6 dark:border-gray-800">
        <nav class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
            <a href="{{ route('admin.forms.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Formulários</a>
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            <span class="text-gray-700 dark:text-gray-200">{{ $form->code }}</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm sm:inline-flex dark:bg-indigo-500">
                    <x-icon name="clipboard-document-list" class="h-6 w-6" />
                </span>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $form->code }}</h1>
                        <x-form-status :status="$form->status" />
                    </div>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $form->name }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm">
                        <span class="inline-flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                            <x-icon name="document-text" class="h-4 w-4" />
                            {{ $form->type }}
                        </span>

                        @if ($form->project)
                            <a href="{{ route('admin.projects.show', $form->project) }}" class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                <x-icon name="briefcase" class="h-4 w-4" />
                                [{{ $form->project->code }}] {{ $form->project->name }}
                            </a>
                        @endif

                        <span class="inline-flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                            <x-icon name="clock" class="h-4 w-4" />
                            Atualizado <x-relative-time :value="$form->updated_at" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('admin.forms.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                >
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Voltar
                </a>

                <a
                    href="{{ route('admin.forms.evaluate', $form) }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <x-icon name="document-check" class="h-4 w-4" />
                    Avaliar resposta
                </a>

                <a
                    href="{{ route('admin.forms.edit', $form) }}"
                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    Editar
                </a>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div class="flex items-center gap-2">
            <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                <x-icon name="document-check" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Respostas do aplicativo
            </h2>
            @if ($hasResponses)
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $answeredCount }} {{ $answeredCount === 1 ? 'resposta' : 'respostas' }}
                </span>
            @endif
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Valores, status e fotos preenchidos pelos técnicos. Essas informações são somente leitura.
        </p>

        @if ($hasResponses)
            <div class="mt-4 space-y-4">
                @foreach ($steps as $step)
                    @php($answered = collect($step['fields'] ?? [])->filter(
                        fn ($field) => filled($field['value'] ?? null) || ! empty($field['images'] ?? []) || ! empty($field['files'] ?? []),
                    ))

                    @if ($answered->isNotEmpty())
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <x-icon name="rectangle-stack" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $step['title'] ?: 'Seção' }}
                                    </h3>
                                </div>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $answered->count() }} {{ $answered->count() === 1 ? 'resposta' : 'respostas' }}
                                </span>
                            </div>

                            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($answered as $field)
                                    <div class="px-5 py-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ ($field['label'] ?? null) ?: ($field['id'] ?? 'Campo') }}
                                                </p>
                                                @if (!empty($field['id']))
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $field['id'] }} &middot; {{ $typeLabels[$field['type'] ?? null] ?? ($field['type'] ?? 'text') }}
                                                    </p>
                                                @endif

                                                <div class="mt-2">
                                                    @if (($field['type'] ?? null) === 'image')
                                                        <div class="flex flex-wrap gap-2">
                                                            @forelse (($field['images'] ?? []) as $image)
                                                                <a
                                                                    href="{{ $image }}"
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                    class="group relative block overflow-hidden rounded-lg ring-1 ring-gray-200 dark:ring-gray-800"
                                                                >
                                                                    <img
                                                                        src="{{ $image }}"
                                                                        alt="{{ ($field['label'] ?? null) ?: 'Foto' }}"
                                                                        loading="lazy"
                                                                        class="h-16 w-24 object-cover transition duration-150 group-hover:opacity-80"
                                                                    >
                                                                    <span class="absolute inset-0 flex items-center justify-center bg-gray-900/0 text-white opacity-0 transition duration-150 group-hover:bg-gray-900/30 group-hover:opacity-100">
                                                                        <x-icon name="eye" class="h-5 w-5" />
                                                                    </span>
                                                                </a>
                                                            @empty
                                                                <span class="text-sm text-gray-500 dark:text-gray-400">Nenhuma foto.</span>
                                                            @endforelse
                                                        </div>
                                                    @elseif (($field['type'] ?? null) === 'file')
                                                        <div class="flex flex-col gap-2">
                                                            @forelse (($field['files'] ?? []) as $index => $file)
                                                                @php($name = $fileName($field, $file, $index))
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
                                                            @empty
                                                                <span class="text-sm text-gray-500 dark:text-gray-400">Nenhum arquivo.</span>
                                                            @endforelse
                                                        </div>
                                                    @else
                                                        <div class="rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:bg-gray-800/60 dark:text-gray-300">
                                                            <span class="whitespace-pre-wrap">{{ ($field['value'] ?? null) ?: '—' }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="shrink-0">
                                                <x-form-field-status :status="$field['status'] ?? null" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="mt-4 flex items-center gap-2 rounded-xl border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <x-icon name="document-check" class="h-4 w-4" />
                Nenhuma resposta preenchida pelo aplicativo até o momento.
            </div>
        @endif
    </div>
</x-layouts.admin>