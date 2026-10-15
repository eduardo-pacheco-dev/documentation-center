<x-layouts.admin title="Editar colaborador">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a
                href="{{ route('admin.colaboradores.show', $colaborador) }}"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 transition hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
            >
                <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                {{ $colaborador->name }}
            </a>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="pencil-square" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Editar colaborador
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $colaborador->name }}{{ $colaborador->role ? ' — '.$colaborador->role : '' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.colaboradores.update', $colaborador) }}" class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @method('PUT')

        @include('admin.colaboradores.partials.form', ['colaborador' => $colaborador])

        <div class="mt-6 flex items-center gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="check" class="h-4 w-4" />
                Salvar alterações
            </button>

            <a
                href="{{ route('admin.colaboradores.show', $colaborador) }}"
                class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>

    <div class="mt-6 rounded-2xl border border-red-200/70 bg-red-50/50 p-6 dark:border-red-900/50 dark:bg-red-950/20">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="flex items-center gap-2 text-sm font-semibold text-red-800 dark:text-red-300">
                    <x-icon name="trash" class="h-4 w-4" />
                    Excluir colaborador
                </h2>
                <p class="mt-1 text-sm text-red-700/80 dark:text-red-300/80">O colaborador será movido para a lixeira.</p>
            </div>

            <form method="POST" action="{{ route('admin.colaboradores.destroy', $colaborador) }}" data-confirm="Excluir o colaborador {{ $colaborador->name }}?" data-confirm-title="Excluir colaborador" data-confirm-label="Excluir">
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-50 dark:border-red-900 dark:bg-gray-900 dark:text-red-400 dark:hover:bg-red-950/40"
                >
                    <x-icon name="trash" class="h-4 w-4" />
                    Excluir colaborador
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
