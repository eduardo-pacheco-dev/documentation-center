<x-layouts.admin title="Editar projeto">
    <div>
        <h1 class="flex items-center gap-2 text-xl font-semibold">
            <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Editar projeto
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $project->name }}</p>
    </div>

    <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="mt-6">
        @csrf
        @method('PUT')

        @include('admin.projects.partials.form', ['project' => $project])

        <div class="mt-6 flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="check" class="h-4 w-4" />
                Salvar alterações
            </button>

            <a
                href="{{ route('admin.projects.show', $project) }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>

    @if ($project->user_id === auth()->id())
        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" class="mt-10 border-t border-gray-200 pt-6 dark:border-gray-800" data-confirm-delete>
            @csrf
            @method('DELETE')

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Excluir projeto</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">As tarefas, dependências, recursos e linhas de base também serão excluídos.</p>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950"
                >
                    <x-icon name="trash" class="h-4 w-4" />
                    Excluir projeto
                </button>
            </div>
        </form>
    @endif
</x-layouts.admin>
