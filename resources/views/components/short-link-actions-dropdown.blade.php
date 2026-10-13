@props(['shortLink'])

<div class="relative inline-block text-left" data-link-actions>
    <button
        type="button"
        class="rounded-full p-1.5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100"
        aria-haspopup="true"
        aria-expanded="false"
        data-dropdown-toggle
    >
        <span class="sr-only">Abrir menu de ações</span>
        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
    </button>

    <div
        class="absolute right-0 z-10 mt-1 hidden w-36 origin-top-right rounded-md bg-white dark:bg-gray-900 py-1 text-left shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10"
        role="menu"
        data-dropdown-menu
    >
        <button
            type="button"
            data-link-details-open
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="eye" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Detalhes
        </button>

        <button
            type="button"
            data-link-modal-open
            data-link-url="{{ $shortLink->url }}"
            data-link-code="{{ $shortLink->code }}"
            data-link-title="{{ $shortLink->title }}"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="link" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Link
        </button>

        <button
            type="button"
            data-modal-open="link-edit-modal-{{ $shortLink->getKey() }}"
            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
            role="menuitem"
        >
            <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            Editar
        </button>

        <form method="POST" action="{{ route('admin.links.destroy', $shortLink) }}">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15"
                role="menuitem"
                onclick="return confirm('Excluir o link {{ $shortLink->code }}?')"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir
            </button>
        </form>
    </div>

    <template data-link-details-template>
        <header class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 class="flex items-center gap-2 text-base font-semibold">
                    <x-icon name="link" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Detalhes do link
                </h2>
                <p class="mt-0.5 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $shortLink->code }}</p>
            </div>

            <button
                type="button"
                data-link-details-close
                class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </header>

        <div class="space-y-4 px-5 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <x-short-link-type :type="$shortLink->type" />
                <x-short-link-status :short-link="$shortLink" />

                @if ($shortLink->needsPassword())
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <x-icon name="user" class="h-3.5 w-3.5" />
                        Com senha
                    </span>
                @endif
            </div>

            <dl class="grid gap-x-4 gap-y-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Título</dt>
                    <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $shortLink->title }}</dd>
                </div>

                @if ($shortLink->description)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Descrição</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $shortLink->description }}</dd>
                    </div>
                @endif

                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">URL pública</dt>
                    <dd class="mt-0.5">
                        <a
                            href="{{ $shortLink->url }}"
                            target="_blank"
                            rel="noopener"
                            class="break-all font-mono text-sm text-indigo-600 hover:underline dark:text-indigo-300"
                        >{{ $shortLink->url }}</a>
                    </dd>
                </div>

                @if ($shortLink->isRedirect())
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Redireciona para</dt>
                        <dd class="mt-0.5 break-all text-sm text-gray-900 dark:text-gray-100">{{ $shortLink->redirect_url }}</dd>
                    </div>
                @endif

                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acessos</dt>
                    <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $shortLink->used_count }} / {{ $shortLink->max_uses ?? '∞' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Documentos</dt>
                    <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $shortLink->documents_count + $shortLink->received_documents_count }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Criado em</dt>
                    <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $shortLink->created_at->format('d/m/Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Expira em</dt>
                    <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $shortLink->expires_at?->format('d/m/Y H:i') ?? 'Nunca' }}</dd>
                </div>
            </dl>
        </div>

        <footer class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            <button
                type="button"
                data-link-details-close
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Fechar
            </button>

            <button
                type="button"
                data-modal-open="link-edit-modal-{{ $shortLink->getKey() }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </button>
        </footer>
    </template>
</div>