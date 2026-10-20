<x-layouts.admin title="Novo equipamento">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Equipamentos</p>
        <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
            <x-icon name="wrench-screwdriver" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
            Novo equipamento
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre os dados do equipamento para consulta. O datasheet pode ser anexado depois.</p>
    </div>

    <form method="POST" action="{{ route('admin.equipments.store') }}" class="mt-6">
        @csrf

        @include('admin.equipments.partials.form', ['equipment' => null, 'statuses' => $statuses])

        <div class="mt-6 flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="check" class="h-4 w-4" />
                Cadastrar equipamento
            </button>

            <a
                href="{{ route('admin.equipments.index') }}"
                class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>
</x-layouts.admin>
