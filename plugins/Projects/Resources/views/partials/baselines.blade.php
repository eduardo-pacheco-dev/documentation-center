<section id="baselines" class="mt-8 hidden" data-project-panel>
    <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
        <x-icon name="flag" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
        Linhas de base
    </h2>

    @if ($canPlan)
        <form method="POST" action="{{ route('admin.projects.baselines.store', $project) }}" class="mt-3 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @csrf

            <div class="grow">
                <label for="baseline-name" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nome da linha de base</label>
                <input type="text" name="name" id="baseline-name" required placeholder="Ex.: Aprovado pela diretoria" value="{{ old('name') }}" class="mt-1 block w-full max-w-sm rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="flag" class="h-4 w-4" />
                Salvar linha de base
            </button>
        </form>
    @endif

    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Nome</th>
                    <th class="px-4 py-3 font-medium">Salva em</th>
                    @if ($canPlan)
                        <th class="px-4 py-3 text-right font-medium">Ações</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($baselines as $baseline)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $baseline->name }}</td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $baseline->saved_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        @if ($canPlan)
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.projects.baselines.restore', [$project, $baseline]) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                                    >
                                        Restaurar no plano
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-500 dark:text-gray-400" colspan="3">Nenhuma linha de base salva.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
