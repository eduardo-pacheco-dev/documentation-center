@props([
    'viewMeta',
    'taskCount',
    'activeList' => null,
    'sort',
    'sortOptions',
    'search',
    'scopeHidden',
    'listPayloadFor',
])

<div class="rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
        <div class="flex min-w-0 items-start gap-3">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $activeList !== null ? $activeList->color->softClasses() : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' }}">
                <x-icon :name="$viewMeta['icon']" class="h-5 w-5" />
            </span>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $viewMeta['title'] }}</h2>
                    <x-ui.badge tone="indigo">{{ $taskCount }}</x-ui.badge>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $viewMeta['description'] }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
            @if ($activeList !== null)
                <x-ui.action
                    variant="secondary"
                    icon="pencil-square"
                    data-modal-open="task-list-modal"
                    data-list="{{ json_encode($listPayloadFor($activeList)) }}"
                >
                    Editar lista
                </x-ui.action>
            @endif

            <form method="GET" action="{{ route('admin.tasks.index') }}" class="flex items-center gap-2">
                @foreach ($scopeHidden as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach

                @if ($search !== '')
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                <label for="task-sort" class="sr-only">Ordenar por</label>
                <div class="relative">
                    <select
                        id="task-sort"
                        name="sort"
                        data-auto-submit
                        class="h-10 appearance-none rounded-xl border border-gray-200 bg-gray-50 pl-3 pr-9 text-sm font-medium text-gray-600 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300"
                    >
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </span>
                </div>
            </form>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.tasks.index') }}" class="flex flex-wrap items-center gap-2 border-t border-gray-100 px-5 py-3 sm:px-6 dark:border-white/10">
        @foreach ($scopeHidden as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        @if ($sort !== 'manual')
            <input type="hidden" name="sort" value="{{ $sort }}">
        @endif

        <div class="relative min-w-0 flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                <x-icon name="magnifying-glass" class="h-4 w-4" />
            </span>
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Pesquisar tarefas..."
                class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:bg-white/5"
            >
        </div>

        <x-ui.action type="submit" variant="secondary">Pesquisar</x-ui.action>

        @if ($search !== '')
            <a href="{{ route('admin.tasks.index', $scopeHidden) }}" class="px-2 text-sm font-medium text-gray-500 transition hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300">Limpar</a>
        @endif
    </form>
</div>
