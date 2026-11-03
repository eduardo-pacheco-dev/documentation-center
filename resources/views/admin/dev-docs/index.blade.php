<x-layouts.admin title="Documentação Dev">
    @php
        $inputClass = 'h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
        $selectClass = 'h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
        $primaryButton = 'inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500';
        $secondaryButton = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
        $hasFilters = $search !== '' || $category !== null;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Administração</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="document-text" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Documentação Dev
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Documente a arquitetura, os padrões e os processos do projeto.</p>
        </div>

        <a href="{{ route('admin.dev-docs.create') }}" class="{{ $primaryButton }}">
            <x-icon name="plus" class="h-4 w-4" />
            Novo documento
        </a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="document-text" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Documentos</p>
        </div>

        <div class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['published'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Publicados</p>
        </div>

        <div class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="pencil-square" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['drafts'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rascunhos</p>
        </div>

        <div class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="squares-2x2" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['categories'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Categorias em uso</p>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form method="GET" action="{{ route('admin.dev-docs.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por título, resumo ou conteúdo"
                    aria-label="Buscar documentos"
                    class="{{ $inputClass }} pl-10"
                >
            </div>

            <select name="category" aria-label="Filtrar por categoria" class="{{ $selectClass }} w-full sm:w-56">
                <option value="">Todas as categorias</option>
                @foreach ($categories as $option)
                    <option value="{{ $option->value }}" @selected($category === $option)>{{ $option->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="{{ $secondaryButton }}">
                <x-icon name="magnifying-glass" class="h-4 w-4" />
                Filtrar
            </button>

            @if ($hasFilters)
                <a href="{{ route('admin.dev-docs.index') }}" class="text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Limpar filtros
                </a>
            @endif
        </form>
    </section>

    <section class="mt-4 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
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

                        <h2 class="mt-2 text-base font-semibold text-gray-900 dark:text-white">
                            <a href="{{ route('admin.dev-docs.show', $document) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $document->title }}
                            </a>
                        </h2>

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

                        <form method="POST" action="{{ route('admin.dev-docs.destroy', $document) }}" data-confirm-delete="Excluir o documento “{{ $document->title }}”? Essa ação não pode ser desfeita.">
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
                        {{ $hasFilters ? 'Nenhum documento encontrado' : 'Nenhum documento cadastrado' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $hasFilters ? 'Ajuste a busca ou os filtros para ver mais resultados.' : 'Crie o primeiro documento para começar.' }}
                    </p>
                </div>
            @endforelse
        </div>

        @if ($documents->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-white/10">
                <x-pagination :paginator="$documents" />
            </div>
        @endif
    </section>
</x-layouts.admin>
