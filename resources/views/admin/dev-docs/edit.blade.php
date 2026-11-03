<x-layouts.admin title="Editar documento">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Documentação Dev</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="pencil-square" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Editar documento
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $document->title }}</p>
        </div>

        <a href="{{ route('admin.dev-docs.show', $document) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <x-icon name="eye" class="h-4 w-4" />
            Visualizar
        </a>
    </div>

    <div class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <x-admin.dev-docs.form
            :action="route('admin.dev-docs.update', $document)"
            method="PUT"
            :document="$document"
            :categories="$categories"
            submit-label="Salvar alterações"
        />
    </div>
</x-layouts.admin>
