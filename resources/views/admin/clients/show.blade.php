<x-layouts.admin :title="$client->name">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="building-office" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $client->name }}
            </h1>
            <div class="mt-2 flex items-center gap-3">
                <x-client-status :client="$client" />
                @if ($client->document)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $client->document }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                data-modal-open="client-edit-modal"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </button>

            <a
                href="{{ route('admin.clients.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Contato</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">E-mail</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        @if ($client->email)
                            <a href="mailto:{{ $client->email }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $client->email }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Telefone</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $client->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Site</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        @if ($client->website)
                            <a href="{{ $client->website }}" target="_blank" rel="noopener" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $client->website }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Endereço</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Logradouro</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        @if ($client->street)
                            {{ $client->street }}, {{ $client->number }}@if ($client->complement) — {{ $client->complement }} @endif
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Bairro</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $client->neighborhood ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Cidade / UF</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $client->city ? $client->city.($client->state ? ' / '.$client->state : '') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">CEP</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $client->zip ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Observações</h2>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ $client->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $client->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $client->updated_at->format('d/m/Y H:i') }}
    </div>

    <div
        id="client-edit-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="client-edit-modal-title"
        @if ($errors->any() && old('modal') === 'edit') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="client-edit-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="pencil-square" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Editar cliente
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados de contato e o endereço do cliente.</p>
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

            <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="mt-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="modal" value="edit">

                @include('admin.clients.partials.form', ['client' => $client])

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

    <script>
        (function () {
            const openModal = (modal) => {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                const error = modal.querySelector('[data-modal-error]');
                if (error) {
                    error.scrollIntoView({ block: 'center' });
                }
            };

            const closeModal = (modal) => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            document.addEventListener('click', (event) => {
                const opener = event.target.closest('[data-modal-open]');

                if (opener) {
                    event.preventDefault();

                    const modal = document.getElementById(opener.dataset.modalOpen);
                    if (modal) {
                        openModal(modal);
                    }

                    return;
                }

                const closer = event.target.closest('[data-modal-close]');
                if (closer) {
                    closeModal(closer.closest('[role="dialog"]'));
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
                }
            });

            const editModal = document.getElementById('client-edit-modal');
            if (editModal?.hasAttribute('data-open')) {
                openModal(editModal);
            }
        })();
    </script>
</x-layouts.admin>
