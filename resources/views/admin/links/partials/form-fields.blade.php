@php($shortLink = $shortLink ?? null)

<div x-data="{ type: '{{ old('type', $shortLink?->type->value ?? 'upload') }}' }" class="space-y-4">
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Título</label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $shortLink?->title) }}"
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
            x-model="type"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="upload" @selected(old('type', $shortLink?->type->value ?? 'upload') === 'upload')>Upload — terceiros enviam documentos</option>
            <option value="download" @selected(old('type', $shortLink?->type->value ?? 'upload') === 'download')>Download — visitantes baixam documentos</option>
            <option value="redirect" @selected(old('type', $shortLink?->type->value ?? 'upload') === 'redirect')>Redirect — redireciona para um endereço</option>
        </select>
        @error('type')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div
        x-show="type === 'redirect'"
        @if (old('type', $shortLink?->type->value ?? 'upload') !== 'redirect') style="display: none" @endif
    >
        <label for="redirect_url" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Endereço de redirecionamento</label>
        <input
            id="redirect_url"
            name="redirect_url"
            type="url"
            value="{{ old('redirect_url', $shortLink?->redirect_url) }}"
            placeholder="https://exemplo.com"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">O visitante será redirecionado para este endereço ao abrir o link.</p>
        @error('redirect_url')
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
        >{{ old('description', $shortLink?->description) }}</textarea>
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
                value="{{ old('expires_at', $shortLink?->expires_at?->format('Y-m-d\TH:i')) }}"
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
                value="{{ old('max_uses', $shortLink?->max_uses) }}"
                placeholder="Sem limite"
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            @error('max_uses')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            Senha de acesso
            @if ($shortLink?->needsPassword())
                <span class="text-xs font-normal text-gray-400 dark:text-gray-500">(definida — deixe vazio para remover)</span>
            @endif
        </label>
        <input
            id="password"
            name="password"
            type="password"
            placeholder="{{ $shortLink?->needsPassword() ? 'Digite uma nova senha ou limpe para remover' : 'Deixe vazio para liberar sem senha' }}"
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
                @checked(old('is_active', $shortLink?->is_active ?? true))
                class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 dark:text-indigo-300 focus:ring-indigo-500"
            >
            Link ativo
        </label>
    </div>
</div>
