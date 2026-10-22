@props([
    'tasks',
    'activeList' => null,
    'sort' => 'manual',
])

<div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]" x-data="{ open: false }">
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]"
    >
        <span class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
            <x-icon name="check-circle" class="h-4 w-4 text-gray-400" />
            Concluídas
            <x-ui.badge>{{ $tasks->count() }}</x-ui.badge>
        </span>
        <span class="text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''">
            <x-icon name="chevron-down" class="h-4 w-4" />
        </span>
    </button>

    <ul x-show="open" x-cloak class="max-h-[24rem] divide-y divide-gray-100 overflow-y-auto border-t border-gray-100 dark:divide-white/10 dark:border-white/10">
        @foreach ($tasks as $task)
            <x-tasks.task-item :task="$task" :active-list="$activeList" :sort="$sort" />
        @endforeach
    </ul>
</div>
