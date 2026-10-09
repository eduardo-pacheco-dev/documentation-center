<section class="mt-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            <x-icon name="users" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Contatos
        </h2>

        <button
            type="button"
            data-modal-open="contact-create-modal"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="user-plus" class="h-4 w-4" />
            Adicionar contato
        </button>
    </div>

    @if ($client->contacts->isEmpty())
        <p class="mt-3 rounded-xl border border-dashed border-gray-300 bg-white px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            Nenhum contato cadastrado.
        </p>
    @else
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($client->contacts as $contact)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-1.5 font-medium text-gray-900 dark:text-gray-100">
                                {{ $contact->name }}
                                @if ($contact->is_primary)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                        <x-icon name="star" class="h-3 w-3" />
                                        Principal
                                    </span>
                                @endif
                            </p>
                            @if ($contact->position)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $contact->position }}</p>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                data-modal-open="contact-edit-modal-{{ $contact->getKey() }}"
                                class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                aria-label="Editar contato"
                            >
                                <x-icon name="pencil-square" class="h-4 w-4" />
                            </button>

                            <form method="POST" action="{{ route('admin.clients.contacts.destroy', [$client, $contact]) }}" data-confirm-delete="Tem certeza que deseja excluir este contato?">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-950 dark:hover:text-red-400"
                                    aria-label="Remover contato"
                                >
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($contact->phone || $contact->email)
                        <dl class="mt-3 space-y-1.5 text-sm">
                            @if ($contact->phone)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <x-icon name="phone" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    <a href="tel:{{ preg_replace('/\D/', '', $contact->phone) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $contact->phone }}</a>
                                </div>
                            @endif
                            @if ($contact->email)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <x-icon name="envelope" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    <a href="mailto:{{ $contact->email }}" class="truncate hover:text-indigo-600 dark:hover:text-indigo-400">{{ $contact->email }}</a>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div
        id="contact-create-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="contact-create-modal-title"
        @if ($errors->contact->any() && old('modal') === 'contact-create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="contact-create-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="user-plus" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Novo contato
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informe os dados do contato do cliente.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('admin.clients.contacts.store', $client) }}" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="contact-create">

                @include('admin.clients.partials.contact-form', ['contact' => null])

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Adicionar contato
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($client->contacts as $contact)
        <div
            id="contact-edit-modal-{{ $contact->getKey() }}"
            class="fixed inset-0 z-50 hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="contact-edit-modal-title-{{ $contact->getKey() }}"
            @if ($errors->contact->any() && old('modal') === 'contact-edit' && (int) old('contact_id') === $contact->getKey()) data-open @endif
        >
            <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

            <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="contact-edit-modal-title-{{ $contact->getKey() }}" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                            <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                            Editar contato
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados de {{ $contact->name }}.</p>
                    </div>

                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                        data-modal-close
                    >
                        <span class="sr-only">Fechar</span>
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.clients.contacts.update', [$client, $contact]) }}" class="mt-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="modal" value="contact-edit">
                    <input type="hidden" name="contact_id" value="{{ $contact->getKey() }}">

                    @include('admin.clients.partials.contact-form', ['contact' => $contact])

                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            data-modal-close
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                        >
                            <x-icon name="check" class="h-4 w-4" />
                            Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</section>
