<x-layouts.admin title="Ordens de serviço">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
        $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Ordens de serviço
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Abra e acompanhe as ordens de serviço dos seus clientes.</p>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                data-modal-open="work-order-import-modal"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                Importar em massa
            </button>

            <button
                type="button"
                data-modal-open="work-order-modal"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Nova OS
            </button>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form
            method="GET"
            action="{{ route('admin.work-orders.index') }}"
            class="flex flex-wrap items-center gap-2"
            data-search-form
        >
            @foreach (['view', 'sort', 'direction', 'per_page'] as $param)
                @if (request($param))
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach

            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por número, título ou cliente..."
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    data-search-input
                >
            </div>

            <button
                type="submit"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                Buscar
            </button>

            @if ($search !== '')
                <a
                    href="{{ route('admin.work-orders.index', array_filter(['status' => $status, 'view' => $view])) }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif

            <label for="status-filter" class="sr-only">Filtrar por status</label>
            <select
                id="status-filter"
                name="status"
                onchange="this.form.submit()"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-3 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="" @selected($status === null)>Todos os status</option>
                @foreach (\App\Enums\WorkOrderStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.work-orders.index', array_merge($viewQuery, ['view' => $mode])) }}"
                    data-view-toggle="{{ $mode }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm {{ $view === $mode ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                >
                    <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($view === 'table')
        <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="overflow-x-auto lg:overflow-x-visible">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="rounded-t-xl bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <x-sort-header column="number" label="Número" icon="clipboard-document-list" class="px-5 py-3" />
                            <x-sort-header column="title" label="Título / Cliente" class="px-5 py-3" />
                            <x-sort-header column="priority" label="Prioridade" class="px-5 py-3" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3" />
                            <x-sort-header column="opened_at" label="Abertura" class="px-5 py-3" />
                            <x-sort-header column="due_at" label="Previsão" class="px-5 py-3" />
                            <x-sort-header column="total" label="Total" class="px-5 py-3" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($workOrders as $workOrder)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <a
                                        href="{{ route('admin.work-orders.show', $workOrder) }}"
                                        class="font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        {{ $workOrder->number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3">
                                    <p class="max-w-[18rem] truncate font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->title }}</p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $workOrder->client->name }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <x-work-order-priority :work-order="$workOrder" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <x-work-order-status :work-order="$workOrder" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $workOrder->opened_at->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $workOrder->due_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $money((float) $workOrder->total) }}</td>
                                <td class="px-5 py-3">
                                    <x-relative-time :value="$workOrder->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-work-order-actions-dropdown :work-order="$workOrder" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="9">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="clipboard-document-list" class="h-4 w-4" />
                                        @if ($search !== '')
                                            Nenhuma ordem de serviço encontrada para "{{ $search }}".
                                        @else
                                            Nenhuma ordem de serviço cadastrada até o momento.
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                <form method="GET" action="{{ route('admin.work-orders.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                        @if (request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach
                    <label for="per-page" class="sr-only">OS por página</label>
                    <select
                        id="per-page"
                        name="per_page"
                        onchange="this.form.submit()"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                        @foreach ([12, 24, 36] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                        @endforeach
                    </select>
                </form>

                <x-pagination :paginator="$workOrders" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($workOrders as $workOrder)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="block truncate text-sm font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                {{ $workOrder->number }}
                            </a>
                            <p class="truncate text-sm text-gray-700 dark:text-gray-300">{{ $workOrder->title }}</p>
                        </div>

                        <x-work-order-actions-dropdown :work-order="$workOrder" />
                    </div>

                    <p class="mt-2 flex items-center gap-1 truncate text-xs text-gray-500 dark:text-gray-400">
                        <x-icon name="building-office" class="h-3.5 w-3.5" />
                        {{ $workOrder->client->name }}
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-work-order-status :work-order="$workOrder" />
                        <x-work-order-priority :work-order="$workOrder" />
                    </div>

                    <div class="mt-4 flex items-end justify-between gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <p>{{ $workOrder->opened_at->format('d/m/Y') }}@if ($workOrder->due_at) &rarr; {{ $workOrder->due_at->format('d/m/Y') }}@endif</p>
                            <p class="mt-0.5">{{ $workOrder->items_count }} {{ $workOrder->items_count === 1 ? 'item' : 'itens' }}</p>
                        </div>
                        <span class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $workOrder->total) }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="clipboard-document-list" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '')
                        Nenhuma ordem de serviço encontrada para "{{ $search }}".
                    @else
                        Nenhuma ordem de serviço cadastrada até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.work-orders.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">OS por página</label>
                <select
                    id="per-page"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$workOrders" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($workOrders as $workOrder)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="clipboard-document-list" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $workOrder->number }} &middot; {{ $workOrder->title }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $workOrder->client->name }}</p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm font-medium text-gray-700 md:inline dark:text-gray-200">
                        {{ $money((float) $workOrder->total) }}
                    </span>

                    <div class="hidden sm:block">
                        <x-work-order-status :work-order="$workOrder" />
                    </div>

                    <x-work-order-actions-dropdown :work-order="$workOrder" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="clipboard-document-list" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($search !== '')
                        Nenhuma ordem de serviço encontrada para "{{ $search }}".
                    @else
                        Nenhuma ordem de serviço cadastrada até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.work-orders.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">OS por página</label>
                <select
                    id="per-page"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    @foreach ([12, 24, 36] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
                    @endforeach
                </select>
            </form>

            <x-pagination :paginator="$workOrders" />
        </div>
    @endif

    <div
        id="work-order-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="work-order-modal-title"
        @if ($errors->any() && old('modal') === 'create') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-3xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="work-order-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Nova ordem de serviço
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Abra uma OS para um cliente e monte a lista de itens.</p>
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

            <form method="POST" action="{{ route('admin.work-orders.store') }}" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="create">

                @include('admin.work-orders.partials.form', ['workOrder' => null])

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
                        Criar ordem de serviço
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="work-order-import-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="work-order-import-modal-title"
        @if ($errors->any() && old('modal') === 'import') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="work-order-import-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="arrow-up-tray" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Importar ordens de serviço
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Envie uma planilha Excel (.xlsx) com uma OS por linha.</p>
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

            @if ($errors->any() && old('modal') === 'import')
                <div
                    data-modal-error
                    class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">Nenhuma OS foi criada. Corrija os itens abaixo e tente novamente:</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @forelse ($errors->get('import') as $message)
                            <li>{{ $message }}</li>
                        @empty
                            <li>Verifique o arquivo enviado.</li>
                        @endforelse
                    </ul>
                </div>
            @endif

            <div class="mt-4 rounded-md border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">
                <p>Colunas: <span class="font-medium">Cliente, Título, Descrição, Prioridade, Status, Abertura, Previsão, Observações</span>.</p>
                <p class="mt-1">O cliente precisa já existir. Prioridade: baixa, normal, alta ou urgente. Status: aberta, em andamento, concluída ou cancelada. Datas em dd/mm/aaaa.</p>
                <a
                    href="{{ route('admin.work-orders.import.template') }}"
                    class="mt-2 inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    <x-icon name="arrow-down-tray" class="h-4 w-4" />
                    Baixar modelo
                </a>
            </div>

            <form method="POST" action="{{ route('admin.work-orders.import') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="import">

                <label for="import-file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planilha (.xlsx)</label>
                <input
                    type="file"
                    name="file"
                    id="import-file"
                    accept=".xlsx,.xls"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white dark:file:bg-white dark:file:text-gray-900"
                >
                @error('file')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

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
                        <x-icon name="arrow-up-tray" class="h-4 w-4" />
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('work-orders-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('work-orders-view', link.dataset.viewToggle);
            });
        });

        const closeDropdowns = (except) => {
            document.querySelectorAll('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
                if (menu !== except) {
                    menu.classList.add('hidden');
                    menu.previousElementSibling.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-dropdown-toggle]');

            if (toggle) {
                const menu = toggle.nextElementSibling;
                const willOpen = menu.classList.contains('hidden');
                closeDropdowns(willOpen ? menu : null);
                menu.classList.toggle('hidden', !willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));
                return;
            }

            if (!event.target.closest('[data-dropdown-menu]')) {
                closeDropdowns();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdowns();
            }
        });

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
                closeDropdowns();

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

        const workOrderModal = document.getElementById('work-order-modal');
        if (workOrderModal?.hasAttribute('data-open')) {
            openModal(workOrderModal);
        }

        const workOrderImportModal = document.getElementById('work-order-import-modal');
        if (workOrderImportModal?.hasAttribute('data-open')) {
            openModal(workOrderImportModal);
        }

        const searchForm = document.querySelector('[data-search-form]');
        const searchInput = document.querySelector('[data-search-input]');

        if (searchForm && searchInput) {
            let searchTimer;

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => searchForm.submit(), 400);
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    clearTimeout(searchTimer);
                }
            });

            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        }
    </script>
</x-layouts.admin>
