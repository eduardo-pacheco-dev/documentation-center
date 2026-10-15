<div
    data-create-modal
    @if ($errors->any() && old('modal') === 'create') data-open-on-load="true" @endif
    class="fixed inset-0 z-50 hidden flex overflow-y-auto p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="create-colaborador-title"
>
    <div class="fixed inset-0 bg-gray-950/50" data-create-modal-close></div>

    <div class="relative m-auto w-full max-w-2xl rounded-2xl bg-white shadow-xl dark:bg-gray-900">
        <header class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 id="create-colaborador-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="user-plus" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Novo colaborador
                </h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Cadastre um membro da equipe técnica ou demais colaboradores.</p>
            </div>
            <button
                type="button"
                data-create-modal-close
                class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </header>

        <form method="POST" action="{{ route('admin.colaboradores.store') }}" class="px-5 py-4">
            @csrf
            <input type="hidden" name="modal" value="create">

            <div class="mb-5">
                <div class="flex items-center justify-between text-xs font-medium text-gray-500 dark:text-gray-400">
                    <span data-wizard-label>Dados pessoais</span>
                    <span data-wizard-counter>Passo 1 de 3</span>
                </div>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                    <div data-wizard-progress class="h-full w-1/3 rounded-full bg-indigo-500 transition-all duration-200"></div>
                </div>
            </div>

            @php($colaborador = null)

            @include('admin.colaboradores.partials.form', ['wizard' => true])

            <div class="mt-6 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    data-create-modal-close
                    class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Cancelar
                </button>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        data-wizard-prev
                        class="hidden rounded-xl border border-gray-200/70 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    >
                        Voltar
                    </button>

                    <button
                        type="button"
                        data-wizard-next
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        Próximo
                    </button>

                    <button
                        type="submit"
                        data-wizard-submit
                        class="hidden items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Criar colaborador
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
