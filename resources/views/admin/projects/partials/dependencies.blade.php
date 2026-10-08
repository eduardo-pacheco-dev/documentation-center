<section id="dependencies" class="mt-8">
    <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
        <x-icon name="link" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
        Dependências
    </h2>

    @if ($canPlan)
        <form method="POST" action="{{ route('admin.projects.dependencies.store', $project) }}" class="mt-3 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @csrf

            <div>
                <label for="dep-predecessor" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Predecessora</label>
                <select name="predecessor_id" id="dep-predecessor" required class="mt-1 block w-56 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach ($tasks as $task)
                        <option value="{{ $task->getKey() }}" @selected(old('predecessor_id') == $task->getKey())>{{ $task->wbs }} · {{ $task->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="dep-successor" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Sucessora</label>
                <select name="successor_id" id="dep-successor" required class="mt-1 block w-56 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach ($tasks as $task)
                        <option value="{{ $task->getKey() }}" @selected(old('successor_id') == $task->getKey())>{{ $task->wbs }} · {{ $task->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="dep-type" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tipo</label>
                <select name="type" id="dep-type" class="mt-1 block rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="fs" @selected(old('type', 'fs') === 'fs')>Fim–Início</option>
                    <option value="ss" @selected(old('type') === 'ss')>Início–Início</option>
                    <option value="ff" @selected(old('type') === 'ff')>Fim–Fim</option>
                    <option value="sf" @selected(old('type') === 'sf')>Início–Fim</option>
                </select>
            </div>

            <div>
                <label for="dep-lag" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Atraso (dias úteis)</label>
                <input type="number" name="lag_days" id="dep-lag" step="0.25" value="{{ old('lag_days', 0) }}" class="mt-1 block w-24 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="plus" class="h-4 w-4" />
                Vincular
            </button>
        </form>
    @endif

    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Predecessora</th>
                    <th class="px-4 py-3 font-medium">Sucessora</th>
                    <th class="px-4 py-3 font-medium">Tipo</th>
                    <th class="px-4 py-3 font-medium">Atraso</th>
                    @if ($canPlan)
                        <th class="px-4 py-3 text-right font-medium">Ações</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($dependencies as $dependency)
                    <tr>
                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $dependency->predecessor?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $dependency->successor?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                            @switch ($dependency->type->value)
                                @case('fs') Fim–Início @break
                                @case('ss') Início–Início @break
                                @case('ff') Fim–Fim @break
                                @default Início–Fim
                            @endswitch
                        </td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ number_format($dependency->lag_minutes / max(1, $project->calendars()->orderByDesc('is_default')->orderBy('id')->value('minutes_per_day') ?? 480), 2, ',', '.') }} d</td>
                        @if ($canPlan)
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.projects.dependencies.destroy', [$project, $dependency]) }}" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400" aria-label="Remover dependência">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-500 dark:text-gray-400" colspan="5">Nenhuma dependência registrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
