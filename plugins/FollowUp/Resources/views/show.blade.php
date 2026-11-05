<x-layouts.admin title="{{ $followUp->title }}">
    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.follow-ups.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Follow-ups</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">{{ $followUp->title }}</span>
    </nav>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge :tone="$followUp->status->tone()">{{ $followUp->status->label() }}</x-ui.badge>
                <x-ui.badge :tone="$followUp->priority->tone()">Prioridade {{ $followUp->priority->label() }}</x-ui.badge>

                @if ($followUp->isOverdue())
                    <x-ui.badge tone="red">Atrasado</x-ui.badge>
                @endif

                @if ($followUp->code)
                    <x-ui.badge tone="gray">Código {{ $followUp->code }}</x-ui.badge>
                @endif
            </div>

            <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $followUp->title }}</h1>

            @if ($followUp->description)
                <p class="mt-2 max-w-3xl whitespace-pre-line text-sm text-gray-500 dark:text-gray-400">{{ $followUp->description }}</p>
            @endif

            <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                criado por {{ $followUp->creator?->full_name ?? 'Usuário removido' }}
                · <x-relative-time :value="$followUp->created_at" />
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.action :href="route('admin.follow-ups.export-one', $followUp)" icon="arrow-down-tray" variant="secondary">
                Exportar
            </x-ui.action>

            <x-ui.action variant="secondary" icon="paper-airplane" data-follow-up-email-open>
                Enviar por e-mail
            </x-ui.action>

            <x-ui.action :href="route('admin.follow-ups.edit', $followUp)" icon="pencil-square" variant="secondary">
                Editar
            </x-ui.action>

            <form method="POST" action="{{ route('admin.follow-ups.destroy', $followUp) }}" data-confirm-delete="Excluir o follow-up {{ $followUp->title }}? Essa ação não pode ser desfeita.">
                @csrf
                @method('DELETE')

                <button type="submit" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/40">
                    <x-icon name="trash" class="h-4 w-4" />
                    Excluir
                </button>
            </form>
        </div>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Cliente</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $followUp->client ?: 'Não informado' }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Projeto</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $followUp->project ?: 'Não informado' }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Responsável</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $followUp->assignee?->full_name ?? 'Não atribuído' }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Prazo</p>
            <p @class([
                'mt-1 text-sm font-medium',
                'text-red-600 dark:text-red-400' => $followUp->isOverdue(),
                'text-amber-600 dark:text-amber-400' => ! $followUp->isOverdue() && $followUp->isDueSoon(),
                'text-gray-800 dark:text-gray-100' => ! $followUp->isOverdue() && ! $followUp->isDueSoon(),
            ])>
                {{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Atualizado em</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100"><x-relative-time :value="$followUp->updated_at" /></p>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#171716]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Concluído em</p>
            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">
                {{ $followUp->completed_at?->format('d/m/Y') ?? '—' }}
            </p>
        </div>
    </div>

    <section class="mt-4 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="chat-bubble-oval-left-ellipsis" class="h-5 w-5" />
            </span>

            <div>
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Histórico e atualizações</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registre o andamento e acompanhe as mudanças deste follow-up.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.follow-ups.updates.store', $followUp) }}" class="mt-5">
            @csrf

            <div class="flex gap-3">
                <x-ui.avatar :name="auth()->user()->full_name" class="mt-0.5" />

                <div class="min-w-0 flex-1">
                    <label for="update-body" class="sr-only">Atualização</label>
                    <textarea
                        id="update-body"
                        name="body"
                        rows="3"
                        maxlength="5000"
                        required
                        placeholder="Escreva uma atualização sobre o andamento..."
                        class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                    >{{ old('body') }}</textarea>

                    @error('body')
                        <x-ui.field-error class="mt-2">{{ $message }}</x-ui.field-error>
                    @enderror

                    <div class="mt-2 flex justify-end">
                        <x-ui.action type="submit" icon="paper-airplane" size="sm">
                            Registrar atualização
                        </x-ui.action>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-6 border-t border-gray-100 pt-2 dark:border-white/10">
            @forelse ($followUp->updates as $update)
                <article class="flex gap-3 border-b border-gray-100 py-4 last:border-b-0 dark:border-white/10">
                    <x-ui.avatar :name="$update->user?->full_name ?? 'Usuário removido'" />

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $update->user?->full_name ?? 'Usuário removido' }}</p>

                            @if ($update->user_id === auth()->id())
                                <x-ui.badge tone="indigo">Você</x-ui.badge>
                            @endif

                            <span class="text-xs text-gray-400 dark:text-gray-500" aria-hidden="true">·</span>
                            <x-relative-time :value="$update->created_at" class="text-xs text-gray-400 dark:text-gray-500" />
                        </div>

                        @if ($update->status_from && $update->status_to)
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                @if ($update->status_from)
                                    <x-ui.badge :tone="$update->status_from->tone()">{{ $update->status_from->label() }}</x-ui.badge>
                                @endif
                                <x-icon name="arrow-right" class="h-3.5 w-3.5 text-gray-400" />
                                @if ($update->status_to)
                                    <x-ui.badge :tone="$update->status_to->tone()">{{ $update->status_to->label() }}</x-ui.badge>
                                @endif
                            </div>
                        @endif

                        <p class="mt-1.5 whitespace-pre-line break-words text-sm text-gray-700 dark:text-gray-200">{{ $update->body }}</p>

                        @if (auth()->user()?->is_admin || $update->user_id === auth()->id())
                            <form method="POST" action="{{ route('admin.follow-ups.updates.destroy', [$followUp, $update]) }}" data-confirm-delete="Remover esta atualização? Essa ação não pode ser desfeita." class="mt-2">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 transition hover:text-red-600 dark:text-red-400 dark:hover:text-red-300">
                                    <x-icon name="trash" class="h-3.5 w-3.5" />
                                    Remover
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <x-ui.empty-state
                    icon="chat-bubble-oval-left-ellipsis"
                    title="Nenhuma atualização ainda"
                    description="Use o campo acima para registrar o primeiro andamento."
                    tone="indigo"
                />
            @endforelse
        </div>
    </section>

    <div class="mt-4">
        <a href="{{ route('admin.follow-ups.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Voltar para os follow-ups
        </a>
    </div>

    <x-follow-up::partials.email-modal :follow-up="$followUp" />
</x-layouts.admin>
