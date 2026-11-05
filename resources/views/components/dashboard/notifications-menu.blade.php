@php
    $levelClasses = [
        'info' => 'bg-sky-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
    ];
@endphp

<div class="relative flex w-full justify-center">
    <button
        type="button"
        @click="notificationsOpen = !notificationsOpen"
        :aria-expanded="notificationsOpen"
        aria-label="Notificações"
        class="group relative inline-flex h-10 shrink-0 items-center rounded-xl text-gray-500 transition hover:bg-white hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
        :class="sidebarExpanded ? 'w-full justify-start gap-3 px-3' : 'w-10 justify-center self-center'"
    >
        <span class="relative">
            <x-icon name="bell" class="h-5 w-5 shrink-0" />

            @if ($unreadCount > 0)
                <span class="absolute -right-2 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </span>

        <span x-cloak x-show="sidebarExpanded" class="flex-1 truncate text-left text-sm font-medium">Notificações</span>
    </button>

    <x-ui.dropdown
        show="notificationsOpen"
        close="notificationsOpen = false"
        origin="bottom.left"
        x-bind:class="sidebarExpanded ? 'left-0 bottom-full mb-2' : 'left-full bottom-0 ml-3'"
        class="w-80 p-1.5"
    >
        <div class="flex items-center justify-between gap-2 px-3 py-2">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Notificações</p>

            @if ($unreadCount > 0)
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $unreadCount }} não lida{{ $unreadCount === 1 ? '' : 's' }}</span>
            @endif
        </div>

        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $url = $data['url'] ?? route('notifications.index');
                $level = $data['level'] ?? 'info';
            @endphp

            <div class="group flex items-start gap-2 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-gray-200 dark:bg-white/10' : ($levelClasses[$level] ?? $levelClasses['info']) }}"></span>

                <a href="{{ $url }}" class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-gray-900 dark:text-white">{{ $data['title'] ?? 'Notificação' }}</span>
                    <span class="mt-0.5 block line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $data['body'] ?? '' }}</span>
                    <span class="mt-1 block text-[10px] text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                </a>

                @if (! $notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="mt-0.5 rounded-md p-1 text-gray-400 transition hover:bg-white hover:text-indigo-600 dark:hover:bg-white/10 dark:hover:text-indigo-400" aria-label="Marcar como lida">
                            <x-icon name="check" class="h-3.5 w-3.5" />
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <p class="px-3 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Nenhuma notificação por aqui.</p>
        @endforelse

        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

        <div class="flex items-center justify-between gap-1 px-2 py-1.5">
            <a href="{{ route('notifications.index') }}" class="rounded-lg px-2 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                Ver todas
            </a>

            <div class="flex items-center gap-1">
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg px-2 py-1.5 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10">
                            Marcar lidas
                        </button>
                    </form>
                @endif

                @if ($notifications->isNotEmpty())
                    <form method="POST" action="{{ route('notifications.clear') }}" data-confirm="Remover todas as notificações?" data-confirm-title="Limpar notificações" data-confirm-label="Remover todas">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg px-2 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                            Limpar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-ui.dropdown>
</div>
