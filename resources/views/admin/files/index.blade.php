<x-layouts.admin title="Arquivos">
    @php
        $uploadFailed = collect($errors->getMessages())->keys()
            ->contains(fn (string $key): bool => str_starts_with($key, 'documents'));

        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $sortQuery = $viewQuery;
        unset($sortQuery['sort'], $sortQuery['direction']);

        $clearQuery = request()->query();
        unset($clearQuery['search'], $clearQuery['page']);
        $clearUrl = route('admin.files.index', $clearQuery);

        $sortableColumns = ['original_name', 'size', 'short_links_count', 'created_at'];
        $currentSort = in_array(request('sort'), $sortableColumns, true) ? request('sort') : 'created_at';
        $currentDirection = in_array(request('direction'), ['asc', 'desc'], true)
            ? request('direction')
            : ($currentSort === 'created_at' ? 'desc' : 'asc');

        $sortOptions = [
            ['label' => 'Nome (A–Z)', 'sort' => 'original_name', 'direction' => 'asc'],
            ['label' => 'Nome (Z–A)', 'sort' => 'original_name', 'direction' => 'desc'],
            ['label' => 'Maior tamanho', 'sort' => 'size', 'direction' => 'desc'],
            ['label' => 'Menor tamanho', 'sort' => 'size', 'direction' => 'asc'],
            ['label' => 'Mais recentes', 'sort' => 'created_at', 'direction' => 'desc'],
            ['label' => 'Mais antigos', 'sort' => 'created_at', 'direction' => 'asc'],
        ];

        $activeSortLabel = collect($sortOptions)
            ->first(fn (array $option): bool => $option['sort'] === $currentSort && $option['direction'] === $currentDirection)['label']
            ?? 'Mais recentes';

        $viewModes = [
            'table' => ['label' => 'Lista', 'icon' => 'table-cells'],
            'cards' => ['label' => 'Grade', 'icon' => 'squares-2x2'],
        ];

        $navQuery = request()->query();
        unset($navQuery['page'], $navQuery['search'], $navQuery['folder']);

        $rootUrl = route('admin.files.index', $navQuery);

        $activeTreePath = collect($breadcrumbs)
            ->map(fn ($crumb) => $crumb->getKey())
            ->all();

        if ($folder !== null) {
            $activeTreePath[] = $folder->getKey();
        }
    @endphp

    <form id="upload-form" method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data" class="hidden">
        @csrf
        @if ($folder !== null)
            <input type="hidden" name="folder" value="{{ $folder->getKey() }}">
        @endif
        <input
            id="file-picker"
            type="file"
            name="documents[]"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.odt,.ods,.jpg,.jpeg,.png"
        >
    </form>

    <div class="sticky top-0 z-30 -mx-4 border-b border-gray-200 bg-gray-50/95 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/95 {{ session('status') ? '' : '-mt-8' }}">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <div class="relative">
                <button
                    type="button"
                    data-dropdown-toggle
                    aria-expanded="false"
                    class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus-visible:ring-offset-gray-950"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Novo
                    <x-icon name="chevron-down" class="h-4 w-4" />
                </button>

                <div
                    data-dropdown-menu
                    role="menu"
                    class="absolute left-0 z-20 mt-2 hidden w-52 origin-top-left rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                >
                    <button
                        type="button"
                        data-modal-open="folder-modal"
                        role="menuitem"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <x-icon name="folder-plus" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        Nova pasta
                    </button>

                    <button
                        type="button"
                        data-upload-trigger
                        role="menuitem"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <x-icon name="arrow-up-tray" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        Enviar arquivos
                    </button>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('admin.files.index') }}"
                class="relative min-w-[14rem] flex-1"
                data-search-form
            >
                @if (request('view'))
                    <input type="hidden" name="view" value="{{ request('view') }}">
                @endif
                @if (request('folder'))
                    <input type="hidden" name="folder" value="{{ request('folder') }}">
                @endif
                @if (request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                @if (request('direction'))
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                @endif

                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar na biblioteca"
                    class="block w-full rounded-full border border-gray-200 bg-white py-2.5 pl-11 pr-10 text-sm shadow-sm placeholder:text-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:border-gray-700 dark:bg-gray-900 dark:placeholder:text-gray-500"
                    data-search-input
                >
                @if ($search !== '')
                    <a
                        href="{{ $clearUrl }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Limpar busca"
                    >
                        <x-icon name="x-mark" class="h-4 w-4" />
                    </a>
                @endif
            </form>

            @if ($view === 'table')
                <div class="relative">
                    <button
                        type="button"
                        data-dropdown-toggle
                        aria-expanded="false"
                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <x-icon name="chevron-up-down" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        {{ $activeSortLabel }}
                    </button>

                    <div
                        data-dropdown-menu
                        role="menu"
                        class="absolute right-0 z-20 mt-2 hidden w-56 origin-top-right rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                    >
                        <p class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Ordenar por</p>

                        @foreach ($sortOptions as $option)
                            <a
                                href="{{ route('admin.files.index', array_merge($sortQuery, ['sort' => $option['sort'], 'direction' => $option['direction']])) }}"
                                role="menuitem"
                                class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                            >
                                <span>{{ $option['label'] }}</span>
                                @if ($option['sort'] === $currentSort && $option['direction'] === $currentDirection)
                                    <x-icon name="check" class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="inline-flex items-center gap-0.5 rounded-full border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-900" role="group" aria-label="Modo de visualização">
                @foreach ($viewModes as $mode => $meta)
                    <a
                        href="{{ route('admin.files.index', array_merge($viewQuery, ['view' => $mode])) }}"
                        data-view-toggle="{{ $mode }}"
                        title="{{ $meta['label'] }}"
                        aria-label="{{ $meta['label'] }}"
                        aria-pressed="{{ $view === $mode ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $view === $mode ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }}"
                    >
                        <x-icon :name="$meta['icon']" class="h-4 w-4" />
                        <span class="hidden sm:inline">{{ $meta['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-col gap-6 lg:flex-row lg:items-start">
        <aside class="w-full shrink-0 lg:w-64">
            <nav
                aria-label="Árvore de pastas"
                class="max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:sticky lg:top-24 lg:max-h-none"
            >
                <ul class="space-y-0.5">
                    <li>
                        <a
                            href="{{ $rootUrl }}"
                            @if ($folder === null) aria-current="page" @endif
                            class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm {{ $folder === null ? 'bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800' }}"
                        >
                            <x-icon name="folder" class="h-4 w-4 shrink-0 {{ $folder === null ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}" />
                            <span class="truncate">Meus arquivos</span>
                        </a>
                    </li>

                    @foreach ($folderTree as $treeNode)
                        <x-folder-tree-node :node="$treeNode" :active-tree-path="$activeTreePath" :active-id="$folder?->getKey()" :nav-query="$navQuery" />
                    @endforeach
                </ul>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
    <nav aria-label="Você está em" class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
        <a
            href="{{ $rootUrl }}"
            class="inline-flex items-center gap-1.5 font-medium text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white"
        >
            <x-icon name="folder" class="h-4 w-4" />
            Meus arquivos
        </a>

        @foreach ($breadcrumbs as $crumb)
            <span aria-hidden="true">/</span>
            @if ($loop->last)
                <span class="font-medium text-gray-900 dark:text-gray-100" aria-current="page">{{ $crumb->name }}</span>
            @else
                <a
                    href="{{ route('admin.files.index', array_merge($navQuery, ['folder' => $crumb->getKey()])) }}"
                    class="hover:text-gray-900 dark:hover:text-white"
                >
                    {{ $crumb->name }}
                </a>
            @endif
        @endforeach
    </nav>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        @if ($folders->isNotEmpty() || $documents->isNotEmpty())
            <p data-list-summary class="text-sm text-gray-500 dark:text-gray-400">
                @if ($folders->isNotEmpty())
                    {{ $folders->count() }} {{ Str::plural('pasta', $folders->count()) }}
                    @if ($documents->isNotEmpty())
                        ·
                    @endif
                @endif
                @if ($documents->isNotEmpty())
                    {{ $documents->total() }} {{ Str::plural('arquivo', $documents->total()) }}
                @endif
                @if ($search !== '')
                    para &ldquo;{{ $search }}&rdquo;
                @endif
            </p>
        @endif

        @if ($documents->isNotEmpty())
            <div
                id="selection-bar"
                class="hidden w-full items-center justify-between gap-3 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 dark:border-indigo-500/30 dark:bg-indigo-500/10 sm:w-auto"
            >
                <span id="selection-count" class="text-sm font-medium text-indigo-700 dark:text-indigo-300">0 arquivos selecionados</span>

                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        data-bulk-link
                        class="rounded-md px-2.5 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                    >
                        Criar link
                    </button>
                    <button
                        type="button"
                        data-bulk-move
                        class="rounded-md px-2.5 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                    >
                        Mover para
                    </button>
                    <button
                        type="button"
                        data-bulk-rename
                        disabled
                        class="rounded-md px-2.5 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                    >
                        Renomear
                    </button>
                    <button
                        type="button"
                        data-bulk-delete
                        class="rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-500/15"
                    >
                        Excluir
                    </button>
                    <button
                        type="button"
                        data-selection-clear
                        class="rounded-md p-1.5 text-indigo-500 hover:bg-indigo-100 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                        aria-label="Limpar seleção"
                    >
                        <x-icon name="x-mark" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if ($documents->isNotEmpty() || $folders->isNotEmpty())
        @if ($view === 'table')
            <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:overflow-x-visible">
                <table class="w-full min-w-[42rem] table-fixed text-sm">
                    <thead class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="w-12 rounded-tl-xl bg-gray-50/80 px-4 py-3 dark:bg-gray-800/60">
                                <input
                                    type="checkbox"
                                    data-select-all
                                    aria-label="Selecionar todos os arquivos"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:checked:bg-indigo-500 dark:text-indigo-400"
                                >
                            </th>
                            <x-sort-header column="original_name" label="Nome" class="bg-gray-50/80 px-4 py-3 dark:bg-gray-800/60" />
                            <x-sort-header column="size" label="Tamanho" class="w-28 whitespace-nowrap bg-gray-50/80 px-4 py-3 dark:bg-gray-800/60" />
                            <x-sort-header column="short_links_count" label="Links" class="w-24 whitespace-nowrap bg-gray-50/80 px-4 py-3 dark:bg-gray-800/60" />
                            <x-sort-header column="created_at" label="Modificado" class="w-32 whitespace-nowrap bg-gray-50/80 px-4 py-3 dark:bg-gray-800/60" />
                            <th scope="col" class="w-24 rounded-tr-xl bg-gray-50/80 px-4 py-3 text-right font-semibold dark:bg-gray-800/60">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($folders as $currentFolder)
                            <tr
                                data-folder-row
                                data-folder-id="{{ $currentFolder->getKey() }}"
                                class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/60"
                            >
                                <td class="px-4 py-3"></td>

                                <td class="px-4 py-3">
                                    <a
                                        href="{{ route('admin.files.index', array_merge($navQuery, ['folder' => $currentFolder->getKey()])) }}"
                                        class="flex items-center gap-3 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                    >
                                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                            <x-icon name="folder" class="h-5 w-5" />
                                        </span>

                                        <span class="min-w-0">
                                            <span class="block truncate font-medium text-gray-900 dark:text-gray-100" title="{{ $currentFolder->name }}">
                                                {{ $currentFolder->name }}
                                            </span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                {{ $currentFolder->documents_count }} {{ Str::plural('arquivo', $currentFolder->documents_count) }}
                                            </span>
                                        </span>
                                    </a>
                                </td>

                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3"></td>

                                <td class="whitespace-nowrap px-4 py-3 text-gray-500 dark:text-gray-400">
                                    <x-relative-time :value="$currentFolder->created_at" />
                                </td>

                                <td class="px-4 py-3">
                                    <x-folder-actions :folder="$currentFolder" class="justify-end" />
                                </td>
                            </tr>
                        @endforeach

                        @foreach ($documents as $document)
                            <tr
                                data-row
                                data-document-id="{{ $document->getKey() }}"
                                data-document-name="{{ $document->original_name }}"
                                class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/60"
                            >
                                <td class="px-4 py-3 align-middle">
                                    <input
                                        type="checkbox"
                                        data-select
                                        value="{{ $document->getKey() }}"
                                        aria-label="Selecionar {{ $document->original_name }}"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:checked:bg-indigo-500 dark:text-indigo-400"
                                    >
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-file-type-icon :name="$document->original_name" />

                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-gray-900 dark:text-gray-100" title="{{ $document->original_name }}">
                                                {{ $document->original_name }}
                                            </p>

                                            @if ($document->uploaded_via_short_link_id !== null)
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    <span class="inline-flex rounded-full bg-indigo-50 px-1.5 py-0.5 text-[11px] font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                                        Recebido via link
                                                    </span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-500 dark:text-gray-400">
                                    {{ Number::fileSize($document->size) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1.5 tabular-nums">
                                        <x-icon name="link" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                                        {{ $document->short_links_count }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-gray-500 dark:text-gray-400">
                                    <x-relative-time :value="$document->created_at" />
                                </td>

                                <td class="px-4 py-3">
                                    <x-document-actions :document="$document" class="justify-end" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($view === 'cards')
            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($folders as $currentFolder)
                    <div
                        data-folder-row
                        data-folder-id="{{ $currentFolder->getKey() }}"
                        class="group relative flex flex-col rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700"
                    >
                        <a
                            href="{{ route('admin.files.index', array_merge($navQuery, ['folder' => $currentFolder->getKey()])) }}"
                            class="flex h-32 items-center justify-center rounded-t-xl bg-gradient-to-b from-amber-50 to-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:from-amber-500/10 dark:to-gray-900"
                        >
                            <x-icon name="folder" class="h-14 w-14 text-amber-500 dark:text-amber-400" />
                        </a>

                        <div class="flex items-start justify-between gap-2 border-t border-gray-100 p-3 dark:border-gray-800">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $currentFolder->name }}">
                                    {{ $currentFolder->name }}
                                </p>
                                <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $currentFolder->documents_count }} {{ Str::plural('arquivo', $currentFolder->documents_count) }}
                                    · <x-relative-time :value="$currentFolder->created_at" />
                                </p>
                            </div>

                            <x-folder-actions :folder="$currentFolder" />
                        </div>
                    </div>
                @endforeach

                @foreach ($documents as $document)
                    <div
                        data-row
                        data-document-id="{{ $document->getKey() }}"
                        data-document-name="{{ $document->original_name }}"
                        class="group relative flex flex-col rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700"
                    >
                        <div class="relative flex h-32 items-center justify-center rounded-t-xl bg-gradient-to-b from-gray-50 to-white dark:from-gray-800/70 dark:to-gray-900">
                            <x-file-type-icon :name="$document->original_name" size="lg" />

                            <input
                                type="checkbox"
                                data-select
                                value="{{ $document->getKey() }}"
                                aria-label="Selecionar {{ $document->original_name }}"
                                class="absolute left-3 top-3 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:checked:bg-indigo-500 dark:text-indigo-400"
                            >
                        </div>

                        <div class="flex items-start justify-between gap-2 border-t border-gray-100 p-3 dark:border-gray-800">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $document->original_name }}">
                                    {{ $document->original_name }}
                                </p>
                                <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ Number::fileSize($document->size) }}
                                    · {{ $document->short_links_count }} {{ Str::plural('link', $document->short_links_count) }}
                                    · <x-relative-time :value="$document->created_at" />
                                </p>
                            </div>

                            <x-document-actions :document="$document" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @foreach ($documents as $document)
            <form
                id="generate-link-{{ $document->getKey() }}"
                method="POST"
                action="{{ route('admin.files.generate-link') }}"
                class="hidden"
            >
                @csrf
                <input type="hidden" name="document_ids[]" value="{{ $document->getKey() }}">
            </form>

            <form
                id="delete-document-{{ $document->getKey() }}"
                method="POST"
                action="{{ route('admin.files.destroy', $document) }}"
                class="hidden"
            >
                @csrf
                @method('DELETE')
            </form>

            <div
                id="links-modal-{{ $document->getKey() }}"
                class="fixed inset-0 z-50 hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="links-modal-title-{{ $document->getKey() }}"
            >
                <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

                <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-file-type-icon :name="$document->original_name" />

                            <div class="min-w-0">
                                <h2 id="links-modal-title-{{ $document->getKey() }}" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    Gerenciar links
                                </h2>
                                <p class="mt-0.5 truncate text-sm text-gray-500 dark:text-gray-400">{{ $document->original_name }}</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            data-modal-close
                        >
                            <span class="sr-only">Fechar</span>
                            <x-icon name="x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <ul class="mt-4 max-h-72 space-y-2 overflow-y-auto">
                        @forelse ($document->shortLinks as $shortLink)
                            <li class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-800">
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1.5 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <span class="truncate">{{ $shortLink->title }}</span>
                                        @if ($shortLink->type->value === 'upload')
                                            <span class="inline-flex shrink-0 items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                                Upload
                                            </span>
                                        @else
                                            <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                                Download
                                            </span>
                                        @endif
                                    </p>

                                    <p class="mt-0.5 truncate font-mono text-xs text-gray-500 dark:text-gray-400">{{ $shortLink->url }}</p>

                                    @if ($shortLink->is_active === false)
                                        <span class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            Desativado
                                        </span>
                                    @elseif ($shortLink->isExpired())
                                        <span class="mt-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/15 dark:text-red-300">
                                            Expirado
                                        </span>
                                    @elseif ($shortLink->hasReachedAccessLimit())
                                        <span class="mt-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                            Limite atingido
                                        </span>
                                    @else
                                        <span class="mt-1 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-300">
                                            Ativo
                                        </span>
                                    @endif
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <a
                                        href="{{ route('admin.links.edit', $shortLink) }}"
                                        class="rounded-md px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800"
                                    >
                                        Abrir
                                    </a>

                                    @if ($shortLink->type->value !== 'upload')
                                        <form method="POST" action="{{ route('admin.links.documents.destroy', [$shortLink, $document]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
                                                data-confirm="Remover este arquivo do link?"
                                            >
                                                Remover
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-gray-300 px-3 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Este arquivo ainda não está em nenhum link.
                                <div class="mt-3">
                                    <button
                                        type="submit"
                                        form="generate-link-{{ $document->getKey() }}"
                                        class="rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                    >
                                        Criar link com este arquivo
                                    </button>
                                </div>
                            </li>
                        @endforelse
                    </ul>

                    <div class="mt-4 flex justify-end">
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                            data-modal-close
                        >
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        <form id="bulk-generate-link-form" method="POST" action="{{ route('admin.files.generate-link') }}" class="hidden">
            @csrf
            <div data-bulk-ids></div>
        </form>

        <div data-delete-template="{{ route('admin.files.destroy', '__ID__') }}" class="hidden"></div>

        @if ($documents->isNotEmpty())
            <div class="mt-5">
                {{ $documents->links() }}
            </div>
        @endif
    @elseif ($search !== '')
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-gray-900">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <x-icon name="magnifying-glass" class="h-7 w-7" />
            </span>
            <h2 class="mt-4 text-base font-semibold text-gray-900 dark:text-gray-100">Nenhum arquivo encontrado</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Nenhum arquivo corresponde a &ldquo;{{ $search }}&rdquo;.
            </p>
            <a
                href="{{ $clearUrl }}"
                class="mt-4 inline-flex rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
            >
                Limpar busca
            </a>
        </div>
    @elseif ($folder !== null)
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-gray-900">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <x-icon name="folder" class="h-7 w-7" />
            </span>
            <h2 class="mt-4 text-base font-semibold text-gray-900 dark:text-gray-100">Nenhum arquivo aqui ainda</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Arraste os arquivos para esta página ou clique em &ldquo;Enviar arquivos&rdquo;.
            </p>
            <button
                type="button"
                data-upload-trigger
                class="mt-5 inline-flex items-center gap-2 rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
            >
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                Enviar arquivos
            </button>
        </div>
    @else
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-gray-900">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <x-icon name="document-text" class="h-7 w-7" />
            </span>
            <h2 class="mt-4 text-base font-semibold text-gray-900 dark:text-gray-100">Sua biblioteca está vazia</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Arraste os arquivos para esta página ou clique em &ldquo;Enviar arquivos&rdquo;.
            </p>
            <p class="mx-auto mt-2 max-w-md text-xs text-gray-400 dark:text-gray-500">
                Até 10 arquivos por envio, cada um com no máximo 20 MB. PDF, DOC, XLS, PPT, TXT, CSV, ODT, ODS, JPG e PNG.
            </p>
            <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                <button
                    type="button"
                    data-upload-trigger
                    class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    <x-icon name="arrow-up-tray" class="h-4 w-4" />
                    Enviar arquivos
                </button>
                <button
                    type="button"
                    data-modal-open="folder-modal"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <x-icon name="folder-plus" class="h-4 w-4" />
                    Criar pasta
                </button>
            </div>
        </div>
    @endif
        </div>
    </div>

    <div
        id="rename-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="rename-modal-title"
        data-action-template="{{ route('admin.files.update', '__ID__') }}"
        @if ($errors->has('original_name') && old('rename_document')) data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="rename-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Renomear arquivo</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Defina um novo nome para o arquivo.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <form id="rename-form" method="POST" action="" class="mt-4 space-y-3">
                @csrf
                @method('PUT')

                <input type="hidden" name="rename_document" value="{{ old('rename_document') }}">

                <label for="rename-input" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome do arquivo</label>
                <input
                    id="rename-input"
                    type="text"
                    name="original_name"
                    value="{{ old('original_name') }}"
                    required
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:border-gray-700 dark:bg-gray-900"
                >

                @error('original_name')
                    <p class="text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="folder-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="folder-modal-title"
        @if ($errors->has('name') && old('folder_modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="folder-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Nova pasta</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($folder !== null)
                            A pasta será criada dentro de &ldquo;{{ $folder->name }}&rdquo;.
                        @else
                            A pasta será criada em Meus arquivos.
                        @endif
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <form id="folder-form" method="POST" action="{{ route('admin.folders.store') }}" class="mt-4 space-y-3">
                @csrf

                <input type="hidden" name="parent_id" value="{{ old('parent_id', $folder?->getKey()) }}">
                <input type="hidden" name="folder_modal" value="create">

                <label for="folder-name-input" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome da pasta</label>
                <input
                    id="folder-name-input"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Ex.: Contratos"
                    required
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:border-gray-700 dark:bg-gray-900"
                >

                @error('name')
                    <p class="text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        Criar pasta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="folder-rename-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="folder-rename-modal-title"
        data-action-template="{{ route('admin.folders.update', '__ID__') }}"
        @if ($errors->has('name') && old('folder_modal') === 'rename') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="folder-rename-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Renomear pasta</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Defina um novo nome para a pasta.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <form id="folder-rename-form" method="POST" action="" class="mt-4 space-y-3">
                @csrf
                @method('PUT')

                <input type="hidden" name="rename_folder" value="{{ old('rename_folder') }}">
                <input type="hidden" name="folder_modal" value="rename">

                <label for="folder-rename-input" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome da pasta</label>
                <input
                    id="folder-rename-input"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:border-gray-700 dark:bg-gray-900"
                >

                @error('name')
                    <p class="text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="move-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="move-modal-title"
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="move-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Mover para</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escolha a pasta de destino.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <form id="move-form" method="POST" action="{{ route('admin.files.move') }}" class="mt-4 space-y-3">
                @csrf

                <input type="hidden" name="folder" id="move-folder-input" value="">
                <div data-move-ids></div>

                @error('folder')
                    <p class="text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
                @error('document_ids')
                    <p class="text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror

                <nav id="move-path" aria-label="Destino atual" class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"></nav>

                <div
                    id="move-list"
                    class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 p-1 dark:border-gray-800"
                ></div>

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        Mover para aqui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script id="folder-tree" type="application/json">@json($folderTree)</script>

    <div
        id="preview-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="preview-modal-title"
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 flex max-h-[90vh] w-full max-w-4xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div class="min-w-0">
                    <h2 id="preview-modal-title" data-preview-title class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100"></h2>
                    <p data-preview-meta class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"></p>
                </div>

                <button
                    type="button"
                    data-modal-close
                    aria-label="Fechar pré-visualização"
                    class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-auto p-5">
                <img data-preview-image alt="" class="mx-auto hidden max-h-[70vh] rounded-lg" />

                <iframe data-preview-frame title="Pré-visualização" class="hidden h-[75vh] w-full rounded-lg border-0"></iframe>

                <pre data-preview-text class="hidden max-h-[75vh] overflow-auto whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-200"></pre>

                <div data-preview-fallback class="hidden flex-col items-center justify-center gap-4 py-8 text-center">
                    <x-icon name="document" class="h-14 w-14 text-gray-300 dark:text-gray-600" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Este tipo de arquivo não pode ser pré-visualizado no navegador.</p>
                    <a
                        data-preview-download
                        href="#"
                        download
                        class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        <x-icon name="arrow-down-tray" class="h-4 w-4" />
                        Baixar arquivo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div
        id="upload-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="upload-modal-title"
        @if ($uploadFailed) data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                        <x-icon name="exclamation-triangle" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 id="upload-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Não foi possível enviar</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revise os arquivos e tente novamente.</p>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <ul class="mt-4 space-y-2">
                @foreach ($errors->getMessages() as $key => $messages)
                    @if (str_starts_with($key, 'documents'))
                        @foreach ($messages as $message)
                            <li class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300" data-modal-error>
                                {{ $message }}
                            </li>
                        @endforeach
                    @endif
                @endforeach
            </ul>

            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                Até 10 arquivos por envio, cada um com no máximo 20 MB. PDF, DOC, XLS, PPT, TXT, CSV, ODT, ODS, JPG e PNG.
            </p>

            <div class="mt-4 flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    data-modal-close
                >
                    Fechar
                </button>
                <button
                    type="button"
                    data-upload-trigger
                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    Selecionar novamente
                </button>
            </div>
        </div>
    </div>

    <div
        id="drop-overlay"
        class="pointer-events-none fixed inset-0 z-40 items-center justify-center p-6"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm dark:bg-gray-950/60"></div>
        <div class="relative flex w-full max-w-md flex-col items-center rounded-2xl border-2 border-dashed border-indigo-400 bg-white/95 px-6 py-10 text-center shadow-xl dark:border-indigo-500 dark:bg-gray-900/95">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
                <x-icon name="arrow-up-tray" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-base font-semibold text-gray-900 dark:text-gray-100">Solte para enviar</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Até 10 arquivos de até 20 MB cada</p>
        </div>
    </div>

    <script>
        (function () {
            const qs = (selector, context = document) => context.querySelector(selector);
            const qsa = (selector, context = document) => Array.from(context.querySelectorAll(selector));

            (function persistView() {
                const params = new URLSearchParams(window.location.search);

                if (params.has('view')) {
                    return;
                }

                const savedView = localStorage.getItem('files-view');

                if (!['table', 'cards'].includes(savedView)) {
                    return;
                }

                params.set('view', savedView);
                window.location.replace(window.location.pathname + '?' + params.toString());
            })();

            qsa('[data-view-toggle]').forEach((link) => {
                link.addEventListener('click', () => localStorage.setItem('files-view', link.dataset.viewToggle));
            });

            const closeDropdowns = (except) => {
                qsa('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
                    if (menu !== except) {
                        menu.classList.add('hidden');
                        menu.previousElementSibling?.setAttribute('aria-expanded', 'false');
                    }
                });
            };

            const openModal = (modal) => {
                if (!modal) return;
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                const error = qs('[data-modal-error]', modal);
                if (error) error.scrollIntoView({ block: 'center' });
            };

            const closeModal = (modal) => {
                if (!modal) return;
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
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

                const trigger = event.target.closest('[data-modal-open]');

                if (trigger) {
                    closeDropdowns();
                    openModal(document.getElementById(trigger.dataset.modalOpen));
                    return;
                }

                const closer = event.target.closest('[data-modal-close]');

                if (closer) {
                    closeModal(closer.closest('[role="dialog"]'));
                }
            });

            document.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-tree-toggle]');

                if (!toggle) return;

                const children = toggle.closest('[data-tree-node]')?.querySelector(':scope > ul');

                if (!children) return;

                const willHide = !children.classList.contains('hidden');
                children.classList.toggle('hidden', willHide);
                toggle.setAttribute('aria-expanded', String(!willHide));
                toggle.querySelector('svg')?.classList.toggle('-rotate-90', willHide);
            });

            const previewModal = document.getElementById('preview-modal');

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-preview-open]');

                if (!trigger || !previewModal) return;

                closeDropdowns();

                const url = trigger.dataset.previewUrl;
                const name = trigger.dataset.previewName;
                const mime = trigger.dataset.previewMime || '';
                const extension = name.includes('.') ? name.split('.').pop().toLowerCase() : '';

                const isImage = mime.startsWith('image/') || ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'avif', 'bmp'].includes(extension);
                const isPdf = mime === 'application/pdf' || extension === 'pdf';
                const isText = mime.startsWith('text/') || ['txt', 'md', 'json', 'csv', 'log', 'xml', 'html', 'css', 'js', 'php'].includes(extension);

                const image = qs('[data-preview-image]', previewModal);
                const frame = qs('[data-preview-frame]', previewModal);
                const text = qs('[data-preview-text]', previewModal);
                const fallback = qs('[data-preview-fallback]', previewModal);

                image.classList.add('hidden');
                frame.classList.add('hidden');
                text.classList.add('hidden');
                fallback.classList.add('hidden');
                image.removeAttribute('src');
                frame.removeAttribute('src');
                text.textContent = '';

                qs('[data-preview-title]', previewModal).textContent = name;
                qs('[data-preview-meta]', previewModal).textContent = trigger.dataset.previewSize || '';
                qs('[data-preview-download]', previewModal).href = url;

                if (isImage) {
                    image.src = url;
                    image.alt = name;
                    image.classList.remove('hidden');
                } else if (isPdf) {
                    frame.src = url;
                    frame.classList.remove('hidden');
                } else if (isText) {
                    text.classList.remove('hidden');
                    fetch(url)
                        .then((response) => (response.ok ? response.text() : Promise.reject()))
                        .then((content) => { text.textContent = content; })
                        .catch(() => {
                            text.classList.add('hidden');
                            fallback.classList.remove('hidden');
                        });
                } else {
                    fallback.classList.remove('hidden');
                }

                openModal(previewModal);
            });

            document.addEventListener('click', (event) => {
                const confirmation = event.target.closest('[data-confirm]');

                if (confirmation && !window.confirm(confirmation.dataset.confirm)) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);

            const renameModal = document.getElementById('rename-modal');
            const renameForm = document.getElementById('rename-form');

            const openRenameModal = (documentId, documentName) => {
                if (!renameModal || !renameForm) return;

                renameForm.action = renameModal.dataset.actionTemplate.replace('__ID__', documentId);
                renameForm.elements.original_name.value = documentName ?? '';
                renameForm.elements.rename_document.value = documentId;
                openModal(renameModal);
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-rename-open]');

                if (!trigger) return;

                closeDropdowns();
                openRenameModal(trigger.dataset.documentId, trigger.dataset.documentName);
            });

            if (renameModal?.hasAttribute('data-open') && renameForm.elements.rename_document.value) {
                openRenameModal(
                    renameForm.elements.rename_document.value,
                    renameForm.elements.original_name.value
                );
            }

            const folderModal = document.getElementById('folder-modal');

            if (folderModal?.hasAttribute('data-open')) {
                openModal(folderModal);
            }

            const folderRenameModal = document.getElementById('folder-rename-modal');
            const folderRenameForm = document.getElementById('folder-rename-form');

            const openFolderRenameModal = (folderId, folderName) => {
                if (!folderRenameModal || !folderRenameForm) return;

                folderRenameForm.action = folderRenameModal.dataset.actionTemplate.replace('__ID__', folderId);
                folderRenameForm.elements.name.value = folderName ?? '';
                folderRenameForm.elements.rename_folder.value = folderId;
                openModal(folderRenameModal);
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-folder-rename-open]');

                if (!trigger) return;

                closeDropdowns();
                openFolderRenameModal(trigger.dataset.folderId, trigger.dataset.folderName);
            });

            if (folderRenameModal?.hasAttribute('data-open') && folderRenameForm.elements.rename_folder.value) {
                openFolderRenameModal(
                    folderRenameForm.elements.rename_folder.value,
                    folderRenameForm.elements.name.value
                );
            }

            const uploadModal = document.getElementById('upload-modal');

            if (uploadModal?.hasAttribute('data-open')) {
                openModal(uploadModal);
            }

            const searchForm = qs('[data-search-form]');
            const searchInput = qs('[data-search-input]');

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

            const selectionBar = document.getElementById('selection-bar');
            const selectionCount = document.getElementById('selection-count');
            const listSummary = qs('[data-list-summary]');
            const renameSelectionButton = qs('[data-bulk-rename]');
            let lastSelectedIndex = null;

            const selectionBoxes = () => qsa('[data-select]');
            const selectedIds = () => selectionBoxes().filter((box) => box.checked).map((box) => box.value);

            function syncSelection() {
                const boxes = selectionBoxes();
                const checked = boxes.filter((box) => box.checked);
                const total = checked.length;

                boxes.forEach((box) => {
                    const row = box.closest('[data-row]');
                    if (!row) return;

                    row.classList.toggle('bg-indigo-50', box.checked);
                    row.classList.toggle('dark:bg-indigo-500/10', box.checked);
                });

                const selectAll = qs('[data-select-all]');

                if (selectAll) {
                    selectAll.checked = boxes.length > 0 && total === boxes.length;
                    selectAll.indeterminate = total > 0 && total < boxes.length;
                }

                if (selectionBar) {
                    selectionBar.classList.toggle('hidden', total === 0);
                    selectionBar.classList.toggle('flex', total > 0);
                }

                if (listSummary) listSummary.classList.toggle('hidden', total > 0);
                if (selectionCount) {
                    selectionCount.textContent = total === 1
                        ? '1 arquivo selecionado'
                        : total + ' arquivos selecionados';
                }
                if (renameSelectionButton) renameSelectionButton.disabled = total !== 1;
            }

            function clearSelection() {
                selectionBoxes().forEach((box) => {
                    box.checked = false;
                });

                syncSelection();
            }

            document.addEventListener('change', (event) => {
                const box = event.target.closest('[data-select]');

                if (box) {
                    const boxes = selectionBoxes();
                    const index = boxes.indexOf(box);

                    if (event.shiftKey && lastSelectedIndex !== null) {
                        const [from, to] = [lastSelectedIndex, index].sort((a, b) => a - b);

                        for (let i = from; i <= to; i++) {
                            boxes[i].checked = box.checked;
                        }
                    }

                    lastSelectedIndex = index;
                    syncSelection();
                    return;
                }

                if (event.target.closest('[data-select-all]')) {
                    const isChecked = event.target.checked;

                    selectionBoxes().forEach((item) => {
                        item.checked = isChecked;
                    });

                    syncSelection();
                }
            });

            document.addEventListener('click', (event) => {
                if (event.target.closest('a, button, input, label, form, [role="dialog"]')) return;

                const row = event.target.closest('[data-row]');

                if (!row || window.getSelection().toString() !== '') return;

                const box = row.querySelector('[data-select]');

                if (!box) return;

                box.checked = !box.checked;
                syncSelection();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;

                closeDropdowns();
                qsa('[role="dialog"]:not(.hidden)').forEach(closeModal);

                if (selectedIds().length > 0) {
                    clearSelection();
                }
            });

            qs('[data-selection-clear]')?.addEventListener('click', clearSelection);

            qs('[data-bulk-link]')?.addEventListener('click', () => {
                const ids = selectedIds();
                const form = document.getElementById('bulk-generate-link-form');

                if (!ids.length || !form) return;

                qs('[data-bulk-ids]', form).innerHTML = ids
                    .map((id) => '<input type="hidden" name="document_ids[]" value="' + id + '">')
                    .join('');

                form.submit();
            });

            qs('[data-bulk-rename]')?.addEventListener('click', () => {
                const ids = selectedIds();

                if (ids.length !== 1) return;

                const row = qs('[data-row][data-document-id="' + ids[0] + '"]');
                openRenameModal(ids[0], row?.dataset.documentName);
            });

            qs('[data-bulk-delete]')?.addEventListener('click', async () => {
                const ids = selectedIds();
                const button = qs('[data-bulk-delete]');
                const template = qs('[data-delete-template]')?.dataset.deleteTemplate;
                const token = qs('input[name="_token"]')?.value;

                if (!ids.length || !template || !token) return;
                if (!window.confirm('Mover ' + ids.length + ' arquivo(s) para a lixeira?')) return;

                button.disabled = true;

                try {
                    for (const id of ids) {
                        const response = await fetch(template.replace('__ID__', id), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                Accept: 'application/json',
                            },
                        });

                        if (!response.ok) throw new Error('Falha ao excluir');
                    }

                    window.location.reload();
                } catch (error) {
                    window.alert('Não foi possível mover todos os arquivos. Tente novamente.');
                    button.disabled = false;
                }
            });

            const moveModal = document.getElementById('move-modal');
            const moveForm = document.getElementById('move-form');
            const movePath = document.getElementById('move-path');
            const moveList = document.getElementById('move-list');
            const moveFolderInput = document.getElementById('move-folder-input');
            const moveTree = JSON.parse(document.getElementById('folder-tree')?.textContent || '[]');
            let moveStack = [];

            const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));

            const folderGlyph = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="h-5 w-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>';

            const moveChildren = () => {
                let level = moveTree;

                for (const node of moveStack) {
                    const found = level.find((item) => item.id === node.id);

                    if (!found) return [];
                    level = found.children;
                }

                return level;
            };

            const renderMovePicker = () => {
                if (!moveModal || !movePath || !moveList || !moveFolderInput) return;

                moveFolderInput.value = moveStack.length ? moveStack[moveStack.length - 1].id : '';

                const crumbs = ['Meus arquivos'].concat(moveStack.map((node) => node.name));

                movePath.innerHTML = crumbs.map((label, index) => {
                    const separator = index > 0 ? '<span aria-hidden="true">/</span>' : '';

                    if (index === moveStack.length) {
                        return separator + '<span class="font-medium text-gray-900 dark:text-gray-100">' + escapeHtml(label) + '</span>';
                    }

                    return separator + '<button type="button" data-move-level="' + index + '" class="hover:text-gray-900 dark:hover:text-white">' + escapeHtml(label) + '</button>';
                }).join('');

                const children = moveChildren();

                moveList.innerHTML = children.length
                    ? children.map((node) => (
                        '<button type="button" data-move-enter="' + node.id + '" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">'
                        + '<span class="text-amber-500 dark:text-amber-400">' + folderGlyph + '</span>'
                        + '<span class="truncate">' + escapeHtml(node.name) + '</span>'
                        + '</button>'
                    )).join('')
                    : '<p class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma subpasta aqui. O arquivo irá para esta pasta.</p>';
            };

            movePath?.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-move-level]');

                if (!trigger) return;

                moveStack = moveStack.slice(0, Number(trigger.dataset.moveLevel));
                renderMovePicker();
            });

            moveList?.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-move-enter]');

                if (!trigger) return;

                const node = moveChildren().find((item) => item.id === Number(trigger.dataset.moveEnter));

                if (node) {
                    moveStack.push(node);
                    renderMovePicker();
                }
            });

            qs('[data-bulk-move]')?.addEventListener('click', () => {
                const ids = selectedIds();
                const idHolder = qs('[data-move-ids]', moveForm);

                if (!ids.length || !moveModal || !idHolder) return;

                idHolder.innerHTML = ids
                    .map((id) => '<input type="hidden" name="document_ids[]" value="' + id + '">')
                    .join('');

                moveStack = [];
                renderMovePicker();
                openModal(moveModal);
            });

            qsa('[data-row][data-document-id]').forEach((row) => {
                row.setAttribute('draggable', 'true');

                row.addEventListener('dragstart', (event) => {
                    const box = row.querySelector('[data-select]');
                    const ids = box && box.checked ? selectedIds() : [row.dataset.documentId];

                    event.dataTransfer.setData('application/x-move-ids', ids.join(','));
                    event.dataTransfer.effectAllowed = 'move';
                    row.classList.add('opacity-50');
                });

                row.addEventListener('dragend', () => row.classList.remove('opacity-50'));
            });

            const isInternalMove = (event) => Array.from(event.dataTransfer?.types || []).includes('application/x-move-ids');
            const dropClasses = ['border-indigo-400', 'bg-indigo-50', 'dark:border-indigo-500', 'dark:bg-indigo-500/10'];

            document.addEventListener('dragover', (event) => {
                const target = event.target.closest('[data-folder-row]');

                if (!target || !isInternalMove(event)) return;

                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                target.classList.add(...dropClasses);
            });

            document.addEventListener('dragleave', (event) => {
                const target = event.target.closest('[data-folder-row]');

                target?.classList.remove(...dropClasses);
            });

            document.addEventListener('drop', async (event) => {
                const target = event.target.closest('[data-folder-row]');
                const ids = event.dataTransfer?.getData('application/x-move-ids');

                if (!target || !ids) return;

                event.preventDefault();
                target.classList.remove(...dropClasses);

                const token = qs('input[name="_token"]')?.value;

                if (!token) return;

                try {
                    const response = await fetch('{{ route('admin.files.move') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({
                            document_ids: ids.split(','),
                            folder: target.dataset.folderId,
                        }),
                    });

                    if (!response.ok) throw new Error('Falha ao mover');

                    window.location.reload();
                } catch (error) {
                    window.alert('Não foi possível mover os arquivos para esta pasta.');
                }
            });

            const uploadForm = document.getElementById('upload-form');
            const filePicker = document.getElementById('file-picker');
            const dropOverlay = document.getElementById('drop-overlay');

            qsa('[data-upload-trigger]').forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    if (!filePicker) return;

                    closeDropdowns();
                    filePicker.value = '';
                    filePicker.click();
                });
            });

            filePicker?.addEventListener('change', () => {
                if (!filePicker.files.length) return;

                if (filePicker.files.length > 10) {
                    window.alert('Selecione no máximo 10 arquivos por envio.');
                    filePicker.value = '';
                    return;
                }

                uploadForm.submit();
            });

            const hasFiles = (event) => event.dataTransfer
                && Array.from(event.dataTransfer.types || []).includes('Files');

            const showDropOverlay = () => {
                if (dropOverlay) dropOverlay.style.display = 'flex';
            };

            const hideDropOverlay = () => {
                if (dropOverlay) dropOverlay.style.display = 'none';
            };

            let dragDepth = 0;

            window.addEventListener('dragenter', (event) => {
                if (!hasFiles(event)) return;

                event.preventDefault();
                dragDepth += 1;
                showDropOverlay();
            });

            window.addEventListener('dragover', (event) => {
                if (hasFiles(event)) event.preventDefault();
            });

            window.addEventListener('dragleave', (event) => {
                dragDepth = event.relatedTarget === null ? 0 : Math.max(0, dragDepth - 1);

                if (dragDepth === 0) hideDropOverlay();
            });

            window.addEventListener('drop', (event) => {
                if (!hasFiles(event)) return;

                event.preventDefault();
                dragDepth = 0;
                hideDropOverlay();

                if (!event.dataTransfer.files.length || !filePicker || !uploadForm) return;

                const transfer = new DataTransfer();

                Array.from(event.dataTransfer.files).forEach((file) => transfer.items.add(file));

                filePicker.files = transfer.files;
                uploadForm.submit();
            });
        })();
    </script>
</x-layouts.admin>
