<x-layouts.admin :title="$station->name">
    @php
        $cardClass = 'rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]';
    @endphp

    <x-ui.page-header
        category="Station {{ $station->code }}"
        :title="$station->name"
        :description="$station->city ? $station->city.($station->state ? ' / '.$station->state : '') : 'Detalhes da station.'"
        icon="signal"
    >
        <x-ui.action :href="route('stations.edit', $station)" variant="secondary" icon="pencil-square">
            Editar
        </x-ui.action>

        <x-ui.action :href="route('stations.index')" variant="ghost" icon="arrow-uturn-left">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-7 grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <div class="space-y-4">
            <section class="{{ $cardClass }} p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Identificação</h2>

                    <x-ui.badge :tone="$station->status->tone()">{{ $station->status->label() }}</x-ui.badge>
                </div>

                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Site ID</dt>
                        <dd class="mt-0.5 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $station->site_id ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">End ID</dt>
                        <dd class="mt-0.5 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $station->end_id ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Operadora</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $station->operator ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Tecnologia</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $station->technology ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Responsável</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $station->assigned_to ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Nokia SMP ID</dt>
                        <dd class="mt-0.5 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $station->nokia_smp_id ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Início</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $station->start_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Término</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">{{ $station->end_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Endereço</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 dark:text-gray-100">
                            {{ collect([$station->street, $station->number, $station->complement])->filter()->implode(', ') ?: '—' }}
                            @if ($station->neighborhood)
                                · {{ $station->neighborhood }}
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Observações</dt>
                        <dd class="mt-0.5 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $station->notes ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            @include('stations::partials.links', ['station' => $station, 'stationOptions' => $stationOptions, 'linkStatuses' => $linkStatuses])

            @include('stations::partials.documents', ['station' => $station])

            @include('stations::partials.comments', ['station' => $station])
        </div>

        <aside class="space-y-4">
            <section class="{{ $cardClass }} p-5">
                <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Localização</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">CEP</dt>
                        <dd class="mt-0.5 font-mono">{{ $station->zip ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Coordenadas</dt>
                        <dd class="mt-0.5 font-mono">
                            {{ $station->latitude ?: '—' }}{{ $station->latitude && $station->longitude ? ', '.$station->longitude : '' }}
                        </dd>
                    </div>
                </dl>

                @if ($station->latitude && $station->longitude)
                    <x-ui.action
                        :href="'https://www.google.com/maps/search/?api=1&query='.$station->latitude.','.$station->longitude"
                        variant="secondary"
                        icon="map-pin"
                        class="mt-4 w-full"
                    >
                        Abrir no mapa
                    </x-ui.action>
                @endif
            </section>

            <section class="{{ $cardClass }} p-5">
                <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Resumo</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-gray-500 dark:text-gray-400">Documentos</dt>
                        <dd class="font-medium">{{ $station->documents->count() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-gray-500 dark:text-gray-400">Comentários</dt>
                        <dd class="font-medium">{{ $station->comments->count() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-gray-500 dark:text-gray-400">Enlaces</dt>
                        <dd class="font-medium">{{ $station->linksAsEndA->count() + $station->linksAsEndB->count() }}</dd>
                    </div>
                </dl>

                <form
                    method="POST"
                    action="{{ route('stations.destroy', $station) }}"
                    class="mt-4 border-t border-gray-100 pt-4 dark:border-white/10"
                    data-confirm="Remover a station {{ $station->name }}? Ela será excluída logicamente."
                    data-confirm-title="Remover station"
                    data-confirm-label="Remover"
                >
                    @csrf
                    @method('DELETE')

                    <x-ui.action type="submit" variant="danger" icon="trash" class="w-full">
                        Remover station
                    </x-ui.action>
                </form>
            </section>
        </aside>
    </div>
</x-layouts.admin>
