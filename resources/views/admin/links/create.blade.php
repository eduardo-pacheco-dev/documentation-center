<x-layouts.admin title="Novo link">
    <h1 class="text-xl font-semibold">Novo link</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie um link curto para enviar ou baixar documentos.</p>

    <form
        method="POST"
        action="{{ route('admin.links.store') }}"
        class="mt-6 max-w-2xl space-y-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm"
    >
        @csrf

        @include('admin.links.partials.form-fields')

        <button
            type="submit"
            class="w-full rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            Criar link
        </button>
    </form>
</x-layouts.admin>