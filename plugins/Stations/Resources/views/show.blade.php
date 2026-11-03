@php
    $statusTone = match (mb_strtolower((string) $station->status)) {
        'aquisitado', 'ativo', 'ativa' => 'green',
        'em análise', 'em analise', 'pendente' => 'amber',
        'cancelado', 'cancelada' => 'red',
        default => 'gray',
    };

    $display = fn (mixed $value): string => filled($value) ? (string) $value : '—';

    $fieldGroups = [
        [
            'title' => 'Identificação',
            'description' => 'Chaves, classificação e tecnologia do elemento de rede.',
            'icon' => 'cube',
            'fields' => [
                'ID do site' => $station->site_id,
                'ID da estação' => $station->station_id,
                'ID do endereço' => $station->endereco_id,
                'Tipo de elemento' => $station->tipo_de_elemento,
                'Tecnologia' => $station->tecnologia,
                'Tipo de conexão' => $station->tipo_de_conexao,
                'Classificação' => $station->classificacao,
                'Status' => $station->status,
            ],
        ],
        [
            'title' => 'Infraestrutura',
            'description' => 'Detentor da área, tipo de infraestrutura, torre e regional.',
            'icon' => 'wrench-screwdriver',
            'fields' => [
                'Detentor da área' => $station->detentor_area,
                'Tipo de infraestrutura' => $station->tipo_de_infra,
                'Tipo de EV' => $station->tipo_de_ev,
                'Tipo da torre' => $station->tipo_da_torre,
                'Regional' => $station->regional,
                'Latitude' => $station->latitude,
                'Longitude' => $station->longitude,
                'Atualizado em' => $station->updated_at?->format('d/m/Y H:i'),
            ],
        ],
    ];

    $logradouro = trim(collect([$station->tipo_de_logradouro, $station->logradouro])->filter()->implode(' '));

    if (filled($station->numero)) {
        $logradouro .= ($logradouro !== '' ? ', ' : '').$station->numero;
    }

    if (filled($station->complemento)) {
        $logradouro .= ' — '.$station->complemento;
    }

    $cepDigits = preg_replace('/\D/', '', (string) $station->cep);
    $cep = strlen($cepDigits) === 8
        ? substr($cepDigits, 0, 5).'-'.substr($cepDigits, 5)
        : $station->cep;

    $municipio = collect([$station->municipio, $station->estado])->filter()->implode('/');

    $enderecoCompleto = collect([
        $logradouro,
        $station->bairro,
        $municipio,
        $cep,
    ])->filter()->implode(' · ');

    $hasCoordinates = $latitude !== null && $longitude !== null;

    $googleMapsUrl = $hasCoordinates
        ? 'https://www.google.com/maps/search/?api=1&query='.$latitude.','.$longitude
        : 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($enderecoCompleto);

    $addressFields = [
        'Logradouro' => $logradouro,
        'Bairro' => $station->bairro,
        'Município' => $station->municipio,
        'Estado (UF)' => $station->estado,
        'CEP' => $cep,
        'Regional' => $station->regional,
    ];
@endphp

