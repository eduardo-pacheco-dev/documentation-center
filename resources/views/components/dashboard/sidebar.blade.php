@php
    use App\Enums\AdminModule;
    use App\Plugins\PluginRepository;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $pluginModules = app(PluginRepository::class)->modules();

    $enabledModules = array_filter(
        AdminModule::enabledFor(auth()->user()),
        fn (AdminModule $module): bool => ! in_array($module->value, $pluginModules, true),
    );
    $primaryNav = array_filter($enabledModules, fn (AdminModule $module): bool => $module->isPrimary());
    $moduleNav = array_filter($enabledModules, fn (AdminModule $module): bool => ! $module->isPrimary());

    $pluginNav = collect(app(PluginRepository::class)->navigation())
        ->filter(fn (array $item): bool => Route::has($item['route']))
        ->filter(fn (array $item): bool => $item['module'] === null || auth()->user()->hasModule($item['module']));

    $pluginPrimaryNav = $pluginNav->where('primary', true);
    $pluginModuleNav = $pluginNav->where('primary', false);

    $moduleItems = collect($moduleNav)
        ->map(fn (AdminModule $module): array => [
            'label' => $module->label(),
            'icon' => $module->icon(),
            'url' => route($module->routeName()),
            'slug' => Str::slug($module->label()),
            'active' => request()->routeIs($module->activePattern()),
        ])
        ->merge($pluginModuleNav->map(fn (array $item): array => [
            'label' => $item['label'],
            'icon' => $item['icon'],
            'url' => route($item['route']),
            'slug' => Str::slug($item['label']),
            'active' => request()->routeIs($item['active']),
        ]))
        ->values();
@endphp

<aside
    x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    :style="sidebarExpanded ? 'width: 15rem' : 'width: 4.5rem'"
    class="fixed inset-y-0 left-0 z-40 flex w-[72px] -translate-x-full flex-col gap-1 bg-[#f4f4f2] px-2 pt-4 pb-4 transition-[width,transform] duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 dark:bg-[#171716]"
    aria-label="Navegação principal"
>
    <x-dashboard.brand />
    <x-dashboard.sidebar-toggle />
    <x-dashboard.quick-action />

    <nav class="flex w-full flex-1 flex-col gap-1 overflow-y-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($primaryNav as $module)
            <x-dashboard.sidebar-item
                :href="route($module->routeName())"
                :icon="$module->icon()"
                :label="$module->label()"
                :module="$module->label()"
                :active="request()->routeIs($module->activePattern())"
            />
        @endforeach

        @foreach ($pluginPrimaryNav as $item)
            <x-dashboard.sidebar-item
                :href="route($item['route'])"
                :icon="$item['icon']"
                :label="$item['label']"
                :module="$item['label']"
                :active="request()->routeIs($item['active'])"
            />
        @endforeach
    </nav>

    <div class="flex w-full flex-col gap-1 border-t border-gray-200/80 pt-2 dark:border-white/10">
        @if ($moduleItems->isNotEmpty())
            <x-dashboard.modules-menu :items="$moduleItems" />
        @endif

        <x-dashboard.sidebar-item
            :href="route('settings')"
            icon="adjustments-horizontal"
            label="Configurações"
            :active="request()->routeIs('settings')"
        />

        <div class="my-1 w-full border-t border-gray-200/80 dark:border-white/10"></div>

        <x-dashboard.user-menu />
    </div>
</aside>
