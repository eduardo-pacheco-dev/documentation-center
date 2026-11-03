@props([
    'navigation',
    'search' => '',
    'category' => null,
    'currentId' => null,
])

<aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">
    <form method="GET" action="{{ route('admin.dev-docs.index') }}" class="relative">
        <label for="wiki-search" class="sr-only">Buscar na wiki</label>
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
        <input
            id="wiki-search"
            type="search"
            name="search"
            value="{{ $search }}"
            placeholder="Buscar na wiki..."
            class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
        >
    </form>

    <nav class="rounded-3xl border border-gray-200/80 bg-white p-3 dark:border-white/10 dark:bg-[#171716]" aria-label="Páginas da wiki">
        <p class="px-2 pb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Wiki</p>

        <a
            href="{{ route('admin.dev-docs.index') }}"
            @class([
                'flex items-center gap-2 rounded-xl px-2.5 py-2 text-sm font-medium transition',
                'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' => $search === '' && $category === null && $currentId === null,
                'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' => $search !== '' || $category !== null || $currentId !== null,
            ])
        >
            <x-icon name="home" class="h-4 w-4 shrink-0" />
            Início da wiki
        </a>

        @foreach ($navigation as $group)
            <div class="mt-3">
                <a
                    href="{{ route('admin.dev-docs.index', ['category' => $group['category']->value]) }}"
                    @class([
                        'flex items-center gap-2 rounded-xl px-2.5 py-2 text-sm font-medium transition',
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' => $category === $group['category'],
                        'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' => $category !== $group['category'],
                    ])
                >
                    <x-icon name="document-text" class="h-4 w-4 shrink-0" />
                    <span class="min-w-0 flex-1 truncate">{{ $group['category']->label() }}</span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $group['documents']->count() }}</span>
                </a>

                <div class="ml-4 mt-1 space-y-0.5 border-l border-gray-100 pl-3 dark:border-white/10">
                    @foreach ($group['documents'] as $navigationDocument)
                        <a
                            href="{{ route('admin.dev-docs.show', $navigationDocument) }}"
                            @class([
                                'flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs transition',
                                'bg-indigo-50 font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' => $currentId === $navigationDocument->getKey(),
                                'text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' => $currentId !== $navigationDocument->getKey(),
                            ])
                        >
                            <span class="min-w-0 flex-1 truncate">{{ $navigationDocument->title }}</span>
                            @unless ($navigationDocument->is_published)
                                <span class="shrink-0 rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">Rascunho</span>
                            @endunless
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($navigation->isEmpty())
            <p class="px-2.5 py-3 text-xs text-gray-400 dark:text-gray-500">Nenhuma página ainda.</p>
        @endif
    </nav>

    <a href="{{ route('admin.dev-docs.create') }}" class="flex h-10 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500">
        <x-icon name="plus" class="h-4 w-4" />
        Nova página
    </a>
</aside>
