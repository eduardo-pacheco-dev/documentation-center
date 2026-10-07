<x-layouts.admin title="Links">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Links</h1>
            <p class="mt-1 text-sm text-gray-500">Gerencie seus links de envio e download de documentos.</p>
        </div>

        <a
            href="{{ route('admin.links.create') }}"
            class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
        >
            Novo link
        </a>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Link</th>
                    <th class="px-5 py-3 font-medium">Título</th>
                    <th class="px-5 py-3 font-medium">Tipo</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Acessos</th>
                    <th class="px-5 py-3 font-medium">Documentos</th>
                    <th class="px-5 py-3 font-medium text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($shortLinks as $shortLink)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-mono text-gray-900">{{ $shortLink->code }}</p>
                            <p class="max-w-[16rem] truncate text-xs text-gray-500">{{ $shortLink->url }}</p>
                        </td>
                        <td class="px-5 py-3">{{ $shortLink->title }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $shortLink->type->value === 'upload' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $shortLink->type->value === 'upload' ? 'Upload' : 'Download' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $shortLink->is_active === false ? 'bg-gray-100 text-gray-600' : ($shortLink->isExpired() ? 'bg-red-100 text-red-700' : ($shortLink->hasReachedAccessLimit() ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700')) }}">
                                {{ $shortLink->is_active === false ? 'Desativado' : ($shortLink->isExpired() ? 'Expirado' : ($shortLink->hasReachedAccessLimit() ? 'Limite atingido' : 'Ativo')) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">
                            {{ $shortLink->used_count }} / {{ $shortLink->max_uses ?? '∞' }}
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $shortLink->documents_count + $shortLink->received_documents_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.links.edit', $shortLink) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                Editar
                            </a>

                            <form method="POST" action="{{ route('admin.links.destroy', $shortLink) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="ml-3 text-sm font-medium text-red-600 hover:underline"
                                    onclick="return confirm('Excluir o link {{ $shortLink->code }}?')"
                                >
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-gray-500" colspan="7">Nenhum link criado até o momento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $shortLinks->links() }}
        </div>
    </div>
</x-layouts.admin>