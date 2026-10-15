<x-layouts.admin title="Nova ordem de serviço">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Operações</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="clipboard-document-list" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Nova ordem de serviço
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Abra uma OS para um cliente e monte a lista de itens.</p>
        </div>

        <a
            href="{{ route('admin.work-orders.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
        >
            <x-icon name="chevron-left" class="h-4 w-4" />
            Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('admin.work-orders.store') }}" class="mt-6">
        @csrf

        <div class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @include('admin.work-orders.partials.form')
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="check" class="h-4 w-4" />
                Criar ordem de serviço
            </button>

            <a
                href="{{ route('admin.work-orders.index') }}"
                class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>
</x-layouts.admin>
