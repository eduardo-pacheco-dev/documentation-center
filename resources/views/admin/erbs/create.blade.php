<x-layouts.admin title="Nova ERB">
    <div>
        <h1 class="flex items-center gap-2 text-xl font-semibold">
            <x-icon name="signal" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Nova ERB
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre uma estação rádio base (torre de telecomunicações).</p>
    </div>

    <form method="POST" action="{{ route('admin.erbs.store') }}" class="mt-6">
        @csrf

        @include('admin.erbs.partials.form')

        <div class="mt-6 flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="check" class="h-4 w-4" />
                Criar ERB
            </button>

            <a
                href="{{ route('admin.erbs.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
        </div>
    </form>
</x-layouts.admin>
