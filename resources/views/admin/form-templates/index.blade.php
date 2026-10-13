<x-layouts.admin title="Templates de formulário">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="document-duplicate" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Templates de formulário
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Estruturas reutilizáveis compartilhadas com todos os usuários.</p>
        </div>

        <a
            href="{{ route('admin.forms.index') }}"
            class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
        >
            Ir para formulários
        </a>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        <form method="GET" action="{{ route('admin.form-templates.index') }}" class="flex flex-wrap items-center gap-2">
            @foreach (['sort', 'direction', 'per_page'] as $param)
                @if (request($param) !== null && request($param) !== '')
                    <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                @endif
            @endforeach

            <div class="relative w-full max-w-sm sm:w-80 sm:max-w-none">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por nome ou tipo..."
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
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
                    href="{{ route('admin.form-templates.index') }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    Limpar
                </a>
            @endif
        </form>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto lg:overflow-x-visible">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="rounded-t-xl bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <x-sort-header column="name" label="Nome" icon="document-duplicate" default="name" class="px-5 py-3" />
                        <x-sort-header column="type" label="Tipo" class="px-5 py-3" />
                        <th class="px-5 py-3 font-medium">Perguntas</th>
                        <th class="px-5 py-3 font-medium">Criado por</th>
                        <x-sort-header column="updated_at" label="Atualizado" default="updated_at" class="px-5 py-3" />
                        <th class="px-5 py-3 text-right font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($templates as $template)
                        <tr>
                            <td class="px-5 py-3">
                                @can('update', $template)
                                    <a
                                        href="{{ route('admin.form-templates.edit', $template) }}"
                                        class="flex items-center gap-2 font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                    >
                                        <x-icon name="document-duplicate" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $template->name }}
                                    </a>
                                @else
                                    <span class="flex items-center gap-2 font-medium text-gray-900 dark:text-gray-100">
                                        <x-icon name="document-duplicate" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $template->name }}
                                    </span>
                                @endcan
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $template->type ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">
                                {{ collect($template->steps)->sum(fn ($step) => count($step['fields'] ?? [])) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $template->user?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-relative-time :value="$template->updated_at" class="text-gray-500 dark:text-gray-400" />
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="relative inline-block text-left">
                                    <button
                                        type="button"
                                        data-dropdown-toggle
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                        class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                    >
                                        <span class="sr-only">Ações</span>
                                        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
                                    </button>

                                    <div
                                        data-dropdown-menu
                                        class="absolute right-0 z-10 mt-1 hidden w-40 rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:shadow-gray-950"
                                    >
                                        @can('update', $template)
                                            <a href="{{ route('admin.form-templates.edit', $template) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                                <x-icon name="pencil-square" class="h-4 w-4" />
                                                Editar
                                            </a>
                                        @endcan
                                        @can('delete', $template)
                                            <form method="POST" action="{{ route('admin.form-templates.destroy', $template) }}" data-confirm="Excluir template {{ $template->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                    Excluir
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="6">
                                <span class="inline-flex items-center gap-2">
                                    <x-icon name="document-duplicate" class="h-4 w-4" />
                                    @if ($search !== '')
                                        Nenhum template encontrado para a busca informada.
                                    @else
                                        Nenhum template cadastrado até o momento. Abra um formulário e use "Salvar como template".
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
            <form method="GET" action="{{ route('admin.form-templates.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach (['search', 'sort', 'direction'] as $param)
                    @if (request($param) !== null && request($param) !== '')
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

            <x-pagination :paginator="$templates" />
        </div>
    </div>

    <script>
        (function () {
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
        })();
    </script>
</x-layouts.admin>
