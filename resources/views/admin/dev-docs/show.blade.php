<x-layouts.admin title="{{ $document->title }}">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ route('admin.dev-docs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                Voltar para a documentação
            </a>

            <div class="mt-3 flex flex-wrap items-center gap-2">
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

            <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $document->title }}</h1>

            @if ($document->summary)
                <p class="mt-2 max-w-3xl text-sm text-gray-500 dark:text-gray-400">{{ $document->summary }}</p>
            @endif

            <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                {{ $document->author?->full_name ?? 'Autor removido' }}
                · criado <x-relative-time :value="$document->created_at" />
                · atualizado <x-relative-time :value="$document->updated_at" />
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.dev-docs.edit', $document) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

            <form method="POST" action="{{ route('admin.dev-docs.destroy', $document) }}" data-confirm-delete="Excluir o documento “{{ $document->title }}”? Essa ação não pode ser desfeita.">
                @csrf
                @method('DELETE')

                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/40">
                    <x-icon name="trash" class="h-4 w-4" />
                    Excluir
                </button>
            </form>
        </div>
    </div>

    <article class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-8 dark:border-white/10 dark:bg-[#171716]">
        <div class="dev-doc-content">
            {!! $renderedContent !!}
        </div>
    </article>
</x-layouts.admin>
