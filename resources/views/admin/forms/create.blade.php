<x-layouts.admin title="Novo formulário">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Novo formulário
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie o modelo e preencha as etapas e campos padrão.</p>
        </div>

        <a
            href="{{ route('admin.forms.index') }}"
            class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
        >
            Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('admin.forms.store') }}" class="mt-6 space-y-6" x-data="stepsEditor({{ json_encode([]) }})">
        @csrf

        @include('admin.forms.partials.form', [
            'form' => null,
            'projects' => $projects,
            'types' => $types,
            'statuses' => $statuses,
        ])

        <div class="flex justify-end gap-2">
            <a
                href="{{ route('admin.forms.index') }}"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="check" class="h-4 w-4" />
                Criar formulário
            </button>
        </div>
    </form>
</x-layouts.admin>