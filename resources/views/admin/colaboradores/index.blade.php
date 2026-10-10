<x-layouts.admin title="Colaboradores">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);

        $hasFilters = $search !== '' || $status !== null || $regional !== null || $uf !== null;
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="user" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Colaboradores
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie a equipe técnica e demais colaboradores.</p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.colaboradores.export', array_filter(['search' => $search, 'status' => $status, 'regional' => $regional, 'uf' => $uf])) }}"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                <x-icon name="arrow-down-tray" class="h-4 w-4" />
                Exportar
            </a>

            <button
                type="button"
                data-modal-open="colaborador-import-modal"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                Importar em massa
            </button>

            <button
                type="button"
                data-create-modal-open
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Novo colaborador
            </button>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form
            method="GET"
            action="{{ route('admin.colaboradores.index') }}"
            class="flex flex-wrap items-center gap-2"
            data-search-form
        >
            @foreach (['view', 'sort', 'direction', 'per_page', 'status', 'regional', 'uf'] as $param)
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
                    placeholder="Buscar por nome, função, e-mail ou CPF..."
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

            @if ($hasFilters)
                <a
                    href="{{ route('admin.colaboradores.index', array_filter(['view' => $view])) }}"
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
                @foreach (['' => 'Todos os status', 'active' => 'Ativos', 'inactive' => 'Inativos'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === ($value ?: null))>{{ $label }}</option>
                @endforeach
            </select>

            <label for="regional-filter" class="sr-only">Filtrar por regional</label>
            <select
                id="regional-filter"
                name="regional"
                onchange="this.form.submit()"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-3 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="">Todas as regionais</option>
                @foreach ($regionals as $regionalOption)
                    <option value="{{ $regionalOption }}" @selected($regional === $regionalOption)>{{ $regionalOption }}</option>
                @endforeach
            </select>

            <label for="uf-filter" class="sr-only">Filtrar por UF</label>
            <select
                id="uf-filter"
                name="uf"
                onchange="this.form.submit()"
                class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-3 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="">Todas as UFs</option>
                @foreach (\App\Enums\Uf::cases() as $ufOption)
                    <option value="{{ $ufOption->value }}" @selected($uf === $ufOption->value)>{{ $ufOption->value }} - {{ $ufOption->label() }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
            @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
                <a
                    href="{{ route('admin.colaboradores.index', array_merge($viewQuery, ['view' => $mode])) }}"
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
                            <x-sort-header column="name" label="Nome" icon="user" class="px-5 py-3" />
                            <x-sort-header column="role" label="Função" class="px-5 py-3" />
                            <th class="px-5 py-3 font-medium">CPF</th>
                            <x-sort-header column="email" label="E-mail" class="px-5 py-3" />
                            <x-sort-header column="status" label="Status" icon="flag" class="px-5 py-3" />
                            <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                            <th class="px-5 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($colaboradores as $colaborador)
                            <tr>
                                <td class="px-5 py-3">
                                    <a
                                        href="{{ route('admin.colaboradores.show', $colaborador) }}"
                                        class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        <x-icon name="user" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $colaborador->name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $colaborador->role ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $colaborador->document ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $colaborador->email ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-colaborador-status :colaborador="$colaborador" />
                                </td>
                                <td class="px-5 py-3">
                                    <x-relative-time :value="$colaborador->updated_at" class="text-gray-500 dark:text-gray-400" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-colaborador-actions-dropdown :colaborador="$colaborador" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="7">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="user" class="h-4 w-4" />
                                        @if ($hasFilters)
                                            Nenhum colaborador encontrado para os filtros informados.
                                        @else
                                            Nenhum colaborador cadastrado até o momento.
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                <form method="GET" action="{{ route('admin.colaboradores.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    @foreach (['search', 'view', 'sort', 'direction', 'status', 'regional', 'uf'] as $param)
                        @if (request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach
                    <label for="per-page" class="sr-only">Itens por página</label>
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

                <x-pagination :paginator="$colaboradores" />
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($colaboradores as $colaborador)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-icon name="user" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.colaboradores.show', $colaborador) }}" class="block truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                    {{ $colaborador->name }}
                                </a>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $colaborador->role ?? 'Função não informada' }}</p>
                            </div>
                        </div>

                        <x-colaborador-actions-dropdown :colaborador="$colaborador" />
                    </div>

                    <div class="mt-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        @if ($colaborador->phone)
                            <p class="flex items-center gap-1">
                                <x-icon name="phone" class="h-3.5 w-3.5" />
                                {{ $colaborador->phone }}
                            </p>
                        @endif
                        @if ($colaborador->email)
                            <p class="flex items-center gap-1">
                                <x-icon name="envelope" class="h-3.5 w-3.5" />
                                {{ $colaborador->email }}
                            </p>
                        @endif
                        @if ($colaborador->document)
                            <p class="font-medium tracking-tight text-gray-600 dark:text-gray-300">{{ $colaborador->document }}</p>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                        <x-colaborador-status :colaborador="$colaborador" />
                        <x-relative-time :value="$colaborador->updated_at" class="text-xs text-gray-500 dark:text-gray-400" />
                    </div>
                </div>
            @empty
                <p class="col-span-full flex items-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="user" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($hasFilters)
                        Nenhum colaborador encontrado para os filtros informados.
                    @else
                        Nenhum colaborador cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.colaboradores.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status', 'regional', 'uf'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">Itens por página</label>
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

            <x-pagination :paginator="$colaboradores" />
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($colaboradores as $colaborador)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-icon name="user" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.colaboradores.show', $colaborador) }}" class="truncate text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                            {{ $colaborador->name }}
                        </a>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $colaborador->role ?? 'Função não informada' }}{{ $colaborador->email ? ' · '.$colaborador->email : '' }}
                        </p>
                    </div>

                    <span class="hidden whitespace-nowrap text-sm text-gray-500 md:inline dark:text-gray-400">
                        {{ $colaborador->document ?? '—' }}
                    </span>

                    <div class="hidden sm:block">
                        <x-colaborador-status :colaborador="$colaborador" />
                    </div>

                    <x-colaborador-actions-dropdown :colaborador="$colaborador" />
                </div>
            @empty
                <p class="flex items-center gap-2 px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                    <x-icon name="user" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    @if ($hasFilters)
                        Nenhum colaborador encontrado para os filtros informados.
                    @else
                        Nenhum colaborador cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
            <form method="GET" action="{{ route('admin.colaboradores.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'view', 'sort', 'direction', 'status', 'regional', 'uf'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
                <label for="per-page" class="sr-only">Itens por página</label>
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

            <x-pagination :paginator="$colaboradores" />
        </div>
    @endif

    @include('admin.colaboradores.partials.create-modal')

    <div
        id="colaborador-import-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="colaborador-import-modal-title"
        @if ($errors->any() && old('modal') === 'import') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="colaborador-import-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="arrow-up-tray" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Importar colaboradores
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Envie uma planilha Excel (.xlsx) com um colaborador por linha.</p>
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
                    <p class="font-medium">Nenhum colaborador foi criado. Corrija os itens abaixo e tente novamente:</p>

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
                <p>Colunas: <span class="font-medium">Nome, Regime de Contrato, Regional, UF, PIS, Função, CPF, CNPJ, RG, Órgão Emissor, Data de Nascimento, Nome da Mãe, Contato, E-mail, Status, Observações</span>.</p>
                <p class="mt-1">Nome é obrigatório. Status: ativo ou inativo. Regime: CLT, PJ, Estágio, Aprendiz ou Temporário. UF: sigla com 2 letras. Data de Nascimento: dd/mm/aaaa.</p>
                <a
                    href="{{ route('admin.colaboradores.import.template') }}"
                    class="mt-2 inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    <x-icon name="arrow-down-tray" class="h-4 w-4" />
                    Baixar modelo
                </a>
            </div>

            <form method="POST" action="{{ route('admin.colaboradores.import') }}" enctype="multipart/form-data" class="mt-5" data-import-form>
                @csrf
                <input type="hidden" name="modal" value="import">

                <label for="import-file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planilha (.xlsx)</label>
                <input
                    type="file"
                    name="file"
                    id="import-file"
                    accept=".xlsx,.xls"
                    required
                    data-import-file
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white dark:file:bg-white dark:file:text-gray-900"
                >
                @error('file')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div data-import-progress hidden class="mt-4">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                        <div data-import-progress-bar class="h-full w-0 rounded-full bg-indigo-500 transition-all duration-200" style="width: 0%"></div>
                    </div>
                    <p data-import-progress-label class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enviando planilha...</p>
                </div>

                <div data-import-errors hidden class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    <p class="font-medium">Nenhum colaborador foi criado. Corrija os itens abaixo e tente novamente:</p>

                    <ul data-import-errors-list class="mt-2 list-disc space-y-1 pl-5"></ul>
                </div>

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
                        data-import-submit
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-60"
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

            const savedView = localStorage.getItem('colaboradores-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('colaboradores-view', link.dataset.viewToggle);
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

        const openModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeModal = (modal) => {
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
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

            const opener = event.target.closest('[data-modal-open], [data-create-modal-open]');

            if (opener) {
                event.preventDefault();
                closeDropdowns();

                const modal = opener.dataset.modalOpen
                    ? document.getElementById(opener.dataset.modalOpen)
                    : document.querySelector('[data-create-modal]');

                if (modal) {
                    openModal(modal);
                }

                return;
            }

            const closer = event.target.closest('[data-modal-close], [data-create-modal-close]');

            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdowns();
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
            }
        });

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

        const createModal = document.querySelector('[data-create-modal]');

        if (createModal?.hasAttribute('data-open-on-load')) {
            openModal(createModal);
        }

        const wizard = createModal?.querySelector('[data-wizard]');

        if (wizard) {
            const steps = Array.from(wizard.querySelectorAll('[data-wizard-step]'));
            const label = createModal.querySelector('[data-wizard-label]');
            const counter = createModal.querySelector('[data-wizard-counter]');
            const progressBar = createModal.querySelector('[data-wizard-progress]');
            const prevButton = createModal.querySelector('[data-wizard-prev]');
            const nextButton = createModal.querySelector('[data-wizard-next]');
            const submitButton = createModal.querySelector('[data-wizard-submit]');

            const stepLabels = ['Dados pessoais', 'Documentos', 'Contrato e contato'];
            const progressWidths = ['w-1/3', 'w-2/3', 'w-full'];

            let current = 0;

            const render = () => {
                steps.forEach((step, index) => {
                    step.classList.toggle('hidden', index !== current);
                });

                if (label) {
                    label.textContent = stepLabels[current] || '';
                }

                if (counter) {
                    counter.textContent = 'Passo ' + (current + 1) + ' de ' + steps.length;
                }

                if (progressBar) {
                    progressBar.classList.remove('w-1/3', 'w-2/3', 'w-full');
                    progressBar.classList.add(progressWidths[current] || 'w-full');
                }

                prevButton?.classList.toggle('hidden', current === 0);
                nextButton?.classList.toggle('hidden', current === steps.length - 1);
                submitButton?.classList.toggle('hidden', current !== steps.length - 1);
                submitButton?.classList.toggle('inline-flex', current === steps.length - 1);
            };

            const goTo = (index) => {
                current = Math.max(0, Math.min(index, steps.length - 1));
                render();
            };

            const firstErrorStep = () => {
                const index = steps.findIndex((step) => step.querySelector('.text-red-600'));

                return index === -1 ? 0 : index;
            };

            nextButton?.addEventListener('click', () => {
                const step = steps[current];
                const invalid = Array.from(step.querySelectorAll('input, select, textarea')).find((field) => !field.checkValidity());

                if (invalid) {
                    invalid.reportValidity();

                    return;
                }

                goTo(current + 1);
            });

            prevButton?.addEventListener('click', () => goTo(current - 1));

            document.querySelectorAll('[data-create-modal-open]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            goTo(firstErrorStep());
        }

        const importModal = document.getElementById('colaborador-import-modal');

        if (importModal?.hasAttribute('data-open')) {
            openModal(importModal);
        }
    </script>

    <script>
        (function () {
            const form = document.querySelector('[data-import-form]');

            if (!form) {
                return;
            }

            const fileInput = form.querySelector('[data-import-file]');
            const submitButton = form.querySelector('[data-import-submit]');
            const progress = form.querySelector('[data-import-progress]');
            const progressBar = form.querySelector('[data-import-progress-bar]');
            const progressLabel = form.querySelector('[data-import-progress-label]');
            const errorsBox = form.querySelector('[data-import-errors]');
            const errorsList = form.querySelector('[data-import-errors-list]');

            const resetErrors = () => {
                if (errorsBox) {
                    errorsBox.hidden = true;
                }

                if (errorsList) {
                    errorsList.innerHTML = '';
                }
            };

            const showErrors = (messages) => {
                if (!errorsBox || !errorsList) {
                    return;
                }

                errorsList.innerHTML = '';

                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    errorsList.appendChild(item);
                });

                errorsBox.hidden = false;
                errorsBox.scrollIntoView({ block: 'center', behavior: 'smooth' });
            };

            const setProgress = (value, label) => {
                if (!progress || !progressBar) {
                    return;
                }

                progress.hidden = false;
                progressBar.style.width = value + '%';

                if (progressLabel && label) {
                    progressLabel.textContent = label;
                }
            };

            const enable = () => {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();

                if (!fileInput || fileInput.files.length === 0) {
                    fileInput?.reportValidity();

                    return;
                }

                resetErrors();

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', (progressEvent) => {
                    if (!progressEvent.lengthComputable) {
                        return;
                    }

                    const percent = Math.min(100, Math.round((progressEvent.loaded / progressEvent.total) * 100));
                    setProgress(percent, percent < 100 ? 'Enviando planilha... ' + percent + '%' : 'Processando planilha...');
                });

                xhr.addEventListener('load', () => {
                    let payload = {};

                    try {
                        payload = JSON.parse(xhr.responseText);
                    } catch (error) {
                        payload = {};
                    }

                    if (xhr.status >= 200 && xhr.status < 300) {
                        setProgress(100, 'Concluído!');
                        window.location = payload.redirect || window.location.href;

                        return;
                    }

                    if (progress) {
                        progress.hidden = true;
                    }

                    enable();

                    const errors = payload.errors || {};
                    let messages = errors.import || Object.values(errors).flat();

                    if (!Array.isArray(messages) || messages.length === 0) {
                        messages = [payload.message || 'Não foi possível importar a planilha.'];
                    }

                    showErrors(messages);
                    window.toasts?.error('Não foi possível importar a planilha.');
                });

                xhr.addEventListener('error', () => {
                    if (progress) {
                        progress.hidden = true;
                    }

                    enable();
                    window.toasts?.error('Erro de rede ao enviar a planilha.');
                });

                if (submitButton) {
                    submitButton.disabled = true;
                }

                setProgress(0, 'Enviando planilha...');
                xhr.send(new FormData(form));
            });
        })();
    </script>
</x-layouts.admin>