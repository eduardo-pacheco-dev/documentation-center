<x-layouts.admin title="Links">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="link" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Links
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie seus links de envio e download de documentos.</p>
        </div>

        <a
            href="{{ route('admin.links.create') }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo link
        </a>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form
            method="GET"
            action="{{ route('admin.links.index') }}"
            class="flex flex-wrap items-center gap-2"
            data-search-form
        >
            @if (request('view'))
                <input type="hidden" name="view" value="{{ request('view') }}">
            @endif
            @if (request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if (request('direction'))
                <input type="hidden" name="direction" value="{{ request('direction') }}">
            @endif
            @if (request('document'))
                <input type="hidden" name="document" value="{{ request('document') }}">
            @endif

            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por título, código ou descrição..."
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    data-search-input
                >
            </div>

            <button
                type="submit"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                Buscar
            </button>

            @if ($search !== '')
                <a
                    href="{{ route('admin.links.index', array_filter(['document' => request('document')])) }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            @if ($document)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-3 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                    <x-icon name="document-text" class="h-3.5 w-3.5" />
                    <span class="max-w-[14rem] truncate">{{ $document->original_name }}</span>
                    <a
                        href="{{ route('admin.links.index') }}"
                        class="rounded-full p-0.5 hover:bg-indigo-200 dark:hover:bg-indigo-500/25"
                        aria-label="Remover filtro por arquivo"
                    >
                        <x-icon name="x-mark" class="h-3.5 w-3.5" />
                    </a>
                </span>
            @endif
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.links.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm {{ $view === $mode ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($view === 'table')
        <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                <thead class="rounded-t-xl bg-gray-50 dark:bg-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
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
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($shortLinks as $shortLink)
                        <tr>
                            <td class="px-5 py-3">
                                <p class="flex items-center gap-1.5 font-mono text-gray-900 dark:text-gray-100">
                                    <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    {{ $shortLink->code }}
                                </p>
                                <p class="mt-0.5 max-w-[16rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->url }}</p>
                            </td>
                            <td class="px-5 py-3">{{ $shortLink->title }}</td>
                            <td class="px-5 py-3">
                                <x-short-link-type :type="$shortLink->type" />
                            </td>
                            <td class="px-5 py-3">
                                <x-short-link-status :short-link="$shortLink" />
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">
                                {{ $shortLink->used_count }} / {{ $shortLink->max_uses ?? '∞' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $shortLink->documents_count + $shortLink->received_documents_count }}</td>
                            <td class="px-5 py-3 text-right">
                                <x-short-link-actions-dropdown :short-link="$shortLink" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="7">
                                <span class="inline-flex items-center gap-2">
                                    <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
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
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($shortLinks as $shortLink)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="link" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $shortLink->code }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->title }}</p>
                            </div>
                        </div>

                        <x-short-link-actions-dropdown :short-link="$shortLink" />
                    </div>

                    <p class="mt-3 truncate text-xs text-gray-400 dark:text-gray-500">{{ $shortLink->url }}</p>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                        <x-short-link-type :type="$shortLink->type" />
                        <x-short-link-status :short-link="$shortLink" />
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <x-icon name="eye" class="h-3.5 w-3.5" />
                        {{ $shortLink->used_count }} / {{ $shortLink->max_uses ?? '∞' }} acessos · {{ $shortLink->documents_count + $shortLink->received_documents_count }} documentos
                    </p>
                </div>
            @empty
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    Nenhum link criado até o momento.
                </p>
            @endforelse
        </div>

        <div class="pt-4">
            {{ $shortLinks->links() }}
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($shortLinks as $shortLink)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="link" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $shortLink->code }}</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->title }}</p>
                    </div>

                    <div class="hidden sm:block">
                        <x-short-link-type :type="$shortLink->type" />
                    </div>

                    <span class="hidden text-xs text-gray-500 md:inline dark:text-gray-400">
                        {{ $shortLink->used_count }} / {{ $shortLink->max_uses ?? '∞' }}
                    </span>

                    <x-short-link-actions-dropdown :short-link="$shortLink" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    Nenhum link criado até o momento.
                </p>
            @endforelse
        </div>

        <div class="pt-4">
            {{ $shortLinks->links() }}
        </div>
    @endif

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('links-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('links-view', link.dataset.viewToggle);
            });
        });

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
