<x-layouts.admin title="Editar cliente">
    <div>
        <h1 class="flex items-center gap-2 text-xl font-semibold">
            <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Editar cliente
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $client->name }}</p>
    </div>

    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="mt-6">
        @csrf
        @method('PUT')

        @include('admin.clients.partials.form', ['client' => $client])

        <div class="mt-6 flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="check" class="h-4 w-4" />
                Salvar alterações
            </button>

            <a
                href="{{ route('admin.clients.show', $client) }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" class="mt-10 border-t border-gray-200 pt-6 dark:border-gray-800" data-confirm-delete>
        @csrf
        @method('DELETE')

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Excluir cliente</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">O cadastro do cliente será movido para a lixeira.</p>
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir cliente
            </button>
        </div>
    </form>
</x-layouts.admin>
