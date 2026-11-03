<x-layouts.admin title="{{ $station->station_id ?: $station->site_id }}">
    @php
        $statusTone = match (mb_strtolower((string) $station->status)) {
            'aquisitado', 'ativo', 'ativa' => 'green',
            'em análise', 'em analise', 'pendente' => 'amber',
            'cancelado', 'cancelada' => 'red',
            default => 'gray',
        };

        $groups = [
            [
                'title' => 'Identificação',
                'fields' => [
                    'SITE_ID' => $station->site_id,
                    'STATION_ID' => $station->station_id,
                    'ENDERECO_ID' => $station->endereco_id,
                    'TIPO_DE_ELEMENTO' => $station->tipo_de_elemento,
                    'TECNOLOGIA' => $station->tecnologia,
                    'TIPO_DE_CONEXAO' => $station->tipo_de_conexao,
                    'CLASSIFICACAO' => $station->classificacao,
                ],
            ],
            [
                'title' => 'Infraestrutura',
                'fields' => [
                    'DETENTOR_AREA' => $station->detentor_area,
                    'TIPO_DE_INFRA' => $station->tipo_de_infra,
                    'TIPO_DE_EV' => $station->tipo_de_ev,
                    'TIPO_DA_TORRE' => $station->tipo_da_torre,
                    'REGIONAL' => $station->regional,
                ],
            ],
            [
                'title' => 'Endereço',
                'fields' => [
                    'TIPO_DE_LOGRADOURO' => $station->tipo_de_logradouro,
                    'LOGRADOURO' => $station->logradouro,
                    'NUMERO' => $station->numero,
                    'COMPLEMENTO' => $station->complemento,
                    'BAIRRO' => $station->bairro,
                    'MUNICIPIO' => $station->municipio,
                    'ESTADO' => $station->estado,
                    'CEP' => $station->cep,
                ],
            ],
            [
                'title' => 'Localização',
                'fields' => [
                    'LATITUDE' => $station->latitude,
                    'LONGITUDE' => $station->longitude,
                ],
            ],
        ];
    @endphp

    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.stations.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Estações</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">{{ $station->station_id ?: $station->site_id }}</span>
    </nav>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge :tone="$statusTone">{{ $station->status ?: 'Sem status' }}</x-ui.badge>

                @if ($station->tecnologia)
                    <x-ui.badge tone="indigo">{{ $station->tecnologia }}</x-ui.badge>
                @endif
            </div>

            <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                {{ $station->station_id ?: $station->site_id }}
            </h1>

            <p class="mt-2 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
                {{ $station->address() ?: 'Endereço não informado' }}
            </p>

            <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                criado <x-relative-time :value="$station->created_at" />
                · atualizado <x-relative-time :value="$station->updated_at" />
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.action :href="route('admin.stations.edit', $station)" icon="pencil-square">
                Editar
            </x-ui.action>

            <form method="POST" action="{{ route('admin.stations.destroy', $station) }}" data-confirm-delete="Excluir a estação {{ $station->station_id ?: $station->site_id }}? Essa ação não pode ser desfeita.">
                @csrf
                @method('DELETE')

                <button type="submit" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/40">
                    <x-icon name="trash" class="h-4 w-4" />
                    Excluir
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-2">
        @foreach ($groups as $group)
            <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ $group['title'] }}</h2>

                <dl class="mt-4 divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($group['fields'] as $label => $value)
                        <div class="grid grid-cols-[10rem_minmax(0,1fr)] gap-3 py-2.5 text-sm">
                            <dt class="font-mono text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $label }}</dt>
                            <dd class="min-w-0 break-words text-gray-700 dark:text-gray-200">{{ filled($value) ? $value : '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.stations.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Voltar para as estações
        </a>
    </div>
</x-layouts.admin>
