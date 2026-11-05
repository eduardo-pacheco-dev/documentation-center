@php
    $levelStyles = [
        'info' => ['icon' => 'exclamation-circle', 'tile' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400', 'bar' => 'bg-sky-500'],
        'success' => ['icon' => 'check', 'tile' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', 'bar' => 'bg-emerald-500'],
        'warning' => ['icon' => 'exclamation-triangle', 'tile' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', 'bar' => 'bg-amber-500'],
        'danger' => ['icon' => 'exclamation-triangle', 'tile' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400', 'bar' => 'bg-red-500'],
    ];
@endphp

<x-layouts.admin title="Notificações">
    <x-ui.page-header
        category="Conta"
        title="Notificações"
        description="Acompanhe os avisos do sistema e dos plugins."
        icon="bell"
    >
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <x-ui.action type="submit" variant="secondary" icon="check">
                    Marcar todas como lidas
                </x-ui.action>
            </form>
        @endif

        @if ($notifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.clear') }}" data-confirm="Remover todas as notificações?" data-confirm-title="Limpar notificações" data-confirm-label="Remover todas">
                @csrf
                @method('DELETE')
                <x-ui.action type="submit" variant="ghost" icon="trash">
                    Limpar tudo
                </x-ui.action>
            </form>
        @endif
    </x-ui.page-header>

    <div class="mt-7 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $level = $data['level'] ?? 'info';
                $style = $levelStyles[$level] ?? $levelStyles['info'];
            @endphp

            <article class="relative flex items-start gap-4 border-b border-gray-100 p-5 last:border-b-0 sm:p-6 dark:border-white/10 {{ $notification->read_at ? '' : 'bg-indigo-50/30 dark:bg-indigo-500/[0.04]' }}">
                @if (! $notification->read_at)
                    <span class="absolute inset-y-0 left-0 w-1 {{ $style['bar'] }}"></span>
                @endif

                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $style['tile'] }}">
                    <x-icon :name="$style['icon']" class="h-5 w-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $data['title'] ?? 'Notificação' }}</h2>

                        @if (! $notification->read_at)
                            <x-ui.badge tone="indigo">Nova</x-ui.badge>
                        @endif

                        @if (filled($data['plugin'] ?? null))
                            <x-ui.badge tone="gray">{{ $data['plugin'] }}</x-ui.badge>
                        @endif
                    </div>

                    <p class="mt-1 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $data['body'] ?? '' }}</p>

                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                        <span>{{ $notification->created_at->diffForHumans() }}</span>

                        @if (filled($data['url'] ?? null))
                            <a href="{{ $data['url'] }}" class="font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">
                                Abrir
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-white/10 dark:hover:text-indigo-400" aria-label="Marcar como lida" title="Marcar como lida">
                                <x-icon name="check" class="h-4 w-4" />
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" aria-label="Remover notificação" title="Remover notificação">
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <x-ui.empty-state icon="bell" title="Nenhuma notificação" description="Quando o sistema ou um plugin enviar um aviso, ele aparecerá aqui." />
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-5">
            {{ $notifications->links() }}
        </div>
    @endif
</x-layouts.admin>
