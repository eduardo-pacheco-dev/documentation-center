<x-layouts.admin title="Editar item">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Catálogo</p>
        <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
            <x-icon name="pencil-square" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
            Editar item
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $catalogItem->name }}</p>
    </div>

    <form method="POST" action="{{ route('admin.catalog.update', $catalogItem) }}" class="mt-6">
        @csrf
        @method('PUT')

        @include('admin.catalog.partials.form', ['catalogItem' => $catalogItem])

        <div class="mt-6 flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="check" class="h-4 w-4" />
                Salvar alterações
            </button>

            <a
                href="{{ route('admin.catalog.show', $catalogItem) }}"
                class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.catalog.destroy', $catalogItem) }}" class="mt-10 border-t border-gray-200 pt-6 dark:border-gray-800" data-confirm-delete>
        @csrf
        @method('DELETE')

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Excluir item</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">O item do catálogo será movido para a lixeira.</p>
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir item
            </button>
        </div>
    </form>
</x-layouts.admin>
