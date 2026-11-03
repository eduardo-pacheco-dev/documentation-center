@php
    $dashboardUrl = route('admin.dashboard', $editing ? ['edit' => 1] : []);
    $indexUrl = route('admin.stations.index', $siteSearch !== '' ? ['search' => $siteSearch] : []);
@endphp

<section class="flex h-full flex-col rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="signal" class="h-4 w-4" />
            </span>

            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Sites</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Busque pelo ID do site</p>
            </div>
        </div>

        <x-ui.badge tone="indigo">{{ $totalSites }}</x-ui.badge>
    </div>

    <div class="border-b border-gray-100 px-5 py-3 dark:border-white/10">
        <form method="GET" action="{{ $dashboardUrl }}" class="flex items-center gap-2" role="search">
            @if ($editing)
                <input type="hidden" name="edit" value="1">
            @endif

            <div class="relative min-w-0 flex-1">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />

                <label for="site-search" class="sr-only">Buscar site por ID</label>
                <input
                    id="site-search"
                    type="search"
                    name="site_search"
                    value="{{ $siteSearch }}"
                    placeholder="Ex.: SPJAB051M"
                    class="h-9 w-full rounded-xl border border-gray-300 bg-gray-50 pl-9 pr-3 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                >
            </div>

            <x-ui.action type="submit" icon="magnifying-glass" size="sm">
                Buscar
            </x-ui.action>

            @if ($siteSearch !== '')
                <a
                    href="{{ $dashboardUrl }}"
                    class="shrink-0 rounded-xl px-2 py-2 text-xs font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >
                    Limpar
                </a>
            @endif
        </form>
    </div>

    <div class="flex-1">
        @forelse ($sites as $site)
            <a
                href="{{ route('admin.stations.index', ['search' => $site->site_id]) }}"
                class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 transition last:border-b-0 hover:bg-gray-50/70 dark:border-white/10 dark:hover:bg-white/[0.03]"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400">
                        <x-icon name="building-office" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0">
                        <p class="truncate font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ $site->site_id }}</p>

                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ collect([$site->municipio, $site->estado])->filter()->implode('/') ?: 'Município não informado' }}
                            · {{ $site->stations_count }} {{ $site->stations_count === 1 ? 'estação' : 'estações' }}
                        </p>
                    </div>
                </div>

                <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
            </a>
        @empty
            <div class="px-5 py-10 text-center">
                <span class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 dark:bg-white/5 dark:text-gray-500">
                    <x-icon name="signal" class="h-5 w-5" />
                </span>

                @if ($siteSearch !== '')
                    <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Nenhum site encontrado</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Não encontramos resultados para “{{ $siteSearch }}”.</p>
                    <a href="{{ $dashboardUrl }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        Limpar busca
                    </a>
                @else
                    <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Nenhum site cadastrado</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cadastre ou importe estações para acompanhar os sites.</p>
                    <x-ui.action :href="route('admin.stations.import')" icon="arrow-up-tray" size="sm" class="mt-3">
                        Importar estações
                    </x-ui.action>
                @endif
            </div>
        @endforelse
    </div>

    <div class="border-t border-gray-100 px-5 py-3 dark:border-white/10">
        <a href="{{ $indexUrl }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            Ver estações
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        </a>
    </div>
</section>
