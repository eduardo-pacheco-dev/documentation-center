<x-layouts.admin :title="$equipment->name">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Equipamentos
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="wrench-screwdriver" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                {{ $equipment->name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $equipment->code ?? 'Sem código' }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-equipment-status :equipment="$equipment" />
                @if ($equipment->category)
                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $equipment->category }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.equipments.index') }}"
                class="inline-flex items-center gap-1.5 rounded-2xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="chevron-left" class="h-4 w-4" />
                Voltar
            </a>

            <button
                type="button"
                data-modal-open="equipment-edit-modal-{{ $equipment->getKey() }}"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </button>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="building-office" class="h-5 w-5" />
            </span>
            <p class="mt-4 truncate text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $equipment->manufacturer ?? '—' }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Fabricante</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="cube" class="h-5 w-5" />
            </span>
            <p class="mt-4 truncate text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $equipment->model ?? '—' }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modelo</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="adjustments-horizontal" class="h-5 w-5" />
            </span>
            <p class="mt-4 truncate text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $equipment->serial_number ?? '—' }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Número de série</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="squares-2x2" class="h-5 w-5" />
            </span>
            <p class="mt-4 truncate text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $equipment->category ?? '—' }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Categoria</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    <x-icon name="document-text" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Descrição</h2>
            </div>

            <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                {{ $equipment->description ?? 'Nenhuma descrição registrada.' }}
            </p>
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                    <x-icon name="clipboard-document" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observações</h2>
            </div>

            <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                {{ $equipment->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </section>
    </div>

    @include('admin.equipments.partials.documents', ['equipment' => $equipment])

    <div class="mt-4 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        <x-icon name="clock" class="h-3.5 w-3.5" />
        <span>Cadastrado em {{ $equipment->created_at->format('d/m/Y H:i') }}</span>
        <span class="text-gray-300 dark:text-gray-600">&middot;</span>
        <span>Atualizado em {{ $equipment->updated_at->format('d/m/Y H:i') }}</span>
    </div>

    @include('admin.equipments.partials.edit-modal', [
        'equipment' => $equipment,
        'statuses' => $statuses,
        'forceOpen' => $openEditModal ?? false,
    ])

    @include('admin.equipments.partials.modal-script')
</x-layouts.admin>
