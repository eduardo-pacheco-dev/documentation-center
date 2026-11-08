@props([
    'counts',
    'view',
    'activeList' => null,
])

@php
    $smartViews = [
        ['key' => 'my_day', 'label' => 'Meu Dia', 'icon' => 'sun', 'count' => $counts['my_day']],
        ['key' => 'important', 'label' => 'Importante', 'icon' => 'star', 'count' => $counts['important']],
        ['key' => 'planned', 'label' => 'Planejado', 'icon' => 'calendar-days', 'count' => $counts['planned']],
        ['key' => 'today', 'label' => 'Hoje', 'icon' => 'clock', 'count' => $counts['today']],
        ['key' => 'week', 'label' => 'Próximos 7 dias', 'icon' => 'chart-bar', 'count' => $counts['week']],
        ['key' => 'all', 'label' => 'Todas as tarefas', 'icon' => 'clipboard-document', 'count' => $counts['all']],
        ['key' => 'completed', 'label' => 'Concluídas', 'icon' => 'check-circle', 'count' => $counts['completed']],
    ];

    $activeItem = 'bg-indigo-600 text-white';
    $idleItem = 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.04]';
@endphp

<div class="rounded-3xl border border-gray-200/80 bg-white p-2.5 dark:border-white/10 dark:bg-[#171716]">
    <p class="px-3 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Vistas</p>

    <nav class="space-y-0.5">
        @foreach ($smartViews as $item)
            @php
                $isActive = $view === $item['key'] && $activeList === null;
                $iconColor = match ($item['key']) {
                    'my_day', 'important' => 'text-amber-500',
                    'planned' => 'text-indigo-500',
                    default => 'text-gray-400',
                };
            @endphp

            <a
                href="{{ route('admin.tasks.index', ['view' => $item['key']]) }}"
                class="group flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $isActive ? $activeItem : $idleItem }}"
            >
                <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0 {{ $isActive ? 'text-white' : $iconColor }}" />
                <span class="flex-1 truncate">{{ $item['label'] }}</span>

                @if ($item['key'] === 'planned' && $counts['overdue'] > 0)
                    <x-ui.badge :tone="$isActive ? 'inverse' : 'red'" :title="$counts['overdue'].' tarefa(s) atrasada(s)'">
                        {{ $counts['overdue'] }}
                    </x-ui.badge>
                @endif

                @if ($item['count'] > 0)
                    <x-ui.badge :tone="$isActive ? 'inverse' : 'gray'">{{ $item['count'] }}</x-ui.badge>
                @endif
            </a>
        @endforeach
    </nav>
</div>
