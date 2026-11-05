@props(['followUp'])

<article class="flex h-full flex-col rounded-3xl border border-gray-200/80 bg-white p-5 transition hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-white/10 dark:bg-[#171716] dark:hover:border-indigo-500/40">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('admin.follow-ups.show', $followUp) }}" class="block truncate font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                {{ $followUp->title }}
            </a>

            <p class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $followUp->description ? \Illuminate\Support\Str::limit($followUp->description, 120) : 'Sem descrição' }}
            </p>
        </div>

        <x-follow-up::partials.actions-menu :follow-up="$followUp" />
    </div>

    <div class="mt-3 flex flex-wrap items-center gap-1.5">
        <x-ui.badge :tone="$followUp->status->tone()">{{ $followUp->status->label() }}</x-ui.badge>
        <x-ui.badge :tone="$followUp->priority->tone()">{{ $followUp->priority->label() }}</x-ui.badge>

        @if ($followUp->isOverdue())
            <x-ui.badge tone="red">Atrasado</x-ui.badge>
        @endif
    </div>

    @if ($followUp->client || $followUp->project)
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            @if ($followUp->client)
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $followUp->client }}</span>
            @endif
            @if ($followUp->client && $followUp->project)
                <span aria-hidden="true">·</span>
            @endif
            @if ($followUp->project)
                {{ $followUp->project }}
            @endif
        </p>
    @endif

    <dl class="mt-auto grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 text-xs dark:border-white/10">
        <div class="min-w-0">
            <dt class="text-gray-400 dark:text-gray-500">Responsável</dt>
            <dd class="mt-0.5 truncate font-medium text-gray-700 dark:text-gray-200">{{ $followUp->assignee?->full_name ?? 'Não atribuído' }}</dd>
        </div>

        <div>
            <dt class="text-gray-400 dark:text-gray-500">Prazo</dt>
            <dd class="mt-0.5 font-medium">
                @if ($followUp->due_date)
                    <span @class([
                        'text-red-600 dark:text-red-400' => $followUp->isOverdue(),
                        'text-amber-600 dark:text-amber-400' => ! $followUp->isOverdue() && $followUp->isDueSoon(),
                        'text-gray-700 dark:text-gray-200' => ! $followUp->isOverdue() && ! $followUp->isDueSoon(),
                    ])>
                        {{ $followUp->due_date->format('d/m/Y') }}
                    </span>
                @else
                    <span class="text-gray-400 dark:text-gray-500">Sem prazo</span>
                @endif
            </dd>
        </div>
    </dl>
</article>
