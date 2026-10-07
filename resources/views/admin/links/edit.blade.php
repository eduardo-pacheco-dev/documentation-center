<x-layouts.admin title="Editar link">
    <h1 class="text-xl font-semibold">Editar link</h1>
    <p class="mt-1 text-sm text-gray-500">Ajuste as configurações e os documentos do link.</p>

    <div class="mt-6 max-w-2xl rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold">Link público</h2>
        <div class="mt-3 flex items-center gap-2">
            <code class="flex-1 truncate rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                {{ $shortLink->url }}
            </code>
            <button
                type="button"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                onclick="navigator.clipboard.writeText('{{ $shortLink->url }}').then(() => this.textContent='Copiado').then(() => setTimeout(() => this.textContent='Copiar', 2000))"
            >
                Copiar
            </button>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('admin.links.update', $shortLink) }}"
        class="mt-6 max-w-2xl space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
    >
        @csrf
        @method('PUT')

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
            <input
                id="title"
                name="title"
                type="text"
                value="{{ old('title', $shortLink->title) }}"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Tipo de link</label>
            <select
                id="type"
                name="type"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="upload" @selected(old('type', $shortLink->type->value) === 'upload')>Upload — terceiros enviam documentos</option>
                <option value="download" @selected(old('type', $shortLink->type->value) === 'download')>Download — visitantes baixam documentos</option>
            </select>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >{{ old('description', $shortLink->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-700">Expira em</label>
                <input
                    id="expires_at"
                    name="expires_at"
                    type="datetime-local"
                    value="{{ old('expires_at', $shortLink->expires_at?->format('Y-m-d\TH:i')) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('expires_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="max_uses" class="block text-sm font-medium text-gray-700">Limite de acessos</label>
                <input
                    id="max_uses"
                    name="max_uses"
                    type="number"
                    min="1"
                    value="{{ old('max_uses', $shortLink->max_uses) }}"
                    placeholder="Sem limite"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('max_uses')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Senha de acesso
                @if ($shortLink->needsPassword())
                    <span class="text-xs font-normal text-gray-400">(definida — deixe vazio para remover)</span>
                @endif
            </label>
            <input
                id="password"
                name="password"
                type="password"
                placeholder="{{ $shortLink->needsPassword() ? 'Digite uma nova senha ou limpe para remover' : 'Deixe vazio para liberar sem senha' }}"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked($shortLink->is_active)
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
                Link ativo
            </label>
        </div>

        <button
            type="submit"
            class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
        >
            Salvar alterações
        </button>
    </form>

    <section class="mt-6 max-w-2xl rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold">
            {{ $shortLink->type->value === 'download' ? 'Documentos no link' : 'Documentos recebidos' }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ $shortLink->type->value === 'download'
                ? 'Faça upload dos arquivos que devem ficar disponíveis para download neste link.'
                : 'Faça upload de documentos e eles serão exibidos nesta página pública.' }}
        </p>

        <form method="POST" action="{{ route('admin.links.documents.store', $shortLink) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            <input
                type="file"
                name="documents[]"
                multiple
                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200"
            >
            @error('documents')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <button
                type="submit"
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
                {{ $shortLink->type->value === 'download' ? 'Adicionar arquivos ao link' : 'Adicionar documento' }}
            </button>
        </form>

        @if ($documents->isNotEmpty())
            <table class="mt-5 min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-2 font-medium">Arquivo</th>
                        <th class="px-4 py-2 font-medium">Tamanho</th>
                        <th class="px-4 py-2 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($documents as $document)
                        <tr>
                            <td class="px-4 py-2">
                                <a href="{{ route('public.short-links.documents.download', $document) }}" class="text-indigo-600 hover:underline">
                                    {{ $document->original_name }}
                                </a>
                            </td>
                            <td class="px-4 py-2 text-gray-500">{{ Number::fileSize($document->size) }}</td>
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.links.documents.destroy', [$shortLink, $document]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-sm font-medium text-red-600 hover:underline"
                                        onclick="return confirm('{{ $shortLink->type->value === 'download' ? 'Remover' : 'Excluir' }} {{ $document->original_name }}{{ $shortLink->type->value === 'download' ? ' deste link?' : '?' }}')"
                                    >
                                        {{ $shortLink->type->value === 'download' ? 'Remover do link' : 'Excluir' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="mt-4 text-sm text-gray-500">
                {{ $shortLink->type->value === 'download'
                    ? 'Nenhum documento anexado a este link ainda. Selecione arquivos no painel Arquivos e gere um link, ou envie arquivos acima.'
                    : 'Nenhum documento recebido por este link ainda.' }}
            </p>
        @endif
    </section>
</x-layouts.admin>