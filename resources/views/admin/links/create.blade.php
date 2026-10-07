<x-layouts.admin title="Novo link">
    <h1 class="text-xl font-semibold">Novo link</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie um link curto para enviar ou baixar documentos.</p>

    <form
        method="POST"
        action="{{ route('admin.links.store') }}"
        class="mt-6 max-w-2xl space-y-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm"
    >
        @csrf

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Título</label>
            <input
                id="title"
                name="title"
                type="text"
                value="{{ old('title') }}"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            @error('title')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tipo de link</label>
            <select
                id="type"
                name="type"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="upload" @selected(old('type') === 'upload')>Upload — terceiros enviam documentos</option>
                <option value="download" @selected(old('type') === 'download')>Download — visitantes baixam documentos</option>
            </select>
            @error('type')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descrição</label>
            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Expira em</label>
                <input
                    id="expires_at"
                    name="expires_at"
                    type="datetime-local"
                    value="{{ old('expires_at') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('expires_at')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="max_uses" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Limite de acessos</label>
                <input
                    id="max_uses"
                    name="max_uses"
                    type="number"
                    min="1"
                    value="{{ old('max_uses') }}"
                    placeholder="Sem limite"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('max_uses')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Senha de acesso</label>
            <input
                id="password"
                name="password"
                type="password"
                placeholder="Deixe vazio para liberar sem senha"
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    checked
                    class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 dark:text-indigo-300 focus:ring-indigo-500"
                >
                Link ativo
            </label>
        </div>

        <button
            type="submit"
            class="w-full rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            Criar link
        </button>
    </form>
</x-layouts.admin>