<x-layouts.admin title="{{ $station->station_id ?: $station->site_id }}">
    @if ($hasCoordinates)
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

            <style>
                .station-map-marker {
                    position: relative;
                    width: 64px;
                    height: 76px;
                }

                .station-map-marker__tower {
                    position: absolute;
                    inset-inline: 0;
                    bottom: 0;
                    display: flex;
                    justify-content: center;
                    color: #4f46e5;
                }

                .station-map-marker__tower svg {
                    width: 44px;
                    height: 64px;
                }

                .station-map-marker__signal {
                    position: absolute;
                    left: 50%;
                    top: 16px;
                    width: 1px;
                    height: 1px;
                }

                .station-map-marker__signal span {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 18px;
                    height: 18px;
                    margin: -9px 0 0 -9px;
                    border: 2px solid rgb(79 70 229 / 55%);
                    border-radius: 9999px;
                    animation: station-signal-wave 2.4s ease-out infinite;
                }

                .station-map-marker__signal span:nth-child(2) {
                    animation-delay: 0.8s;
                }

                .station-map-marker__signal span:nth-child(3) {
                    animation-delay: 1.6s;
                }

                .dark .station-map-marker__tower {
                    color: #a5b4fc;
                }

                .dark .station-map-marker__signal span {
                    border-color: rgb(165 180 252 / 55%);
                }

                @keyframes station-signal-wave {
                    0% {
                        transform: scale(0.35);
                        opacity: 0.8;
                    }

                    75%,
                    100% {
                        transform: scale(2.8);
                        opacity: 0;
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    .station-map-marker__signal span {
                        animation: none;
                        opacity: 0.5;
                    }
                }
            </style>
        @endpush
    @endif

    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.stations.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Estações</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">{{ $station->station_id ?: $station->site_id }}</span>
    </nav>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="flex min-w-0 items-start gap-3">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="signal" class="h-5 w-5" />
            </span>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge :tone="$statusTone">{{ $station->status ?: 'Sem status' }}</x-ui.badge>

                    @if ($station->tecnologia)
                        <x-ui.badge tone="indigo">{{ $station->tecnologia }}</x-ui.badge>
                    @endif

                    @if ($station->tipo_de_elemento)
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $station->tipo_de_elemento }}</span>
                    @endif
                </div>

                <h1 class="mt-2 truncate text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                    {{ $station->station_id ?: $station->site_id }}
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-mono text-xs">SITE {{ $station->site_id }}</span>

                    @if ($enderecoCompleto)
                        <span class="mx-1 text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
                        {{ $enderecoCompleto }}
                    @endif
                </p>

                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    criado <x-relative-time :value="$station->created_at" />
                    · atualizado <x-relative-time :value="$station->updated_at" />
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.action :href="route('admin.stations.export', $station)" icon="arrow-down-tray" variant="secondary">
                Exportar
            </x-ui.action>

            <x-ui.action :href="route('admin.stations.edit', $station)" icon="pencil-square" variant="secondary">
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

    <dl class="mt-4 grid gap-4 rounded-2xl border border-gray-200/80 bg-white px-4 py-3 text-sm sm:grid-cols-3 dark:border-white/10 dark:bg-[#171716]">
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Endereço</dt>
            <dd class="mt-1 font-medium text-gray-700 dark:text-gray-200">{{ $enderecoCompleto ?: 'Não informado' }}</dd>
        </div>

        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Coordenadas</dt>
            <dd class="mt-1 font-medium text-gray-700 dark:text-gray-200">
                @if ($hasCoordinates)
                    {{ $latitude }}, {{ $longitude }}
                @else
                    Não informadas
                @endif
            </dd>
        </div>

        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Última atualização</dt>
            <dd class="mt-1 font-medium text-gray-700 dark:text-gray-200">
                <x-relative-time :value="$station->updated_at" />
            </dd>
        </div>
    </dl>

    <nav aria-label="Seções da estação" class="sticky top-2 z-40 mt-4 flex gap-1 overflow-x-auto rounded-2xl border border-gray-200/80 bg-white p-1.5 dark:border-white/10 dark:bg-[#171716]">
        @foreach ([
            'informacoes' => 'Informações',
            'localizacao' => 'Localização',
            'mapa' => 'Mapa',
            'ppi-tssr' => 'PPI/TSSR',
            'anexos' => 'Anexos',
            'comentarios' => 'Comentários',
        ] as $anchor => $label)
            <a
                href="#{{ $anchor }}"
                class="shrink-0 rounded-xl px-3 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
            >
                {{ $label }}

                @if ($anchor === 'ppi-tssr' && $ppiTssrFiles->isNotEmpty())
                    <span class="ml-1 text-[10px] text-indigo-500 dark:text-indigo-400">{{ $ppiTssrFiles->count() }}</span>
                @endif

                @if ($anchor === 'anexos' && $attachmentFiles->isNotEmpty())
                    <span class="ml-1 text-[10px] text-indigo-500 dark:text-indigo-400">{{ $attachmentFiles->count() }}</span>
                @endif

                @if ($anchor === 'comentarios' && $comments->isNotEmpty())
                    <span class="ml-1 text-[10px] text-indigo-500 dark:text-indigo-400">{{ $comments->count() }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <section id="informacoes" class="mt-4 scroll-mt-24 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="clipboard-document-list" class="h-5 w-5" />
            </span>

            <div>
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Informações</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Identificação técnica, classificação e infraestrutura da estação.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
            @foreach ($fieldGroups as $group)
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <x-icon :name="$group['icon']" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $group['title'] }}</h3>
                    </div>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $group['description'] }}</p>

                    <dl class="mt-3 divide-y divide-gray-200/70 dark:divide-white/10">
                        @foreach ($group['fields'] as $label => $value)
                            <div class="grid grid-cols-[9.5rem_minmax(0,1fr)] gap-3 py-2.5 text-sm">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                                <dd class="min-w-0 break-words font-medium text-gray-800 dark:text-gray-100">{{ $display($value) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </section>

    <section id="localizacao" class="mt-4 scroll-mt-24 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="map-pin" class="h-5 w-5" />
            </span>

            <div>
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Localização</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Endereço completo e coordenadas geográficas do site.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
            <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Endereço</h3>

                <dl class="mt-3 divide-y divide-gray-200/70 dark:divide-white/10">
                    @foreach ($addressFields as $label => $value)
                        <div class="grid grid-cols-[8.5rem_minmax(0,1fr)] gap-3 py-2.5 text-sm">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="min-w-0 break-words font-medium text-gray-800 dark:text-gray-100">{{ $display($value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="flex flex-col gap-3">
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Coordenadas</h3>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-white/5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Latitude</p>
                            <p class="mt-1 font-mono text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $display($station->latitude) }}</p>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-white/5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Longitude</p>
                            <p class="mt-1 font-mono text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $display($station->longitude) }}</p>
                        </div>
                    </div>
                </div>

                <a
                    href="{{ $googleMapsUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                >
                    <x-icon name="map-pin" class="h-4 w-4" />
                    Abrir no Google Maps
                    <x-icon name="arrow-top-right-on-square" class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>

    <section id="mapa" class="mt-4 scroll-mt-24 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="map-pin" class="h-5 w-5" />
                </span>

                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Mapa</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Localização aproximada do site no OpenStreetMap.</p>
                </div>
            </div>

            @if ($hasCoordinates)
                <a
                    href="{{ $googleMapsUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300"
                >
                    <x-icon name="arrow-top-right-on-square" class="h-4 w-4" />
                    Abrir no Google Maps
                </a>
            @endif
        </div>

        @if ($hasCoordinates)
            <div class="relative z-0 mt-5 overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                <div id="station-map" class="h-[340px] w-full bg-gray-100 sm:h-[400px] dark:bg-white/5"></div>
            </div>

            <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500">
                Mapa © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="underline decoration-dotted underline-offset-2 hover:text-gray-600 dark:hover:text-gray-300">OpenStreetMap</a>
            </p>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-10 text-center dark:border-white/15 dark:bg-[#171716]">
                <x-icon name="map-pin" class="mx-auto h-6 w-6 text-gray-400 dark:text-gray-500" />
                <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">Coordenadas não informadas</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Edite a estação e informe a latitude e a longitude para exibir o mapa.</p>
                <x-ui.action :href="route('admin.stations.edit', $station)" variant="secondary" size="sm" icon="pencil-square" class="mt-4">
                    Informar coordenadas
                </x-ui.action>
            </div>
        @endif
    </section>

    <div class="mt-4">
        <x-stations::partials.attachment-section
            :station="$station"
            :category="\Plugins\Stations\Models\StationAttachment::CATEGORY_PPI_TSSR"
            title="PPI/TSSR"
            description="Documentos de PPI (Plano de Projeto e Implantação) e TSSR (Technical Site Survey Report)."
            :files="$ppiTssrFiles"
            icon="document-check"
            empty-title="Nenhum documento de PPI/TSSR"
            empty-description="Envie os relatórios e planos técnicos desta estação."
            class="scroll-mt-24"
            id="ppi-tssr"
        />

        <x-stations::partials.attachment-section
            :station="$station"
            :category="\Plugins\Stations\Models\StationAttachment::CATEGORY_ATTACHMENT"
            title="Anexos"
            description="Arquivos complementares, fotos, plantas averbadas e documentos de apoio da estação."
            :files="$attachmentFiles"
            icon="paper-clip"
            empty-title="Nenhum anexo"
            empty-description="Envie fotos, plantas ou outros documentos de apoio."
            class="mt-4 scroll-mt-24"
            id="anexos"
        />
    </div>

    <section id="comentarios" class="mt-4 scroll-mt-24 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="chat-bubble-oval-left-ellipsis" class="h-5 w-5" />
                </span>

                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Comentários</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Observações e alinhamentos da equipe sobre esta estação.</p>
                </div>
            </div>

            <x-ui.badge :tone="$comments->isNotEmpty() ? 'indigo' : 'gray'">
                {{ $comments->count() }} {{ $comments->count() === 1 ? 'comentário' : 'comentários' }}
            </x-ui.badge>
        </div>

        <form
            method="POST"
            action="{{ route('admin.stations.comments.store', $station) }}"
            class="mt-5"
            x-data="{ count: {{ mb_strlen((string) old('body', '')) }} }"
        >
            @csrf

            <div class="flex gap-3">
                <x-ui.avatar :name="auth()->user()->full_name" class="mt-0.5" />

                <div class="min-w-0 flex-1">
                    <label for="comment-body" class="sr-only">Comentário</label>

                    <textarea
                        id="comment-body"
                        name="body"
                        rows="3"
                        maxlength="2000"
                        required
                        placeholder="Escreva um comentário sobre a estação..."
                        x-on:input="count = $event.target.value.length"
                        class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                    >{{ old('body') }}</textarea>

                    @error('body')
                        <x-ui.field-error class="mt-2">{{ $message }}</x-ui.field-error>
                    @enderror

                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">
                            <span x-text="count"></span>/2000 caracteres
                        </p>

                        <x-ui.action type="submit" icon="paper-airplane" size="sm">
                            Comentar
                        </x-ui.action>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-6 border-t border-gray-100 pt-2 dark:border-white/10">
            @forelse ($comments as $comment)
                <article class="flex gap-3 border-b border-gray-100 py-4 last:border-b-0 dark:border-white/10">
                    <x-ui.avatar :name="$comment->user?->full_name ?? 'Usuário removido'" />

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $comment->user?->full_name ?? 'Usuário removido' }}</p>

                            @if ($comment->user_id === auth()->id())
                                <x-ui.badge tone="indigo">Você</x-ui.badge>
                            @endif

                            <span class="text-xs text-gray-400 dark:text-gray-500" aria-hidden="true">·</span>
                            <x-relative-time :value="$comment->created_at" class="text-xs text-gray-400 dark:text-gray-500" />
                        </div>

                        <p class="mt-1.5 whitespace-pre-line break-words text-sm text-gray-700 dark:text-gray-200">{{ $comment->body }}</p>

                        @if (auth()->user()?->is_admin || $comment->user_id === auth()->id())
                            <form
                                method="POST"
                                action="{{ route('admin.stations.comments.destroy', [$station, $comment]) }}"
                                data-confirm-delete="Remover este comentário? Essa ação não pode ser desfeita."
                                class="mt-2"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 transition hover:text-red-600 dark:text-red-400 dark:hover:text-red-300">
                                    <x-icon name="trash" class="h-3.5 w-3.5" />
                                    Remover
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <x-ui.empty-state
                    icon="chat-bubble-oval-left-ellipsis"
                    title="Nenhum comentário ainda"
                    description="Use o campo acima para registrar a primeira observação sobre esta estação."
                    tone="indigo"
                />
            @endforelse
        </div>
    </section>

    <div class="mt-4">
        <a href="{{ route('admin.stations.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Voltar para as estações
        </a>
    </div>

    @if ($hasCoordinates)
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const element = document.getElementById('station-map');

                    if (! element || typeof L === 'undefined') {
                        return;
                    }

                    const latitude = {{ $latitude }};
                    const longitude = {{ $longitude }};
                    const title = @json($station->station_id ?: $station->site_id);
                    const address = @json($enderecoCompleto);

                    const map = L.map(element, {
                        scrollWheelZoom: false,
                        zoomControl: true,
                    }).setView([latitude, longitude], 16);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    }).addTo(map);

                    const popup = document.createElement('div');
                    popup.className = 'text-sm';

                    const heading = document.createElement('strong');
                    heading.textContent = title;
                    popup.append(heading);

                    if (address) {
                        popup.append(document.createElement('br'), document.createTextNode(address));
                    }

                    const towerIcon = L.divIcon({
                        className: 'station-map-marker',
                        html: `
                            <div class="station-map-marker__signal" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <div class="station-map-marker__tower" aria-hidden="true">
                                <svg viewBox="0 0 48 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 3v7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    <circle cx="24" cy="3" r="2" fill="currentColor" />
                                    <path d="M15 61 24 13l9 48" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                                    <path d="M17.5 50h13M19.5 39h9M21 29h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    <path d="M19.5 39 30.5 50M28.5 39 17.5 50M21 29l7.5 10M27 29l-7.5 10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" opacity=".75" />
                                    <path d="M13 62h22" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                </svg>
                            </div>
                        `,
                        iconSize: [64, 76],
                        iconAnchor: [32, 76],
                        popupAnchor: [0, -72],
                    });

                    L.marker([latitude, longitude], {
                        icon: towerIcon,
                        alt: title,
                        title: title,
                    }).addTo(map).bindPopup(popup).openPopup();

                    window.setTimeout(() => map.invalidateSize(), 200);
                });
            </script>
        @endpush
    @endif
</x-layouts.admin>
