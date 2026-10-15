<x-layouts.admin title="Novo colaborador">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a
                href="{{ route('admin.colaboradores.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 transition hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
            >
                <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                Colaboradores
            </a>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="user-plus" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Novo colaborador
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre um membro da equipe técnica ou demais colaboradores.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.colaboradores.store') }}" class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf

        @include('admin.colaboradores.partials.form')

        <div class="mt-6 flex items-center gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="check" class="h-4 w-4" />
                Criar colaborador
            </button>

            <a
                href="{{ route('admin.colaboradores.index') }}"
                class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>
</x-layouts.admin>
