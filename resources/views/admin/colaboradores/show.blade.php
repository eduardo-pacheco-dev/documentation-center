<x-layouts.admin :title="$colaborador->name">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="user" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $colaborador->name }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-colaborador-status :colaborador="$colaborador" />
                @if ($colaborador->role)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $colaborador->role }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.colaboradores.edit', $colaborador) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

            <a
                href="{{ route('admin.colaboradores.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Dados pessoais</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Nome</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Função</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->role ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Data de nascimento</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->birth_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Nome da mãe</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->mother_name ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd><x-colaborador-status :colaborador="$colaborador" /></dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Documentos</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">CPF</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->document ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">CNPJ</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->cnpj ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">PIS</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->pis ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">RG</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->rg ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Órgão emissor</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->rg_issuer ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Contrato e lotação</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Regime de contrato</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->contract_regime?->value ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Regional</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->regional ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">UF</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->uf?->value ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <x-icon name="phone" class="h-3.5 w-3.5" />
                        Contato
                    </dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->phone ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <x-icon name="envelope" class="h-3.5 w-3.5" />
                        E-mail
                    </dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $colaborador->email ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-3 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Observações</h2>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ $colaborador->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $colaborador->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $colaborador->updated_at->format('d/m/Y H:i') }}
    </div>
</x-layouts.admin>