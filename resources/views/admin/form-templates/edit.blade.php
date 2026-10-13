<x-layouts.admin title="Editar template">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="document-duplicate" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Editar template
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $template->name }}
                @if ($template->user)
                    &middot; criado por {{ $template->user->name }}
                @endif
            </p>
        </div>

        <a
            href="{{ route('admin.form-templates.index') }}"
            class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
        >
            Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-medium">Não foi possível salvar o template. Corrija os itens destacados e tente novamente.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.form-templates.update', $template) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do template</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $template->name) }}"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                <select
                    name="type"
                    id="type"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    <option value="">Sem tipo</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(old('type', $template->type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="squares-2x2" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Estrutura
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Seções e perguntas que serão copiadas ao usar este template.</p>
            </div>

            <div class="px-5 py-5">
                @include('admin.forms.partials.builder', ['steps' => $template->steps ?? []])
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a
                href="{{ route('admin.form-templates.index') }}"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="check" class="h-4 w-4" />
                Salvar template
            </button>
        </div>
    </form>

    @can('delete', $template)
        <form method="POST" action="{{ route('admin.form-templates.destroy', $template) }}" data-confirm="Excluir template {{ $template->name }}?" class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir template
            </button>
        </form>
    @endcan
</x-layouts.admin>
