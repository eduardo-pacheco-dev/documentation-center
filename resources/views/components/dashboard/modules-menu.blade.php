@props(['items' => []])

<div class="relative flex w-full justify-center">
    <x-dashboard.sidebar-item
        icon="squares-2x2"
        label="Módulos"
        @click="modulesOpen = !modulesOpen"
        x-bind:aria-expanded="modulesOpen"
    />

    <x-ui.dropdown
        show="modulesOpen"
        close="modulesOpen = false"
        origin="bottom.left"
        x-bind:class="sidebarExpanded ? 'left-0 bottom-full mb-2' : 'left-full bottom-0 ml-3'"
        class="w-64 p-3"
    >
        <p class="px-1 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Módulos</p>

        <div class="grid grid-cols-2 gap-1">
            @foreach ($items as $module)
                <a
                    href="{{ route($module->routeName()) }}"
                    data-module="{{ \Illuminate\Support\Str::slug($module->label()) }}"
                    @class([
                        'flex items-center gap-2 rounded-xl px-2.5 py-2 text-xs font-medium transition',
                        'bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white' => request()->routeIs($module->activePattern()),
                        'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' => ! request()->routeIs($module->activePattern()),
                    ])
                >
                    <x-icon :name="$module->icon()" class="h-4 w-4 shrink-0" />
                    <span class="truncate">{{ $module->label() }}</span>
                </a>
            @endforeach
        </div>
    </x-ui.dropdown>
</div>
