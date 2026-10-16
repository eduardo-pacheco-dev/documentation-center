@php
    $structureError = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'steps'));
    $initialSteps = old('steps') ?? [];

    $cardHeader = 'border-b border-gray-100 px-5 py-4 dark:border-gray-800';
    $inputClass = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $secondaryButton = 'rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
@endphp

<x-layouts.admin title="Novo template">
    <nav class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
        <a href="{{ route('admin.form-templates.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Templates de formulário</a>
        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        <span class="text-gray-700 dark:text-gray-200">Novo template</span>
    </nav>

    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Biblioteca</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="plus" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
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
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-medium">Não foi possível criar o template. Corrija os itens destacados e tente novamente.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.form-templates.store') }}" class="mt-6">
        @csrf

        <section class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="{{ $cardHeader }}">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="pencil-square" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Dados do template</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nome e tipo que identificam o template na biblioteca.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
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

        <section class="mt-6 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="{{ $cardHeader }}">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <x-icon name="squares-2x2" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Estrutura do template</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Adicione as seções e as perguntas que o template vai aplicar aos formulários.</p>
                    </div>
                </div>
            </div>

            @if ($structureError)
                <div class="mx-5 mt-5 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    <x-icon name="exclamation-circle" class="h-4 w-4 shrink-0" />
                    <p>Não foi possível salvar a estrutura. Corrija os campos destacados e tente novamente.</p>
                </div>
            @endif

            <div class="p-5">
                @include('admin.forms.partials.builder', ['steps' => $initialSteps])
            </div>
        </section>

        <div class="sticky bottom-0 mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/95 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/95">
            <p class="text-xs text-gray-500 dark:text-gray-400">Você pode ajustar dados e estrutura depois na página do template.</p>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.form-templates.index') }}" class="{{ $secondaryButton }}">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Criar template
                </button>
            </div>
        </div>
    </form>
</x-layouts.admin>
