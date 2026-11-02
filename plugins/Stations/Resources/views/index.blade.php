<x-layouts.admin title="Stations">
    @php
        $query = request()->query();
        unset($query['page']);

        $cardClass = 'rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]';
        $inputClass = 'h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:bg-white/5';
    @endphp

    <x-ui.page-header
        category="Operação"
        title="Stations"
        description="Gestão independente de stations, com documentos, comentários e enlaces."
        icon="signal"
    >
        <x-ui.action :href="route('stations.import.template')" variant="secondary" icon="arrow-down-tray">
            Modelo de importação
        </x-ui.action>

        <x-ui.action :href="route('stations.create')" icon="plus">
            Nova station
        </x-ui.action>
    </x-ui.page-header>

    @error('import')
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-semibold">Não foi possível importar a planilha.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach (explode("\n", $message) as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        </div>
    @enderror

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="{{ $cardClass }} p-5">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="signal" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Stations cadastradas</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $stats['active'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ativas</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="exclamation-triangle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $stats['maintenance'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em manutenção</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <x-icon name="arrows-right-left" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $stats['links'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enlaces cadastrados</p>
        </div>
    </div>

    <section class="{{ $cardClass }} mt-4 p-4">
        <form method="GET" action="{{ route('stations.index') }}" class="flex flex-wrap items-center gap-2">
            @foreach (['view', 'sort', 'direction', 'per_page'] as $param)
                @if (request($param))
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach

            <div class="relative w-full max-w-sm">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por código, site, end, operadora, cidade..."
                    class="{{ $inputClass }} pl-9"
                >
            </div>

            <label for="status-filter" class="sr-only">Status</label>
            <select id="status-filter" name="status" class="{{ $inputClass }} w-auto pr-8" onchange="this.form.submit()">
                <option value="">Todos os status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}" @selected($status === $option->value)>{{ $option->label() }}</option>
                @endforeach
            </select>

            <x-ui.action type="submit" variant="secondary" icon="magnifying-glass" class="h-10">
                Buscar
            </x-ui.action>

            @if ($search !== '' || $status !== null)
                <a href="{{ route('stations.index', array_filter(['view' => $view])) }}" class="px-2 text-sm font-medium text-gray-500 transition hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300">
                    Limpar
                </a>
            @endif
        </form>

        <details class="mt-3 border-t border-gray-100 pt-3 dark:border-white/10">
            <summary class="cursor-pointer text-sm font-medium text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-300">
                Importar stations de uma planilha (.xlsx)
            </summary>

            <form method="POST" action="{{ route('stations.import') }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-end gap-2">
                @csrf

                <div class="w-full max-w-sm">
                    <label for="import-file" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Arquivo</label>
                    <input id="import-file" name="file" type="file" accept=".xlsx,.xls" required class="mt-1 block w-full cursor-pointer rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300">
                </div>

                <x-ui.action type="submit" variant="secondary" icon="arrow-up-tray" class="h-10">
                    Importar
                </x-ui.action>

                <p class="w-full text-xs text-gray-500 dark:text-gray-400">
                    Colunas: {{ implode(', ', ['Site ID', 'End ID', 'Operadora', 'Tecnologia', 'Nokia SMP ID', 'Status', 'Início', 'Término', 'Logradouro', 'Número', 'Complemento', 'Bairro', 'Cidade', 'Estado', 'CEP', 'Latitude', 'Longitude', 'Observações']) }}.
                </p>
            </form>
        </details>
    </section>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Exibindo
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $stations->count() }}</span>
            de
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $stations->total() }}</span>
            stations
        </p>

        <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-1 dark:border-white/10 dark:bg-[#171716]" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards'] as $mode => $label)
                <a
                    href="{{ route('stations.index', array_merge($query, ['view' => $mode])) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-sm font-medium transition {{ $view === $mode ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : 'squares-2x2'" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($stations->isEmpty())
        <div class="{{ $cardClass }} mt-4">
            <x-ui.empty-state
                icon="signal"
                title="Nenhuma station encontrada"
                description="Cadastre a primeira station ou importe uma planilha para começar."
                tone="indigo"
                shape="square"
            />
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($stations as $station)
                <article class="{{ $cardClass }} p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $station->code }}</p>
                            <h2 class="mt-1 truncate text-base font-semibold text-gray-900 dark:text-white">{{ $station->name }}</h2>
                        </div>

                        <x-ui.badge :tone="$station->status->tone()">{{ $station->status->label() }}</x-ui.badge>
                    </div>

                    <dl class="mt-4 space-y-1.5 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500 dark:text-gray-400">Site / End</dt>
                            <dd class="truncate font-mono text-xs">{{ $station->site_id ?: '—' }} / {{ $station->end_id ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500 dark:text-gray-400">Operadora</dt>
                            <dd class="truncate">{{ $station->operator ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500 dark:text-gray-400">Tecnologia</dt>
                            <dd class="truncate">{{ $station->technology ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500 dark:text-gray-400">Cidade / UF</dt>
                            <dd class="truncate">{{ $station->city ?: '—' }}{{ $station->state ? ' / '.$station->state : '' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex items-center gap-3 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1"><x-icon name="document-text" class="h-3.5 w-3.5" /> {{ $station->documents_count }}</span>
                        <span class="inline-flex items-center gap-1"><x-icon name="chat-bubble-oval-left-ellipsis" class="h-3.5 w-3.5" /> {{ $station->comments_count }}</span>
                        <span class="inline-flex items-center gap-1"><x-icon name="arrows-right-left" class="h-3.5 w-3.5" /> {{ $station->links_as_end_a_count + $station->links_as_end_b_count }}</span>

                        <a href="{{ route('stations.show', $station) }}" class="ml-auto font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            Abrir
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$stations" />
        </div>
    @else
        <div class="{{ $cardClass }} mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
                        <tr>
                            <x-sort-header column="code" label="Station" icon="signal" class="px-5 py-3.5" />
                            <x-sort-header column="operator" label="Operadora" class="px-5 py-3.5" />
                            <x-sort-header column="technology" label="Tecnologia" class="px-5 py-3.5" />
                            <x-sort-header column="city" label="Local" class="px-5 py-3.5" />
                            <x-sort-header column="status" label="Status" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 font-semibold">Vínculos</th>
                            <x-sort-header column="updated_at" label="Atualizada" icon="clock" class="px-5 py-3.5" />
                            <th class="px-5 py-3.5 text-right font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($stations as $station)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('stations.show', $station) }}" class="font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-300">
                                        {{ $station->name }}
                                    </a>
                                    <p class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ $station->code }}
                                        @if ($station->site_id || $station->end_id)
                                            · {{ $station->site_id ?: '—' }} / {{ $station->end_id ?: '—' }}
                                        @endif
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-gray-600 dark:text-gray-300">{{ $station->operator ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-gray-600 dark:text-gray-300">{{ $station->technology ?: '—' }}</td>
                                <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">
                                    {{ $station->city ?: '—' }}{{ $station->state ? ' / '.$station->state : '' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <x-ui.badge :tone="$station->status->tone()">{{ $station->status->label() }}</x-ui.badge>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center gap-1" title="Documentos"><x-icon name="document-text" class="h-3.5 w-3.5" /> {{ $station->documents_count }}</span>
                                        <span class="inline-flex items-center gap-1" title="Comentários"><x-icon name="chat-bubble-oval-left-ellipsis" class="h-3.5 w-3.5" /> {{ $station->comments_count }}</span>
                                        <span class="inline-flex items-center gap-1" title="Enlaces"><x-icon name="arrows-right-left" class="h-3.5 w-3.5" /> {{ $station->links_as_end_a_count + $station->links_as_end_b_count }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-gray-500 dark:text-gray-400">
                                    <x-relative-time :value="$station->updated_at" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <x-ui.action :href="route('stations.show', $station)" size="sm" variant="secondary" icon="eye">
                                            Ver
                                        </x-ui.action>

                                        <x-ui.action :href="route('stations.edit', $station)" size="sm" variant="secondary" icon="pencil-square">
                                            Editar
                                        </x-ui.action>

                                        <form
                                            method="POST"
                                            action="{{ route('stations.destroy', $station) }}"
                                            data-confirm="Remover a station {{ $station->name }}? Ela será excluída logicamente."
                                            data-confirm-title="Remover station"
                                            data-confirm-label="Remover"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <x-ui.action type="submit" size="sm" variant="danger" icon="trash">
                                                Remover
                                            </x-ui.action>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-white/10">
                <form method="GET" action="{{ route('stations.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                        @if (request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach

                    <label for="per-page" class="sr-only">Itens por página</label>
                    <select id="per-page" name="per_page" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-gray-50 py-1.5 pl-2 pr-8 text-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300">
                        @foreach ([12, 24, 36] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                        @endforeach
                    </select>
                </form>

                <x-pagination :paginator="$stations" />
            </div>
        </div>
    @endif
</x-layouts.admin>
