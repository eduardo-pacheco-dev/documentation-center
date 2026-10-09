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
            <a
                href="{{ route('admin.clients.edit', $client) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

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
</x-layouts.admin>
