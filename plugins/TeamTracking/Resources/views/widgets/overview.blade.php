@php
    $indexUrl = route('admin.teams.index');
@endphp

<section class="flex h-full flex-col rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="users" class="h-4 w-4" />
            </span>
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Equipes</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $activeCount }} {{ $activeCount === 1 ? 'equipe ativa' : 'equipes ativas' }}</p>
            </div>
        </div>

        @if ($attentionCount > 0)
            <x-ui.badge tone="red">{{ $attentionCount }} em atenção</x-ui.badge>
        @else
            <x-ui.badge tone="green">Em dia</x-ui.badge>
        @endif
    </div>

    <div class="flex-1">
        @forelse ($teams as $team)
            <a href="{{ route('admin.teams.show', $team) }}" class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 transition last:border-b-0 hover:bg-gray-50/70 dark:border-white/10 dark:hover:bg-white/[0.03]">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $team->name }}</p>
                    <p class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $team->members_count }} {{ $team->members_count === 1 ? 'membro' : 'membros' }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $team->leader?->full_name ?? 'Sem líder' }}</span>
                        @if ($team->latestCheckIn)
                            <span aria-hidden="true">·</span>
                            <x-ui.badge :tone="$team->latestCheckIn->status->tone()">{{ $team->latestCheckIn->status->label() }}</x-ui.badge>
                            <span>{{ $team->latestCheckIn->health_score }}/5</span>
                        @endif
                    </p>
                </div>
                <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
            </a>
        @empty
            <div class="px-5 py-10 text-center">
                <span class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 dark:bg-white/5 dark:text-gray-500">
                    <x-icon name="users" class="h-5 w-5" />
                </span>
                <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Nenhuma equipe ativa</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cadastre uma equipe para começar o acompanhamento.</p>
            </div>
        @endforelse
    </div>

    <div class="border-t border-gray-100 px-5 py-3 dark:border-white/10">
        <a href="{{ $indexUrl }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            Ver equipes
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        </a>
    </div>
</section>
