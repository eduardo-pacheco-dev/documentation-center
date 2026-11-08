@props([
    'lists',
    'activeList' => null,
    'listCounts',
    'listPayloadFor',
])

@php
    $idleItem = 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.04]';
@endphp

<div class="rounded-3xl border border-gray-200/80 bg-white p-2.5 dark:border-white/10 dark:bg-[#171716]">
    <div class="flex items-center justify-between px-2 py-1.5">
        <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Minhas listas</h2>
        <button
            type="button"
            data-modal-open="task-list-modal"
            class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-white/10 dark:hover:text-indigo-300"
            aria-label="Nova lista"
            title="Nova lista"
        >
            <x-icon name="plus" class="h-4 w-4" />
        </button>
    </div>

    @if ($lists->isEmpty())
        <p class="px-3 py-3 text-xs leading-relaxed text-gray-500 dark:text-gray-400">Nenhuma lista ainda. Crie a primeira para começar.</p>
    @else
        <nav class="mt-1 space-y-0.5">
            @foreach ($lists as $list)
                @php
                    $isActiveList = $activeList !== null && $activeList->is($list);
                @endphp

                <div x-data="{ open: false }" class="group relative">
                    <a
                        href="{{ route('admin.tasks.index', ['view' => 'list', 'list' => $list->getKey()]) }}"
                        class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 pr-9 text-sm font-medium transition {{ $isActiveList ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : $idleItem }}"
                    >
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $list->color->softClasses() }}">
                            <x-icon :name="$list->icon->value" class="h-3.5 w-3.5" />
                        </span>

                        <span class="flex-1 truncate">{{ $list->name }}</span>

                        @if ($listCounts->get($list->getKey(), 0) > 0)
                            <x-ui.badge>{{ $listCounts->get($list->getKey()) }}</x-ui.badge>
                        @endif
                    </a>

                    <div class="absolute right-1 top-1/2 -translate-y-1/2">
                        <button
                            type="button"
                            @click="open = !open"
                            class="rounded-lg p-1 text-gray-400 opacity-100 transition hover:bg-gray-100 hover:text-gray-600 focus:opacity-100 lg:opacity-0 lg:group-hover:opacity-100 dark:hover:bg-white/10 dark:hover:text-gray-200"
                            aria-label="Ações da lista"
                        >
                            <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            @click.outside="open = false"
                            class="absolute right-0 z-20 mt-1 w-40 rounded-xl border border-gray-200 bg-white py-1 dark:border-white/10 dark:bg-[#1c1c1b]"
                        >
                            <button
                                type="button"
                                data-modal-open="task-list-modal"
                                data-list='@json($listPayloadFor($list))'
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10"
                            >
                                <x-icon name="pencil-square" class="h-4 w-4" />
                                Renomear
                            </button>

                            <form
                                method="POST"
                                action="{{ route('admin.todo-lists.destroy', $list) }}"
                                data-confirm-delete="Excluir a lista e todas as suas tarefas?"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                                >
                                    <x-icon name="trash" class="h-4 w-4" />
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </nav>
    @endif
</div>
