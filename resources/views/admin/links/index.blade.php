<x-layouts.admin title="Links">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="link" class="h-5 w-5 text-gray-400" />
                Links
            </h1>
            <p class="mt-1 text-sm text-gray-500">Gerencie seus links de envio e download de documentos.</p>
        </div>

        <a
            href="{{ route('admin.links.create') }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo link
        </a>
    </div>

    <form
        method="GET"
        action="{{ route('admin.links.index') }}"
        class="mt-6 flex flex-wrap items-center gap-2"
        data-search-form
    >
        @if (request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if (request('direction'))
            <input type="hidden" name="direction" value="{{ request('direction') }}">
        @endif
        @if (request('document'))
            <input type="hidden" name="document" value="{{ request('document') }}">
        @endif

        <div class="relative w-full max-w-sm">
            <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Buscar por título, código ou descrição..."
                class="block w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                data-search-input
            >
        </div>

        <button
            type="submit"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
        >
            Buscar
        </button>

        @if ($search !== '')
            <a
                href="{{ route('admin.links.index', array_filter(['document' => request('document')])) }}"
                class="text-sm text-gray-500 hover:text-gray-900"
            >
                Limpar
            </a>
        @endif

        @if ($document)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-3 py-1.5 text-xs font-medium text-indigo-700">
                <x-icon name="document-text" class="h-3.5 w-3.5" />
                <span class="max-w-[14rem] truncate">{{ $document->original_name }}</span>
                <a
                    href="{{ route('admin.links.index') }}"
                    class="rounded-full p-0.5 hover:bg-indigo-200"
                    aria-label="Remover filtro por arquivo"
                >
                    <x-icon name="x-mark" class="h-3.5 w-3.5" />
                </a>
            </span>
        @endif
    </form>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="rounded-t-xl bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <x-sort-header column="code" label="Link" icon="link" class="px-5 py-3" />
                    <x-sort-header column="title" label="Título" icon="document-text" class="px-5 py-3" />
                    <x-sort-header column="type" label="Tipo" icon="arrow-up-tray" class="px-5 py-3" />
                    <th class="px-5 py-3 font-medium">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Status
                        </span>
                    </th>
                    <x-sort-header column="used_count" label="Acessos" icon="eye" class="px-5 py-3" />
                    <x-sort-header column="documents" label="Documentos" icon="document" class="px-5 py-3" />
                    <th class="px-5 py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($shortLinks as $shortLink)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="flex items-center gap-1.5 font-mono text-gray-900">
                                <x-icon name="link" class="h-4 w-4 text-gray-400" />
                                {{ $shortLink->code }}
                            </p>
                            <p class="mt-0.5 max-w-[16rem] truncate text-xs text-gray-500">{{ $shortLink->url }}</p>
                        </td>
                        <td class="px-5 py-3">{{ $shortLink->title }}</td>
                        <td class="px-5 py-3">
                            @if ($shortLink->type->value === 'upload')
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    <x-icon name="arrow-up-tray" class="h-3.5 w-3.5" />
                                    Upload
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                    <x-icon name="arrow-down-tray" class="h-3.5 w-3.5" />
                                    Download
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($shortLink->is_active === false)
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                    <x-icon name="x-circle" class="h-3.5 w-3.5" />
                                    Desativado
                                </span>
                            @elseif ($shortLink->isExpired())
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                    <x-icon name="clock" class="h-3.5 w-3.5" />
                                    Expirado
                                </span>
                            @elseif ($shortLink->hasReachedAccessLimit())
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                    <x-icon name="exclamation-triangle" class="h-3.5 w-3.5" />
                                    Limite atingido
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                    Ativo
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500">
                            {{ $shortLink->used_count }} / {{ $shortLink->max_uses ?? '∞' }}
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $shortLink->documents_count + $shortLink->received_documents_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="relative inline-block text-left">
                                <button
                                    type="button"
                                    class="rounded-full p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    data-dropdown-toggle
                                >
                                    <span class="sr-only">Abrir menu de ações</span>
                                    <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
                                </button>

                                <div
                                    class="absolute right-0 z-10 mt-1 hidden w-36 origin-top-right rounded-md bg-white py-1 text-left shadow-lg ring-1 ring-gray-900/5"
                                    role="menu"
                                    data-dropdown-menu
                                >
                                    <a
                                        href="{{ route('admin.links.edit', $shortLink) }}"
                                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                        role="menuitem"
                                    >
                                        <x-icon name="pencil-square" class="h-4 w-4 text-gray-400" />
                                        Editar
                                    </a>

                                    <form method="POST" action="{{ route('admin.links.destroy', $shortLink) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50"
                                            role="menuitem"
                                            onclick="return confirm('Excluir o link {{ $shortLink->code }}?')"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-gray-500" colspan="7">
                            <span class="inline-flex items-center gap-2">
                                <x-icon name="link" class="h-4 w-4 text-gray-400" />
                                Nenhum link criado até o momento.
                            </span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $shortLinks->links() }}
        </div>
    </div>

    <script>
        const closeDropdowns = (except) => {
            document.querySelectorAll('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
                if (menu !== except) {
                    menu.classList.add('hidden');
                    menu.previousElementSibling.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-dropdown-toggle]');

            if (toggle) {
                const menu = toggle.nextElementSibling;
                const willOpen = menu.classList.contains('hidden');
                closeDropdowns(willOpen ? menu : null);
                menu.classList.toggle('hidden', !willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));
                return;
            }

            if (!event.target.closest('[data-dropdown-menu]')) {
                closeDropdowns();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdowns();
            }
        });

        const searchForm = document.querySelector('[data-search-form]');
        const searchInput = document.querySelector('[data-search-input]');

        if (searchForm && searchInput) {
            let searchTimer;

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => searchForm.submit(), 400);
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    clearTimeout(searchTimer);
                }
            });

            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        }
    </script>
</x-layouts.admin>
