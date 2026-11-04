<x-layouts.admin title="{{ $team->name }}">
    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.teams.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Equipes</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">{{ $team->name }}</span>
    </nav>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge :tone="$team->status->tone()">{{ $team->status->label() }}</x-ui.badge>
                @if ($team->area)<x-ui.badge tone="indigo">{{ $team->area }}</x-ui.badge>@endif
            </div>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $team->name }}</h1>
            @if ($team->description)
                <p class="mt-2 max-w-3xl text-sm text-gray-500 dark:text-gray-400">{{ $team->description }}</p>
            @endif
            <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                início {{ $team->started_at?->format('d/m/Y') ?? 'não informado' }}
                · atualizado <x-relative-time :value="$team->updated_at" />
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.action :href="route('admin.teams.edit', $team)" icon="pencil-square" variant="secondary">Editar</x-ui.action>
            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" data-confirm-delete="Excluir a equipe {{ $team->name }}? Membros e acompanhamentos serão removidos.">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/40">
                    <x-icon name="trash" class="h-4 w-4" /> Excluir
                </button>
            </form>
        </div>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Membros</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $team->members->count() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Líder</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $team->leader?->full_name ?? 'Sem líder' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Último acompanhamento</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $team->latestCheckIn?->reference_date?->format('d/m/Y') ?? 'Sem registros' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Saúde</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $team->latestCheckIn?->health_score ? $team->latestCheckIn->health_score.'/5' : '—' }}</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)]">
        <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><x-icon name="users" class="h-5 w-5" /></span>
                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Membros</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pessoas vinculadas a esta equipe.</p>
                </div>
            </div>

            <ul class="mt-4 divide-y divide-gray-100 dark:divide-white/10">
                @forelse ($team->members as $member)
                    <li class="flex items-center gap-3 py-3">
                        <x-ui.avatar :name="$member->full_name" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $member->full_name }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $member->email }} · {{ $member->pivot->role === 'lider' ? 'Líder' : 'Membro' }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.teams.members.destroy', [$team, $member]) }}" data-confirm-delete="Remover {{ $member->full_name }} da equipe?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15" aria-label="Remover {{ $member->full_name }}"><x-icon name="trash" class="h-4 w-4" /></button>
                        </form>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum membro vinculado.</li>
                @endforelse
            </ul>

            @if ($availableUsers->isNotEmpty())
                <form method="POST" action="{{ route('admin.teams.members.store', $team) }}" class="mt-4 rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    @csrf
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Adicionar membro</p>
                    <div class="mt-3 space-y-3">
                        <select name="user_id" required class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Selecione um usuário</option>
                            @foreach ($availableUsers as $user)
                                <option value="{{ $user->getKey() }}" @selected((string) old('user_id') === (string) $user->getKey())>{{ $user->full_name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        <div class="grid gap-2 sm:grid-cols-2">
                            <select name="role" required class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="membro" @selected(old('role', 'membro') === 'membro')>Membro</option>
                                <option value="lider" @selected(old('role') === 'lider')>Líder</option>
                            </select>
                            <input type="date" name="joined_at" value="{{ old('joined_at', now()->format('Y-m-d')) }}" class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        @error('role')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        <x-ui.action type="submit" icon="user-plus" size="sm" class="w-full justify-center">Adicionar</x-ui.action>
                    </div>
                </form>
            @endif
        </section>

        <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><x-icon name="clipboard-document-list" class="h-5 w-5" /></span>
                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Acompanhamentos</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registros de saúde e evolução da equipe.</p>
                </div>
            </div>

            <div class="mt-4 divide-y divide-gray-100 dark:divide-white/10">
                @forelse ($team->checkIns as $checkIn)
                    <article class="py-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $checkIn->title }}</h3>
                                    <x-ui.badge :tone="$checkIn->status->tone()">{{ $checkIn->status->label() }}</x-ui.badge>
                                    <x-ui.badge tone="gray">{{ $checkIn->health_score }}/5</x-ui.badge>
                                </div>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ $checkIn->reference_date->format('d/m/Y') }} · {{ $checkIn->user?->full_name ?? 'Usuário removido' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.teams.check-ins.edit', [$team, $checkIn]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Editar acompanhamento"><x-icon name="pencil-square" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route('admin.teams.check-ins.destroy', [$team, $checkIn]) }}" data-confirm-delete="Remover este acompanhamento?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15" aria-label="Remover acompanhamento"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        </div>

                        <p class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-gray-200">{{ $checkIn->summary }}</p>

                        @if ($checkIn->next_steps)
                            <p class="mt-2 rounded-xl bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:bg-white/5 dark:text-gray-300"><strong class="font-semibold">Próximos passos:</strong> {{ $checkIn->next_steps }}</p>
                        @endif
                    </article>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum acompanhamento registrado.</p>
                @endforelse
            </div>

            <details class="mt-4 rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                <summary class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-gray-100">Novo acompanhamento</summary>
                <div class="mt-4">
                    <x-team-tracking::partials.check-in-form :action="route('admin.teams.check-ins.store', $team)" :statuses="$checkInStatuses" submit-label="Registrar acompanhamento" />
                </div>
            </details>
        </section>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.teams.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <x-icon name="chevron-left" class="h-4 w-4" /> Voltar para as equipes
        </a>
    </div>
</x-layouts.admin>
