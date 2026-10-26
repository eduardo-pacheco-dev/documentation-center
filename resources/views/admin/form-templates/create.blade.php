@php
    $structureError = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'steps'));
    $initialSteps = old('steps') ?? [];

    $panelClass = 'rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
    $cardHeader = 'border-b border-gray-200 px-4 py-3 dark:border-gray-800';
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $primaryButton = 'inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white transition hover:bg-indigo-500';
    $secondaryButton = 'inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800';
@endphp

<x-layouts.admin title="Novo template">
    <nav class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
        <a href="{{ route('admin.form-templates.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Templates de formulário</a>
        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        <span class="text-gray-700 dark:text-gray-200">Novo template</span>
    </nav>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="plus" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Novo template
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monte um template do zero: defina os dados e a estrutura de seções e perguntas.</p>
        </div>

        <a href="{{ route('admin.form-templates.index') }}" class="{{ $secondaryButton }}">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-medium">Não foi possível criar o template. Corrija os itens destacados e tente novamente.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.form-templates.store') }}" class="mt-4">
        @csrf

        <div class="grid gap-4 lg:grid-cols-[20rem_minmax(0,1fr)] lg:items-start">
            <aside class="lg:sticky lg:top-4">
                <section class="{{ $panelClass }}">
                    <div class="{{ $cardHeader }}">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="pencil-square" class="h-4 w-4" />
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Dados do template</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Nome e tipo na biblioteca.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 p-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do template</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                placeholder="Ex.: Registro fotográfico de torre"
                                required
                                class="{{ $inputClass }}"
                            >
                            @error('name')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                            <select name="type" id="type" class="{{ $inputClass }}">
                                <option value="">Sem tipo</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">O tipo ajuda a filtrar o template na listagem.</p>
                        </div>
                    </div>
                </section>
            </aside>

            <div class="min-w-0">
                <section class="{{ $panelClass }}">
                    <div class="{{ $cardHeader }}">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                <x-icon name="squares-2x2" class="h-4 w-4" />
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Estrutura do template</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Adicione as seções e as perguntas que o template vai aplicar aos formulários.</p>
                            </div>
                        </div>
                    </div>

                    @if ($structureError)
                        <div class="mx-4 mt-4 flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                            <x-icon name="exclamation-circle" class="h-4 w-4 shrink-0" />
                            <p>Não foi possível salvar a estrutura. Corrija os campos destacados e tente novamente.</p>
                        </div>
                    @endif

                    <div class="p-4">
                        @include('admin.forms.partials.builder', ['steps' => $initialSteps])
                    </div>
                </section>
            </div>
        </div>

        <div class="sticky bottom-0 mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 py-3 dark:border-gray-800 dark:bg-gray-950">
            <p class="text-xs text-gray-500 dark:text-gray-400">Você pode ajustar dados e estrutura depois na página do template.</p>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.form-templates.index') }}" class="{{ $secondaryButton }}">
                    Cancelar
                </a>
                <button type="submit" class="{{ $primaryButton }}">
                    <x-icon name="plus" class="h-4 w-4" />
                    Criar template
                </button>
            </div>
        </div>
    </form>
</x-layouts.admin>
