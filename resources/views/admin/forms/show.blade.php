<x-layouts.admin :title="$form->code">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $form->code }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $form->name }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-form-status :status="$form->status" />
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $form->type }}</span>
                @if ($form->project)
                    <a href="{{ route('admin.projects.show', $form->project) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        [{{ $form->project->code }}] {{ $form->project->name }}
                    </a>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.forms.evaluate', $form) }}"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
            >
                <x-icon name="document-check" class="h-4 w-4" />
                Avaliar resposta
            </a>

            <button
                type="button"
                data-modal-open="save-template-modal"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
            >
                <x-icon name="document-duplicate" class="h-4 w-4" />
                Salvar como template
            </button>

            <a
                href="{{ route('admin.forms.edit', $form) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

            <a
                href="{{ route('admin.forms.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    @php
        $hasResponses = collect($steps)->contains(fn ($step) => collect($step['fields'] ?? [])->contains(
            fn ($field) => filled($field['value'] ?? null) || ! empty($field['images'] ?? []),
        ));
    @endphp

    <div class="mt-6">
        <div class="mb-3">
            <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                <x-icon name="squares-2x2" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Estrutura do formulário
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Monte as seções e perguntas. Essas informações definem o que o aplicativo exibe e são separadas das respostas.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.forms.steps.update', $form) }}">
            @csrf
            @method('PATCH')

            @include('admin.forms.partials.builder', ['steps' => $steps])

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

    @if ($hasResponses)
        <div class="mt-8">
            <div class="mb-3">
                <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="document-check" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Respostas do aplicativo
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Valores, status e fotos preenchidos pelos técnicos. Somente leitura.</p>
            </div>

            <div class="space-y-4">
                @foreach ($steps as $step)
                    @php($answeredFields = collect($step['fields'] ?? [])->filter(
                        fn ($field) => filled($field['value'] ?? null) || ! empty($field['images'] ?? []),
                    ))

                    @if ($answeredFields->isNotEmpty())
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                                <x-icon name="rectangle-stack" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $step['title'] ?: 'Seção' }}
                                </h3>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                        <tr>
                                            <th class="px-5 py-3 font-medium">Campo</th>
                                            <th class="px-5 py-3 font-medium">Valor</th>
                                            <th class="px-5 py-3 text-right font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach ($answeredFields as $field)
                                            <tr>
                                                <td class="px-5 py-3">
                                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ ($field['label'] ?? null) ?: ($field['id'] ?? 'Campo') }}</p>
                                                    @if (!empty($field['id']))
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $field['id'] }} &middot; {{ $field['type'] ?? 'text' }}</p>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-3">
                                                    @if (($field['type'] ?? null) === 'image')
                                                        <span class="flex flex-wrap items-center gap-1">
                                                            @forelse (($field['images'] ?? []) as $image)
                                                                <a
                                                                    href="{{ $image }}"
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                    class="inline-flex items-center gap-0.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-indigo-400 dark:hover:bg-gray-700"
                                                                >
                                                                    <x-icon name="photo" class="h-3.5 w-3.5" />
                                                                    foto
                                                                </a>
                                                            @empty
                                                                <span class="text-gray-500 dark:text-gray-400">Nenhuma foto.</span>
                                                            @endforelse
                                                        </span>
                                                    @else
                                                        <span class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">{{ ($field['value'] ?? null) ?: '—' }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-3 text-right">
                                                    <x-form-field-status :status="$field['status'] ?? null" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $form->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $form->updated_at->format('d/m/Y H:i') }}
    </div>

    <div
        id="save-template-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="save-template-modal-title"
        @if ($errors->any() && old('modal') === 'template') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="save-template-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="document-duplicate" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Salvar como template
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        As seções e perguntas atuais serão copiadas. O template fica disponível para todos os usuários.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            @if ($errors->any() && old('modal') === 'template')
                <div
                    data-modal-error
                    class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">Não foi possível salvar o template. Corrija os itens destacados e tente novamente.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.form-templates.store') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="modal" value="template">
                <input type="hidden" name="form_id" value="{{ $form->id }}">

                <div>
                    <label for="template_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do template</label>
                    <input
                        type="text"
                        name="name"
                        id="template_name"
                        value="{{ old('name', $form->name) }}"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Salvar template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const openModal = (modal) => {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = (modal) => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            document.addEventListener('click', (event) => {
                const opener = event.target.closest('[data-modal-open]');

                if (opener) {
                    event.preventDefault();

                    const modal = document.getElementById(opener.dataset.modalOpen);
                    if (modal) {
                        openModal(modal);
                    }

                    return;
                }

                const closer = event.target.closest('[data-modal-close]');
                if (closer) {
                    closeModal(closer.closest('[role="dialog"]'));
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
                }
            });

            document.querySelectorAll('[role="dialog"][data-open]').forEach(openModal);
        })();
    </script>
</x-layouts.admin>
