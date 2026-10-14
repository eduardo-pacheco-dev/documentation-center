<x-layouts.admin :title="$erb->code">
    @php
        $address = collect([
            trim(($erb->street ?? '').($erb->number ? ', '.$erb->number : '')),
            $erb->complement,
            $erb->neighborhood,
            trim(($erb->city ?? '').($erb->state ? '/'.$erb->state : '')),
            $erb->zip,
        ])->filter();

        $radioLinkCount = $erb->radioLinksAsEndA->count() + $erb->radioLinksAsEndB->count();
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Estação rádio base
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="signal" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                {{ $erb->code }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $erb->name }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-erb-status :erb="$erb" />
                @if ($erb->operator)
                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $erb->operator }}
                    </span>
                @endif
                @if ($erb->technology)
                    <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        {{ $erb->technology }}
                    </span>
                @endif
                @if ($erb->city)
                    <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                        <x-icon name="map-pin" class="h-3.5 w-3.5" />
                        {{ $erb->city }}{{ $erb->state ? '/'.$erb->state : '' }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.erbs.index') }}"
                class="inline-flex items-center gap-1.5 rounded-2xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="chevron-left" class="h-4 w-4" />
                Voltar
            </a>

            <button
                type="button"
                data-modal-open="erb-edit-modal"
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
                <x-icon name="clipboard-document-list" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $erb->workOrders->count() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ordens de serviço</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="briefcase" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $erb->projects->count() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Projetos</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="arrows-right-left" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $radioLinkCount }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Radio links</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="paper-clip" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $erb->documents->count() }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Anexos</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="map-pin" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Localização</h2>
            </div>

            <div class="mt-4 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                @forelse ($address as $line)
                    <p>{{ $line }}</p>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Endereço não informado.</p>
                @endforelse
            </div>

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
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="signal" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Operadora e tecnologia</h2>
            </div>

            <dl class="mt-4 text-sm">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Operadora</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $erb->operator ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Tecnologia</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $erb->technology ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-2.5 pb-0">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd><x-erb-status :erb="$erb" /></dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                    <x-icon name="cube" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Integração AFL</h2>
            </div>

            <dl class="mt-4 text-sm">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Site ID</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $erb->site_id ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">End ID</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $erb->end_id ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Responsável</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $erb->assigned_to ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">ID SMP</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $erb->nokia_smp_id ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Ordem de serviço</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $erb->service_order_id ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Início</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $erb->start_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-2.5 pb-0">
                    <dt class="text-gray-500 dark:text-gray-400">Fim</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $erb->end_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:col-span-2 lg:col-span-3 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                        <x-icon name="document-text" class="h-5 w-5" />
                    </span>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observações</h2>
                </div>
                <button
                    type="button"
                    data-modal-open="erb-edit-modal"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                >
                    <x-icon name="pencil-square" class="h-3.5 w-3.5" />
                    Editar
                </button>
            </div>
            <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                {{ $erb->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </section>
    </div>

    @if ($erb->latitude !== null && $erb->longitude !== null)
        <style>
            #erb-map {
                position: relative;
                z-index: 0;
                isolation: isolate;
                height: 420px;
            }

            @media (max-width: 640px) {
                #erb-map {
                    height: 320px;
                }
            }

            .erb-map-pin {
                display: block;
                width: 20px;
                height: 20px;
                border: 3px solid #ffffff;
                border-radius: 9999px;
                background: #4f46e5;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.4), 0 0 0 6px rgba(79, 70, 229, 0.25);
            }

            .erb-map-popup {
                min-width: 190px;
                font-size: 12px;
                line-height: 1.5;
                color: #4b5563;
            }

            .erb-map-popup-title {
                font-size: 13px;
                font-weight: 600;
                color: #111827;
            }

            .erb-map-popup-coords {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 11px;
                color: #6b7280;
            }

            .erb-map-popup-link {
                display: inline-flex;
                align-items: center;
                margin-top: 6px;
                font-weight: 600;
                color: #4f46e5;
                text-decoration: none;
            }

            .erb-map-popup-link:hover {
                text-decoration: underline;
            }

            .dark .erb-map-popup {
                color: #9ca3af;
            }

            .dark .erb-map-popup-title {
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
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Mapa</h2>
                @if ($erb->latitude !== null && $erb->longitude !== null)
                    <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format((float) $erb->latitude, 5, ',', '.') }},
                        {{ number_format((float) $erb->longitude, 5, ',', '.') }}
                    </span>
                @endif
            </div>

            @if ($erb->latitude !== null && $erb->longitude !== null)
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="https://www.google.com/maps?q={{ $erb->latitude }},{{ $erb->longitude }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    >
                        <x-icon name="arrow-top-right-on-square" class="h-3.5 w-3.5" />
                        Google Maps
                    </a>

                    <a
                        href="https://www.openstreetmap.org/?mlat={{ $erb->latitude }}&mlon={{ $erb->longitude }}#map=16/{{ $erb->latitude }}/{{ $erb->longitude }}"
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

        @if ($erb->latitude !== null && $erb->longitude !== null)
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />

            <div id="erb-map" class="bg-gray-100 dark:bg-gray-800"></div>

            <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                (function () {
                    const container = document.getElementById('erb-map');

                    if (! container) {
                        return;
                    }

                    if (typeof L === 'undefined') {
                        container.innerHTML = '<p style="display: flex; height: 100%; align-items: center; justify-content: center; font-size: 13px; color: #6b7280;">Não foi possível carregar o mapa.</p>';

                        return;
                    }

                    const latitude = @js((float) $erb->latitude);
                    const longitude = @js((float) $erb->longitude);

                    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;',
                    })[character]);

                    const map = L.map(container, {
                        center: [latitude, longitude],
                        zoom: 16,
                        scrollWheelZoom: false,
                    });

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19,
                    }).addTo(map);

                    const address = @js($address->implode(' · '));

                    const popup = [
                        '<div class="erb-map-popup">',
                        '<p class="erb-map-popup-title">' + escapeHtml(@js($erb->code)) + '</p>',
                        address ? '<p>' + escapeHtml(address) + '</p>' : '',
                        '<p class="erb-map-popup-coords">' + latitude.toFixed(5) + ', ' + longitude.toFixed(5) + '</p>',
                        '<a class="erb-map-popup-link" href="https://www.google.com/maps?q=' + latitude + ',' + longitude + '" target="_blank" rel="noopener">Abrir no Google Maps</a>',
                        '</div>',
                    ].join('');

                    L.marker([latitude, longitude], {
                        title: @js($erb->name),
                        icon: L.divIcon({
                            className: '',
                            html: '<span class="erb-map-pin"></span>',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10],
                            popupAnchor: [0, -11],
                        }),
                    })
                        .addTo(map)
                        .bindPopup(popup)
                        .openPopup();
                })();
            </script>
        @else
            <div class="px-5 py-12 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <x-icon name="map-pin" class="h-5 w-5" />
                </span>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Coordenadas não informadas para esta ERB.</p>
            </div>
        @endif
    </section>

    <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="clipboard-document-list" class="h-4 w-4" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ordens de serviço</h2>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $erb->workOrders->count() }}
                </span>
            </div>

            <button
                type="button"
                data-modal-open="erb-work-orders-modal"
                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="pencil-square" class="h-3.5 w-3.5" />
                Editar
            </button>
        </div>

        @if ($erb->workOrders->isEmpty())
            <div class="px-5 py-12 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <x-icon name="clipboard-document-list" class="h-5 w-5" />
                </span>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nenhuma ordem de serviço vinculada a esta ERB.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50/80 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5 font-medium">Número</th>
                            <th class="px-5 py-3.5 font-medium">Cliente</th>
                            <th class="px-5 py-3.5 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($erb->workOrders as $workOrder)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                        {{ $workOrder->number }}
                                    </a>
                                </td>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $workOrder->client?->name ?? '—' }}</td>
                                <td class="px-5 py-4"><x-work-order-status :work-order="$workOrder" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="briefcase" class="h-4 w-4" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Projetos</h2>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $erb->projects->count() }}
                </span>
            </div>

            <button
                type="button"
                data-modal-open="erb-projects-modal"
                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="pencil-square" class="h-3.5 w-3.5" />
                Editar
            </button>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($erb->projects as $project)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                    <a href="{{ route('admin.projects.show', $project) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                        {{ $project->name }}
                    </a>
                    <x-project-status :project="$project" />
                </div>
            @empty
                <p class="flex items-center justify-center gap-2 px-5 py-12 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="briefcase" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    Nenhum projeto vinculado a esta ERB.
                </p>
            @endforelse
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    <x-icon name="arrows-right-left" class="h-4 w-4" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Radio links</h2>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $radioLinkCount }}
                </span>
            </div>

            <button
                type="button"
                data-modal-open="erb-radio-links-modal"
                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="pencil-square" class="h-3.5 w-3.5" />
                Editar
            </button>
        </div>

        @php
            $radioLinks = $erb->radioLinksAsEndA->merge($erb->radioLinksAsEndB)->sortByDesc('updated_at');
        @endphp

        @if ($radioLinks->isEmpty())
            <div class="px-5 py-12 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <x-icon name="arrows-right-left" class="h-5 w-5" />
                </span>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nenhum radio link vinculado a esta ERB.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($radioLinks as $radioLink)
                    @php
                        $otherEnd = $radioLink->erb_a_id === $erb->getKey() ? $radioLink->erbB : $radioLink->erbA;
                    @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                        <div class="flex min-w-0 items-center gap-3">
                            <a href="{{ route('admin.radio-links.show', $radioLink) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                {{ $radioLink->code }}
                            </a>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                com {{ $otherEnd?->code ?? 'ERB removida' }}
                                @if ($radioLink->frequency !== null)
                                    &middot; {{ number_format((float) $radioLink->frequency, 1, ',', '.') }} GHz
                                @endif
                            </span>
                        </div>
                        <x-radio-link-status :radio-link="$radioLink" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @include('admin.erbs.partials.edit-modal', ['erb' => $erb])

    @include('admin.erbs.partials.relations', [
        'erb' => $erb,
        'projectOptions' => $projectOptions,
        'workOrderOptions' => $workOrderOptions,
        'radioLinkOptions' => $radioLinkOptions,
    ])

    @include('admin.erbs.partials.documents', ['erb' => $erb])

    @include('admin.erbs.partials.comments', ['erb' => $erb])

    <div class="mt-4 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            Criado em {{ $erb->created_at->format('d/m/Y H:i') }}
        </span>
        <span class="mx-2 text-gray-300 dark:text-gray-600">·</span>
        <span class="inline-flex items-center gap-1.5">
            <x-icon name="arrow-path" class="h-3.5 w-3.5" />
            Atualizado em {{ $erb->updated_at->format('d/m/Y H:i') }}
        </span>
    </div>
</x-layouts.admin>
