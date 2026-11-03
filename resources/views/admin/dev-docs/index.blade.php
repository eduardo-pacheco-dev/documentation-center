<x-layouts.admin title="Wiki de Desenvolvimento">
    @php
        $hasFilters = $search !== '' || $category !== null;
    @endphp

    <div class="grid gap-6 lg:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
        <x-admin.dev-docs.wiki-sidebar
            :navigation="$navigation"
            :search="$search"
            :category="$category"
        />

        <div class="min-w-0 space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Documentação Dev</p>
                    <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-gray-100">
                        <x-icon name="document-text" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                        Wiki de Desenvolvimento
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Um espaço colaborativo para documentar a arquitetura, os padrões e os processos do projeto.
                    </p>
                </div>

                <a href="{{ route('admin.dev-docs.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <x-icon name="plus" class="h-4 w-4" />
                    Nova página
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-gray-200/80 bg-white px-4 py-2.5 text-xs text-gray-500 dark:border-white/10 dark:bg-[#171716] dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
                    páginas
                </span>

                <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

                <span class="inline-flex items-center gap-1.5">
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $stats['published'] }}</span>
                    publicadas
                </span>

                <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

                <span class="inline-flex items-center gap-1.5">
                    <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $stats['drafts'] }}</span>
                    rascunhos
                </span>

                <span class="hidden h-3 w-px bg-gray-200 sm:block dark:bg-white/10"></span>

                <span class="inline-flex items-center gap-1.5">
                    <span class="font-semibold text-violet-600 dark:text-violet-400">{{ $stats['categories'] }}</span>
                    categorias
                </span>
            </div>

            @unless ($hasFilters)
                @if ($navigation->isNotEmpty())
                    <section>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Navegar por categoria</h2>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ($navigation as $group)
                                <a href="{{ route('admin.dev-docs.index', ['category' => $group['category']->value]) }}" class="group rounded-2xl border border-gray-200/80 bg-white p-4 transition hover:border-indigo-200 dark:border-white/10 dark:bg-[#171716] dark:hover:border-indigo-500/40">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                            <x-icon name="document-text" class="h-5 w-5" />
                                        </span>
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">{{ $group['documents']->count() }}</span>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $group['category']->label() }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $group['documents']->first()?->title }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endunless

            <section>
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ $hasFilters ? 'Resultados' : 'Atualizados recentemente' }}
                        </h2>

                        @if ($search !== '')
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Busca por “{{ $search }}”</p>
                        @elseif ($category !== null)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Categoria: {{ $category->label() }}</p>
                        @endif
                    </div>

                    @if ($hasFilters)
                        <a href="{{ route('admin.dev-docs.index') }}" class="text-xs font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Limpar filtros</a>
                    @endif
                </div>

                <div class="mt-3 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
                    <div class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse ($documents as $document)
                            <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                            {{ $document->category->label() }}
                                        </span>

                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $document->is_published,
                                            'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => ! $document->is_published,
                                        ])>
                                            {{ $document->is_published ? 'Publicado' : 'Rascunho' }}
                                        </span>
                                    </div>

                                    <h3 class="mt-2 text-base font-semibold text-gray-900 dark:text-white">
                                        <a href="{{ route('admin.dev-docs.show', $document) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                            {{ $document->title }}
                                        </a>
                                    </h3>

                                    @if ($document->summary)
                                        <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">{{ $document->summary }}</p>
                                    @endif

                                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                        {{ $document->author?->full_name ?? 'Autor removido' }}
                                        · atualizado <x-relative-time :value="$document->updated_at" />
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <a href="{{ route('admin.dev-docs.show', $document) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:text-gray-400 dark:hover:border-indigo-500/40 dark:hover:text-indigo-400" aria-label="Visualizar {{ $document->title }}">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>

                                    <a href="{{ route('admin.dev-docs.edit', $document) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:text-gray-400 dark:hover:border-indigo-500/40 dark:hover:text-indigo-400" aria-label="Editar {{ $document->title }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.dev-docs.destroy', $document) }}" data-confirm-delete="Excluir a página “{{ $document->title }}”? Essa ação não pode ser desfeita.">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-red-200 hover:text-red-600 dark:border-white/10 dark:text-gray-400 dark:hover:border-red-500/40 dark:hover:text-red-400" aria-label="Excluir {{ $document->title }}">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-16 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                    <x-icon name="document-text" class="h-6 w-6" />
                                </span>
                                <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $hasFilters ? 'Nenhuma página encontrada' : 'Nenhuma página cadastrada' }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $hasFilters ? 'Ajuste a busca ou os filtros para ver mais resultados.' : 'Crie a primeira página para começar.' }}
                                </p>
                            </div>
                        @endforelse
                    </div>

                    @if ($documents->hasPages())
                        <div class="border-t border-gray-100 p-4 dark:border-white/10">
                            <x-pagination :paginator="$documents" />
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts.admin>
