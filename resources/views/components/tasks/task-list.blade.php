@props([
    'tasks',
    'activeList' => null,
    'sort' => 'manual',
])

<div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <ul class="max-h-[24rem] divide-y divide-gray-100 overflow-y-auto dark:divide-white/10">
        @foreach ($tasks as $task)
            <x-tasks.task-item :task="$task" :active-list="$activeList" :sort="$sort" />
        @endforeach
    </ul>
</div>
