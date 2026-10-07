<x-layouts.admin title="Arquivos">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Arquivos</h1>
            <p class="mt-1 text-sm text-gray-500">Envie arquivos e gere links de download a partir deles.</p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('admin.files.store') }}"
        enctype="multipart/form-data"
        class="mt-6 max-w-2xl space-y-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
    >
        @csrf

        <label for="documents" class="block text-sm font-medium text-gray-700">Enviar arquivos</label>
        <input
            id="documents"
            type="file"
            name="documents[]"
            multiple
            required
            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200"
        >
        @error('documents')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
        >
            Enviar arquivos
        </button>
    </form>

    @if ($documents->isNotEmpty())
        <form method="POST" action="{{ route('admin.files.generate-link') }}" class="mt-6">
            @csrf

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="w-10 px-4 py-3">
                                <input
                                    type="checkbox"
                                    onclick="document.querySelectorAll('.select-file').forEach((el) => { el.checked = this.checked; })"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                >
                            </th>
                            <th class="px-4 py-3 font-medium">Arquivo</th>
                            <th class="px-4 py-3 font-medium">Tamanho</th>
                            <th class="px-4 py-3 font-medium">Em links</th>
                            <th class="px-4 py-3 font-medium">Enviado em</th>
                            <th class="px-4 py-3 font-medium text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($documents as $document)
                            <tr>
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="document_ids[]"
                                        value="{{ $document->getKey() }}"
                                        class="select-file rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <p class="max-w-[18rem] truncate text-gray-900">{{ $document->original_name }}</p>
                                    @if ($document->uploaded_via_short_link_id !== null)
                                        <p class="text-xs text-gray-400">Recebido via link de upload</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ Number::fileSize($document->size) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $document->short_links_count }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $document->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        form="delete-document-{{ $document->getKey() }}"
                                        class="text-sm font-medium text-red-600 hover:underline"
                                        onclick="if (! confirm('Excluir {{ $document->original_name }}?')) event.preventDefault()"
                                    >
                                        Excluir
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 px-4 py-4">
                    <input
                        type="text"
                        name="title"
                        placeholder="Título do link (opcional)"
                        class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                    >
                        Gerar link de download
                    </button>
                </div>
            </div>
        </form>

        @foreach ($documents as $document)
            <form
                id="delete-document-{{ $document->getKey() }}"
                method="POST"
                action="{{ route('admin.files.destroy', $document) }}"
                class="hidden"
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <div class="pt-4">
            {{ $documents->links() }}
        </div>
    @else
        <p class="mt-6 text-sm text-gray-500">Nenhum arquivo enviado ainda. Envie arquivos acima para começar.</p>
    @endif
</x-layouts.admin>