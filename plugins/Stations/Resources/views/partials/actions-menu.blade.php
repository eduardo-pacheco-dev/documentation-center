@props(['station'])

@php
    $menuId = 'station-actions-'.$station->getKey();
@endphp

<div
    x-data="{
        menuId: '{{ $menuId }}',
        open: false,
        trigger: null,
        top: 0,
        left: 0,
        toggle(button) {
            this.trigger = button;
            this.open = ! this.open;

            if (! this.open) {
                return;
            }

            const rect = button.getBoundingClientRect();
            const width = 176;
            const height = 136;
            const gap = 6;
            const below = rect.bottom + gap;

            this.top = below + height > window.innerHeight - 8
                ? Math.max(8, rect.top - height - gap)
                : below;

            this.left = Math.max(8, Math.min(rect.right - width, window.innerWidth - width - 8));

            this.$nextTick(() => {
                document.getElementById(this.menuId)?.querySelector('[role="menuitem"]')?.focus();
            });
        },
        close(refocus = false) {
            this.open = false;

            if (refocus) {
                this.trigger?.focus();
            }
        },
    }"
    x-on:scroll.window="close()"
    x-on:resize.window="close()"
    class="inline-block text-left"
>
    <button
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
        aria-haspopup="menu"
        aria-controls="{{ $menuId }}"
        x-bind:aria-expanded="open ? 'true' : 'false'"
        x-on:click="toggle($el)"
    >
        <span class="sr-only">Abrir menu de ações da estação {{ $station->station_id ?: $station->site_id }}</span>
        <x-icon name="ellipsis-horizontal" class="h-5 w-5" />
    </button>

    <template x-teleport="body">
        <div
            id="{{ $menuId }}"
            x-cloak
            x-show="open"
            x-transition.opacity.duration.100ms
            x-on:click.outside="close()"
            x-on:keydown.escape.window="close(true)"
            x-bind:style="`top: ${top}px; left: ${left}px;`"
            class="fixed z-[90] w-44 rounded-xl border border-gray-200/80 bg-white p-1 shadow-lg dark:border-white/10 dark:bg-[#1c1c1b]"
            role="menu"
            aria-orientation="vertical"
        >
            <a
                href="{{ route('admin.stations.show', $station) }}"
                role="menuitem"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
            >
                <x-icon name="eye" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                Ver detalhes
            </a>

            <a
                href="{{ route('admin.stations.edit', $station) }}"
                role="menuitem"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
            >
                <x-icon name="pencil-square" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                Editar
            </a>

            <form
                method="POST"
                action="{{ route('admin.stations.destroy', $station) }}"
                data-confirm-delete="Excluir a estação {{ $station->station_id ?: $station->site_id }}? Essa ação não pode ser desfeita."
                x-on:submit="close()"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    role="menuitem"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
                >
                    <x-icon name="trash" class="h-4 w-4 text-red-500 dark:text-red-400" />
                    Excluir
                </button>
            </form>
        </div>
    </template>
</div>
