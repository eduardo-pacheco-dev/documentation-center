<div
    data-create-modal
    @if ($errors->any()) data-open-on-load="true" @endif
    class="fixed inset-0 z-50 hidden flex overflow-y-auto p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="create-colaborador-title"
>
    <div class="fixed inset-0 bg-gray-950/50" data-create-modal-close></div>

    <div class="relative m-auto w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-gray-900">
        <header class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 id="create-colaborador-title" class="text-base font-semibold">Novo colaborador</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Cadastre um membro da equipe técnica ou demais colaboradores.</p>
            </div>
            <button
                type="button"
                data-create-modal-close
                class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </header>

        <form method="POST" action="{{ route('admin.colaboradores.store') }}" class="px-5 py-4">
            @csrf

            @php($colaborador = null)

            @include('admin.colaboradores.partials.form')

            <div class="mt-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    data-create-modal-close
                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                >
                    <x-icon name="check" class="h-4 w-4" />
                    Criar colaborador
                </button>
            </div>
        </form>
    </div>
</div>