<section id="resources" class="mt-8">
    <div x-data="{ open: false, action: '', method: 'POST', form: {}, updateTemplate: $el.dataset.updateTemplate }" data-update-template="{{ route('admin.projects.resources.update', [$project, '__RESOURCE__']) }}">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                <x-icon name="adjustments-horizontal" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Recursos
            </h2>

            @if ($canPlan)
                <button
                    type="button"
                    @click="form = { name: '', type: 'work', code: '', max_units: 100, cost_per_hour: 0, cost_per_unit: 0, is_active: true }; action = @js(route('admin.projects.resources.store', $project)); method = 'POST'; open = true;"
                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-1.5 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Novo recurso
                </button>
            @endif
        </div>

        <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nome</th>
                        <th class="px-4 py-3 font-medium">Tipo</th>
                        <th class="px-4 py-3 font-medium">Código</th>
                        <th class="px-4 py-3 font-medium">Unidades máx.</th>
                        <th class="px-4 py-3 font-medium">Custo/hora</th>
                        <th class="px-4 py-3 font-medium">Ativo</th>
                        @if ($canPlan)
                            <th class="px-4 py-3 text-right font-medium">Ações</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($resources as $resource)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $resource->name }}</td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                @switch ($resource->type?->value)
                                    @case('work') Trabalho @break
                                    @case('material') Material @break
                                    @case('equipment') Equipamento @break
                                    @case('cost') Custo @break
                                    @default —
                                @endswitch
                            </td>
                            <td class="px-4 py-2 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $resource->code ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ number_format((float) $resource->max_units, 0) }}%</td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ number_format((float) $resource->cost_per_hour, 2, ',', '.') }}</td>
                            <td class="px-4 py-2">
                                @if ($resource->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/15 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">Sim</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">Não</span>
                                @endif
                            </td>
                            @if ($canPlan)
                                <td class="whitespace-nowrap px-4 py-2 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button
                                            type="button"
                                            @click="form = { name: @js($resource->name), type: @js($resource->type?->value), code: @js($resource->code), max_units: @js((float) $resource->max_units), cost_per_hour: @js((float) $resource->cost_per_hour), cost_per_unit: @js((float) $resource->cost_per_unit), is_active: {{ $resource->is_active ? 'true' : 'false' }} }; action = updateTemplate.replace('__RESOURCE__', @js($resource->getKey())); method = 'PUT'; open = true;"
                                            class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                                            aria-label="Editar recurso"
                                        >
                                            <x-icon name="pencil-square" class="h-4 w-4" />
                                        </button>

                                        <form method="POST" action="{{ route('admin.projects.resources.destroy', [$project, $resource]) }}" data-confirm-delete>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400" aria-label="Excluir recurso">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-gray-500 dark:text-gray-400" colspan="7">Nenhum recurso cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($canPlan)
            <div x-show="open" x-transition style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" role="dialog" aria-modal="true" @click.self="open = false" @keydown.escape.window="open = false">
                <form method="POST" :action="action" class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                    @csrf
                    <input type="hidden" name="_method" :value="method">

                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recurso</h3>
                        <button type="button" @click="open = false" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Fechar">
                            <x-icon name="x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="resource-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
                            <input type="text" name="name" id="resource-name" x-model="form.name" required class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="resource-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                            <select name="type" id="resource-type" x-model="form.type" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                <option value="work">Trabalho</option>
                                <option value="material">Material</option>
                                <option value="equipment">Equipamento</option>
                                <option value="cost">Custo</option>
                            </select>
                        </div>

                        <div>
                            <label for="resource-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
                            <input type="text" name="code" id="resource-code" x-model="form.code" maxlength="50" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="resource-units" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidades máx. (%)</label>
                            <input type="number" name="max_units" id="resource-units" min="0" step="1" x-model="form.max_units" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="resource-hour" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custo por hora</label>
                            <input type="number" name="cost_per_hour" id="resource-hour" min="0" step="0.01" x-model="form.cost_per_hour" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="resource-unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custo por unidade</label>
                            <input type="number" name="cost_per_unit" id="resource-unit" min="0" step="0.01" x-model="form.cost_per_unit" class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="resource-active" value="1" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="resource-active" class="text-sm text-gray-700 dark:text-gray-300">Ativo</label>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" @click="open = false" class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Cancelar</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200">
                            <x-icon name="check" class="h-4 w-4" />
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</section>
