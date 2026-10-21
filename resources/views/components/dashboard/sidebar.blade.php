@php
    $primaryNav = [
        ['url' => route('admin.dashboard'), 'active' => 'admin.dashboard', 'icon' => 'home', 'label' => 'Início'],
        ['url' => route('admin.projects.index'), 'active' => 'admin.projects.*', 'icon' => 'briefcase', 'label' => 'Projetos'],
        ['url' => route('admin.work-orders.index'), 'active' => 'admin.work-orders.*', 'icon' => 'clipboard-document-list', 'label' => 'Ordens de serviço'],
        ['url' => route('admin.clients.index'), 'active' => 'admin.clients.*', 'icon' => 'building-office', 'label' => 'Clientes'],
        ['url' => route('admin.files.index'), 'active' => 'admin.files.*', 'icon' => 'document-text', 'label' => 'Arquivos'],
        ['url' => route('admin.agenda.index'), 'active' => 'admin.agenda.*', 'icon' => 'calendar-days', 'label' => 'Agenda'],
        ['url' => route('admin.tasks.index'), 'active' => 'admin.tasks.*', 'icon' => 'check-circle', 'label' => 'Tarefas'],
        ['url' => route('admin.forms.index'), 'active' => 'admin.forms.*', 'icon' => 'document-check', 'label' => 'Formulários'],
    ];

    $moduleNav = [
        ['url' => route('admin.erbs.index'), 'active' => 'admin.erbs.*', 'icon' => 'signal', 'label' => 'ERBs'],
        ['url' => route('admin.radio-links.index'), 'active' => 'admin.radio-links.*', 'icon' => 'arrows-right-left', 'label' => 'Radio links'],
        ['url' => route('admin.catalog.index'), 'active' => 'admin.catalog.*', 'icon' => 'cube', 'label' => 'Catálogo'],
        ['url' => route('admin.equipments.index'), 'active' => 'admin.equipments.*', 'icon' => 'wrench-screwdriver', 'label' => 'Equipamentos'],
        ['url' => route('admin.links.index'), 'active' => 'admin.links.*', 'icon' => 'link', 'label' => 'Links'],
        ['url' => route('admin.colaboradores.index'), 'active' => 'admin.colaboradores.*', 'icon' => 'user', 'label' => 'Colaboradores'],
        ['url' => route('admin.form-templates.index'), 'active' => 'admin.form-templates.*', 'icon' => 'document-duplicate', 'label' => 'Templates'],
    ];

    if (auth()->user()->is_admin) {
        $moduleNav[] = ['url' => route('admin.backups.index'), 'active' => 'admin.backups.*', 'icon' => 'rectangle-stack', 'label' => 'Backups'];
        $moduleNav[] = ['url' => route('admin.users.index'), 'active' => 'admin.users.*', 'icon' => 'users', 'label' => 'Usuários'];
    }
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
        @foreach ($primaryNav as $item)
            <x-dashboard.sidebar-item
                :href="$item['url']"
                :icon="$item['icon']"
                :label="$item['label']"
                :module="$item['label']"
                :active="request()->routeIs($item['active'])"
            />
        @endforeach
    </nav>

    <div class="flex w-full flex-col gap-1 border-t border-gray-200/80 pt-2 dark:border-white/10">
        <x-dashboard.modules-menu :items="$moduleNav" />

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
