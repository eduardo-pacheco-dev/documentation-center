<x-layouts.admin :title="$radioLink->code">
    @php
        $ghz = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 3, ',', '.').' GHz';
        $mhz = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 0, ',', '.').' MHz';
        $mbps = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 0, ',', '.').' Mbps';
        $km = fn (?float $value): string => $value === null
            ? '—'
            : number_format($value, 2, ',', '.').' km';
        $endLabel = fn ($erb): string => $erb?->site_id ?: ($erb?->code ?? '—');

        $locatedEndpoints = collect([
            ['side' => 'A', 'erb' => $radioLink->erbA],
            ['side' => 'B', 'erb' => $radioLink->erbB],
        ])
            ->filter(fn (array $endpoint) => $endpoint['erb'] !== null && $endpoint['erb']->latitude !== null && $endpoint['erb']->longitude !== null)
            ->map(fn (array $endpoint) => [
                'side' => $endpoint['side'],
                'label' => $endpoint['erb']->site_id ?: $endpoint['erb']->code,
                'name' => $endpoint['erb']->name,
                'latitude' => (float) $endpoint['erb']->latitude,
                'longitude' => (float) $endpoint['erb']->longitude,
                'url' => route('admin.erbs.show', $endpoint['erb']),
            ])
            ->values();

        $bothEndpointsLocated = $locatedEndpoints->count() === 2;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Rede
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="arrows-right-left" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                {{ $radioLink->code }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Enlace de {{ $endLabel($radioLink->erbA) }} a {{ $endLabel($radioLink->erbB) }}
            </p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-radio-link-status :radio-link="$radioLink" />
                @if ($radioLink->polarization)
                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $radioLink->polarization->label() }}
                    </span>
                @endif
                @if ($radioLink->frequency !== null)
                    <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="signal" class="h-3.5 w-3.5" />
                        {{ $ghz($radioLink->frequency) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.radio-links.index') }}"
                class="inline-flex items-center gap-1.5 rounded-2xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="chevron-left" class="h-4 w-4" />
                Voltar
            </a>

            <button
                type="button"
                data-modal-open="radio-link-edit-modal"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </button>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="signal" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $ghz($radioLink->frequency) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Frequência</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="adjustments-horizontal" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $mhz($radioLink->bandwidth) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Largura de banda</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="chart-bar" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $mbps($radioLink->capacity) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Capacidade</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="map-pin" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $km($radioLink->distanceKm()) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Distância entre as pontas</p>
        </div>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    <x-icon name="arrows-right-left" class="h-4 w-4" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Pontas do enlace</h2>
            </div>
        </div>

        <div class="grid gap-px bg-gray-100 dark:bg-gray-800 sm:grid-cols-2">
            @foreach (['A' => $radioLink->erbA, 'B' => $radioLink->erbB] as $side => $erb)
                <div class="bg-white p-5 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Ponta {{ $side }}
                        </span>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon name="signal" class="h-4 w-4" />
                        </span>
                    </div>

                    @if ($erb)
                        <a
                            href="{{ route('admin.erbs.show', $erb) }}"
                            class="mt-3 block truncate text-lg font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                        >
                            {{ $erb->site_id ?: $erb->code }}
                        </a>
                        <p class="mt-0.5 truncate text-sm text-gray-500 dark:text-gray-400">
                            {{ $erb->code }} — {{ $erb->name }}
                        </p>

                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Equipamento</dt>
                                <dd class="truncate font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($side === 'A' ? $radioLink->equipment_a : $radioLink->equipment_b) ?? 'Não informado' }}
                                </dd>
                            </div>
                        </dl>

                        @if ($erb->latitude !== null && $erb->longitude !== null)
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-gray-50 px-3 py-2.5 dark:bg-gray-800/50">
                                <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format((float) $erb->latitude, 5, ',', '.') }},
                                    {{ number_format((float) $erb->longitude, 5, ',', '.') }}
                                </span>
                                <a
                                    href="https://www.google.com/maps?q={{ $erb->latitude }},{{ $erb->longitude }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                >
                                    <x-icon name="arrow-top-right-on-square" class="h-3.5 w-3.5" />
                                    Ver no mapa
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">ERB não encontrada.</p>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Equipamento</dt>
                                <dd class="truncate font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($side === 'A' ? $radioLink->equipment_a : $radioLink->equipment_b) ?? 'Não informado' }}
                                </dd>
                            </div>
                        </dl>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    @if ($locatedEndpoints->isNotEmpty())
        <style>
            #radio-link-map {
                position: relative;
                z-index: 0;
                isolation: isolate;
                height: 420px;
            }

            @media (max-width: 640px) {
                #radio-link-map {
                    height: 320px;
                }
            }

            .radio-link-map-pin {
                display: block;
                width: 20px;
                height: 20px;
                border: 3px solid #ffffff;
                border-radius: 9999px;
                background: #4f46e5;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.4), 0 0 0 6px rgba(79, 70, 229, 0.25);
            }

            .radio-link-map-pin-end {
                background: #059669;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.4), 0 0 0 6px rgba(5, 150, 105, 0.25);
            }

            .radio-link-map-distance {
                display: inline-flex;
                align-items: center;
                padding: 2px 8px;
                border-radius: 9999px;
                background: #ffffff;
                color: #374151;
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.25);
            }

            .radio-link-map-popup {
                min-width: 190px;
                font-size: 12px;
                line-height: 1.5;
                color: #4b5563;
            }

            .radio-link-map-popup-title {
                font-size: 13px;
                font-weight: 600;
                color: #111827;
            }

            .radio-link-map-popup-coords {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 11px;
            }

            .dark .radio-link-map-popup {
                color: #9ca3af;
            }

            .dark .radio-link-map-popup-title {
                color: #f3f4f6;
            }

            .dark .leaflet-popup-content-wrapper,
            .dark .leaflet-popup-tip {
                background: #111827;
            }
        </style>
    @endif

    <section class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="map-pin" class="h-4 w-4" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Mapa do enlace</h2>
                @if ($bothEndpointsLocated)
                    <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        {{ $km($radioLink->distanceKm()) }}
                    </span>
                @endif
            </div>

            @if ($bothEndpointsLocated)
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="https://www.google.com/maps/dir/?api=1&origin={{ $radioLink->erbA->latitude }},{{ $radioLink->erbA->longitude }}&destination={{ $radioLink->erbB->latitude }},{{ $radioLink->erbB->longitude }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    >
                        <x-icon name="arrow-top-right-on-square" class="h-3.5 w-3.5" />
                        Google Maps
                    </a>

                    <a
                        href="https://www.openstreetmap.org/directions?from={{ $radioLink->erbA->latitude }},{{ $radioLink->erbA->longitude }}&to={{ $radioLink->erbB->latitude }},{{ $radioLink->erbB->longitude }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    >
                        <x-icon name="arrow-top-right-on-square" class="h-3.5 w-3.5" />
                        OpenStreetMap
                    </a>
                </div>
            @endif
        </div>

        @if ($locatedEndpoints->isNotEmpty())
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />

            <div id="radio-link-map" class="bg-gray-100 dark:bg-gray-800"></div>

            <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                (function () {
                    const container = document.getElementById('radio-link-map');

                    if (! container) {
                        return;
                    }

                    if (typeof L === 'undefined') {
                        container.innerHTML = '<p style="display: flex; height: 100%; align-items: center; justify-content: center; font-size: 13px; color: #6b7280;">Não foi possível carregar o mapa.</p>';

                        return;
                    }

                    const endpoints = @js($locatedEndpoints->all());

                    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;',
                    })[character]);

                    const map = L.map(container, { scrollWheelZoom: false });

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19,
                    }).addTo(map);

                    const points = endpoints.map((endpoint) => [endpoint.latitude, endpoint.longitude]);

                    if (points.length === 2) {
                        L.polyline(points, {
                            color: '#4f46e5',
                            weight: 3,
                            opacity: 0.9,
                            dashArray: '10 8',
                        }).addTo(map);

                        map.fitBounds(L.latLngBounds(points), { padding: [48, 48] });

                        const middle = [
                            (points[0][0] + points[1][0]) / 2,
                            (points[0][1] + points[1][1]) / 2,
                        ];

                        L.marker(middle, {
                            interactive: false,
                            icon: L.divIcon({
                                className: '',
                                html: '<span class="radio-link-map-distance">' + escapeHtml(@js($km($radioLink->distanceKm()))) + '</span>',
                                iconSize: [78, 20],
                                iconAnchor: [39, 10],
                            }),
                        }).addTo(map);
                    } else {
                        map.setView(points[0], 16);
                    }

                    endpoints.forEach((endpoint) => {
                        const popup = [
                            '<div class="radio-link-map-popup">',
                            '<p class="radio-link-map-popup-title">' + escapeHtml('Ponta ' + endpoint.side + ' — ' + endpoint.label) + '</p>',
                            '<p>' + escapeHtml(endpoint.name) + '</p>',
                            '<p class="radio-link-map-popup-coords">' + endpoint.latitude.toFixed(5) + ', ' + endpoint.longitude.toFixed(5) + '</p>',
                            '<a href="' + escapeHtml(endpoint.url) + '" target="_blank" rel="noopener">Abrir cadastro da ERB</a>',
                            '</div>',
                        ].join('');

                        L.marker([endpoint.latitude, endpoint.longitude], {
                            title: endpoint.label,
                            icon: L.divIcon({
                                className: '',
                                html: '<span class="radio-link-map-pin' + (endpoint.side === 'A' ? '' : ' radio-link-map-pin-end') + '"></span>',
                                iconSize: [20, 20],
                                iconAnchor: [10, 10],
                                popupAnchor: [0, -11],
                            }),
                        })
                            .addTo(map)
                            .bindPopup(popup);
                    });
                })();
            </script>
        @else
            <div class="px-5 py-12 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <x-icon name="map-pin" class="h-5 w-5" />
                </span>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Informe as coordenadas das pontas para ver o traçado do enlace no mapa.</p>
            </div>
        @endif
    </section>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="cube" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Configuração</h2>
            </div>

            <dl class="mt-4 text-sm">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd><x-radio-link-status :radio-link="$radioLink" /></dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Polarização</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $radioLink->polarization?->label() ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-2.5 pb-0">
                    <dt class="text-gray-500 dark:text-gray-400">Distância</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $km($radioLink->distanceKm()) }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md lg:col-span-2 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <x-icon name="document-text" class="h-5 w-5" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observações</h2>
                </div>
                <button
                    type="button"
                    data-modal-open="radio-link-edit-modal"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                >
                    <x-icon name="pencil-square" class="h-3.5 w-3.5" />
                    Editar
                </button>
            </div>
            <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                {{ $radioLink->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </section>
    </div>

    <div class="mt-4 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            Criado em {{ $radioLink->created_at->format('d/m/Y H:i') }}
        </span>
        <span class="mx-2 text-gray-300 dark:text-gray-600">·</span>
        <span class="inline-flex items-center gap-1.5">
            <x-icon name="arrow-path" class="h-3.5 w-3.5" />
            Atualizado em {{ $radioLink->updated_at->format('d/m/Y H:i') }}
        </span>
    </div>

    @include('admin.radio-links.partials.edit-modal', [
        'radioLink' => $radioLink,
        'erbs' => $erbs,
        'forceOpen' => $openEditModal ?? false,
    ])

    @include('admin.radio-links.partials.modal-script')
</x-layouts.admin>
