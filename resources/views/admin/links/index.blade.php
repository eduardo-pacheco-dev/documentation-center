<x-layouts.admin title="Links">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $hasFilters = $search !== '' || $type !== null || $status !== null || $document !== null;

        $linksOnPage = $shortLinks->getCollection();
        $documentsOnPage = $linksOnPage->sum(fn ($link) => $link->documents_count + $link->received_documents_count);
        $accessesOnPage = $linksOnPage->sum('used_count');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Compartilhamento
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="link" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Links
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Gerencie links de envio, download e redirecionamento de documentos.
            </p>
        </div>

        <button
            type="button"
            data-create-modal-open
            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo link
        </button>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-start justify-between">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="link" class="h-5 w-5" />
                </span>
                <x-icon name="link" class="h-4 w-4 text-gray-300 dark:text-gray-600" />
            </div>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                {{ $shortLinks->total() }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Links encontrados</p>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <div class="flex items-start justify-between">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="document" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                {{ $documentsOnPage }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Documentos nesta página</p>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <div class="flex items-start justify-between">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                    <x-icon name="eye" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                {{ $accessesOnPage }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Acessos nesta página</p>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <div class="flex items-start justify-between">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    <x-icon name="eye" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                {{ $linksOnPage->count() }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Itens na página</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            method="GET"
            action="{{ route('admin.links.index') }}"
            class="flex flex-wrap items-center gap-2"
            data-search-form
        >
            @foreach (['view', 'sort', 'direction', 'document', 'type', 'status', 'per_page'] as $param)
                @if (request($param))
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach

            <div class="relative w-full sm:w-80">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por título, código ou descrição..."
                    class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-950 py-2.5 pl-10 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    data-search-input
                >
            </div>

            <button
                type="submit"
                class="rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-800"
            >
                Buscar
            </button>

            @if ($hasFilters)
                <a
                    href="{{ route('admin.links.index', array_filter(['view' => $view])) }}"
                    class="text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="type-filter" class="sr-only">Filtrar por tipo</label>
            <select
                id="type-filter"
                name="type"
                onchange="this.form.submit()"
                class="rounded-xl border border-gray-300 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200"
            >
                @foreach (['' => 'Todos os tipos', 'upload' => 'Upload', 'download' => 'Download', 'redirect' => 'Redirect'] as $value => $label)
                    <option value="{{ $value }}" @selected($type === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select
                id="status-filter"
                name="status"
                onchange="this.form.submit()"
                class="rounded-xl border border-gray-300 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200"
            >
                @foreach (['' => 'Todos os status', 'active' => 'Ativos', 'inactive' => 'Desativados', 'expired' => 'Expirados', 'limit' => 'Limite atingido'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>

            @if ($document)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-3 py-1.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                    <x-icon name="document-text" class="h-3.5 w-3.5" />
                    <span class="max-w-[14rem] truncate">{{ $document->original_name }}</span>
                    <a
                        href="{{ route('admin.links.index') }}"
                        class="rounded-full p-0.5 transition hover:bg-indigo-200 dark:hover:bg-indigo-500/25"
                        aria-label="Remover filtro por arquivo"
                    >
                        <x-icon name="x-mark" class="h-3.5 w-3.5" />
                    </a>
                </span>
            @endif
        </form>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $linksOnPage->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $shortLinks->total() }}</span>
            links
        </p>

        <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.links.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm transition {{ $view === $mode ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($view === 'table')
        <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
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
                            <tr class="align-middle transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    <p class="flex items-center gap-2">
                                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                            <x-icon name="link" class="h-4 w-4" />
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-mono text-sm font-medium text-gray-900 dark:text-gray-100">{{ $shortLink->code }}</span>
                                            <span class="block max-w-[16rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->url }}</span>
                                        </span>
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="block max-w-[18rem] truncate font-medium text-gray-900 dark:text-gray-100">{{ $shortLink->title }}</span>
                                    @if ($shortLink->description)
                                        <span class="block max-w-[18rem] truncate text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->description }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-short-link-type :type="$shortLink->type" />
                                </td>
                                <td class="px-5 py-4">
                                    <x-short-link-status :short-link="$shortLink" />
                                </td>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $shortLink->used_count }}</span>
                                    <span class="text-xs">/ {{ $shortLink->max_uses ?? '∞' }}</span>
                                </td>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">
                                    {{ $shortLink->documents_count + $shortLink->received_documents_count }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-short-link-actions-dropdown :short-link="$shortLink" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-12 text-center text-gray-500 dark:text-gray-400" colspan="7">
                                    <span class="inline-flex flex-col items-center gap-2">
                                        <x-icon name="link" class="h-6 w-6 text-gray-300 dark:text-gray-600" />
                                        @if ($hasFilters)
                                            Nenhum link encontrado para os filtros informados.
                                        @else
                                            Nenhum link criado até o momento.
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                @include('admin.links.partials.pagination')
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($shortLinks as $shortLink)
                @php
                    $progress = $shortLink->max_uses ? min(100, (int) round($shortLink->used_count / max(1, $shortLink->max_uses) * 100)) : null;
                @endphp
                <article class="group rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <x-icon name="link" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $shortLink->code }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->title }}</p>
                            </div>
                        </div>

                        <x-short-link-actions-dropdown :short-link="$shortLink" />
                    </div>

                    <p class="mt-3 truncate text-xs text-gray-400 dark:text-gray-500">{{ $shortLink->url }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <x-short-link-type :type="$shortLink->type" />
                        <x-short-link-status :short-link="$shortLink" />
                    </div>

                    @if ($progress !== null)
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Acessos</span>
                                <span>{{ $shortLink->used_count }} / {{ $shortLink->max_uses }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                            <x-icon name="eye" class="h-3.5 w-3.5" />
                            {{ $shortLink->used_count }} acessos · {{ $shortLink->documents_count + $shortLink->received_documents_count }} documentos
                        </p>
                    @endif
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    @if ($hasFilters)
                        Nenhum link encontrado para os filtros informados.
                    @else
                        Nenhum link criado até o momento.
                    @endif
                </div>
            @endforelse
        </div>

        <div class="pt-4">
            @include('admin.links.partials.pagination')
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($shortLinks as $shortLink)
                <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="link" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-mono text-sm font-medium text-gray-900 dark:text-gray-100">{{ $shortLink->code }}</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->title }}</p>
                    </div>

                    <div class="hidden sm:block">
                        <x-short-link-type :type="$shortLink->type" />
                    </div>

                    <span class="hidden text-xs text-gray-500 md:inline dark:text-gray-400">
                        {{ $shortLink->used_count }} / {{ $shortLink->max_uses ?? '∞' }}
                    </span>

                    <x-short-link-status :short-link="$shortLink" />

                    <x-short-link-actions-dropdown :short-link="$shortLink" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($hasFilters)
                        Nenhum link encontrado para os filtros informados.
                    @else
                        Nenhum link criado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="pt-4">
            @include('admin.links.partials.pagination')
        </div>
    @endif

    @include('admin.links.partials.create-modal')
    @include('admin.links.partials.link-modal')
    @include('admin.links.partials.details-modal')

    @foreach ($shortLinks as $shortLink)
        @include('admin.links.partials.edit-modal', ['shortLink' => $shortLink])
    @endforeach

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

        const createModal = document.querySelector('[data-create-modal]');

        const openCreateModal = () => {
            createModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        const closeCreateModal = () => {
            createModal.classList.add('hidden');
            document.body.style.overflow = '';
        };

        if (createModal) {
            document.querySelectorAll('[data-create-modal-open]').forEach((button) => {
                button.addEventListener('click', openCreateModal);
            });

            document.querySelectorAll('[data-create-modal-close]').forEach((button) => {
                button.addEventListener('click', closeCreateModal);
            });

            if (createModal.hasAttribute('data-open-on-load')) {
                openCreateModal();
            }
        }

        const linkModal = document.querySelector('[data-link-modal]');

        if (linkModal) {
            const linkUrl = linkModal.querySelector('[data-link-modal-url]');
            const linkTitle = linkModal.querySelector('[data-link-modal-title]');
            const linkOpen = linkModal.querySelector('[data-link-modal-open-link]');
            const linkCopy = linkModal.querySelector('[data-link-modal-copy]');
            const linkCopyLabel = linkModal.querySelector('[data-link-modal-copy-label]');

            const openLinkModal = (button) => {
                linkUrl.textContent = button.dataset.linkUrl;
                linkTitle.textContent = button.dataset.linkTitle;
                linkOpen.href = button.dataset.linkUrl;
                linkCopyLabel.textContent = 'Copiar';
                linkModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };

            const closeLinkModal = () => {
                linkModal.classList.add('hidden');
                document.body.style.overflow = '';
            };

            document.querySelectorAll('[data-link-modal-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    closeDropdowns();
                    openLinkModal(button);
                });
            });

            linkModal.querySelectorAll('[data-link-modal-close]').forEach((button) => {
                button.addEventListener('click', closeLinkModal);
            });

            linkCopy.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(linkOpen.href);
                    linkCopyLabel.textContent = 'Copiado!';
                    setTimeout(() => {
                        linkCopyLabel.textContent = 'Copiar';
                    }, 2000);
                } catch (error) {
                    window.prompt('Copie o link:', linkOpen.href);
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !linkModal.classList.contains('hidden')) {
                    closeLinkModal();
                }
            });
        }

        const detailsModal = document.querySelector('[data-link-details-modal]');

        if (detailsModal) {
            const detailsContent = detailsModal.querySelector('[data-link-details-content]');

            const openDetailsModal = (template) => {
                detailsContent.replaceChildren(template.content.cloneNode(true));
                detailsModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };

            const closeDetailsModal = () => {
                detailsModal.classList.add('hidden');
                detailsContent.replaceChildren();
                document.body.style.overflow = '';
            };

            document.querySelectorAll('[data-link-details-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    closeDropdowns();
                    openDetailsModal(button.closest('[data-link-actions]').querySelector('[data-link-details-template]'));
                });
            });

            detailsModal.addEventListener('click', (event) => {
                if (event.target.closest('[data-link-details-close]')) {
                    closeDetailsModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !detailsModal.classList.contains('hidden')) {
                    closeDetailsModal();
                }
            });
        }

        const editModals = document.querySelectorAll('[data-link-edit-modal]');

        if (editModals.length) {
            const openEditModal = (modal) => {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };

            const closeEditModal = (modal) => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            };

            const closeOtherDialogs = () => {
                document.querySelectorAll('[data-link-details-modal]:not(.hidden), [data-link-modal]:not(.hidden)').forEach((dialog) => {
                    dialog.classList.add('hidden');
                });

                const detailsContent = document.querySelector('[data-link-details-content]');

                if (detailsContent) {
                    detailsContent.replaceChildren();
                }
            };

            document.addEventListener('click', (event) => {
                const opener = event.target.closest('[data-modal-open]');

                if (opener) {
                    event.preventDefault();
                    closeDropdowns();
                    closeOtherDialogs();

                    const modal = document.getElementById(opener.dataset.modalOpen);

                    if (modal) {
                        openEditModal(modal);
                    }

                    return;
                }

                const closer = event.target.closest('[data-modal-close]');

                if (closer) {
                    const dialog = closer.closest('[role="dialog"]');

                    if (dialog && dialog.hasAttribute('data-link-edit-modal')) {
                        closeEditModal(dialog);
                    }
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    editModals.forEach((modal) => {
                        if (!modal.classList.contains('hidden')) {
                            closeEditModal(modal);
                        }
                    });
                }
            });

            editModals.forEach((modal) => {
                if (modal.hasAttribute('data-open')) {
                    openEditModal(modal);
                }
            });
        }
    </script>
</x-layouts.admin>