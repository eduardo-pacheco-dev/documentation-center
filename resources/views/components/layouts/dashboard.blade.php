@props(['title' => 'Dashboard'])

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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }} · {{ config('app.name') }}</title>

        <script>
            (function () {
                const theme = @json(auth()->user()?->theme ?? 'system');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    </head>
    <body class="bg-[#fafaf9] text-gray-900 antialiased dark:bg-[#101010] dark:text-gray-100">
        <div
            x-data="{
                sidebarOpen: false,
                sidebarExpanded: localStorage.getItem('dashboard-sidebar-expanded') === 'true',
                modulesOpen: false,
                notificationsOpen: false,
                userOpen: false,
            }"
            x-init="$watch('sidebarExpanded', (value) => localStorage.setItem('dashboard-sidebar-expanded', value))"
            class="min-h-screen lg:flex"
        >
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-gray-900/40 backdrop-blur-sm lg:hidden"
                style="display: none;"
            ></div>

            <aside
                x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                :style="sidebarExpanded ? 'width: 15rem' : 'width: 4.5rem'"
                class="fixed inset-y-0 left-0 z-40 flex w-[72px] -translate-x-full flex-col gap-1 border-r border-gray-200/80 bg-[#f4f4f2] px-2 py-4 transition-[width,transform] duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 dark:border-white/10 dark:bg-[#171716]"
                aria-label="Navegação principal"
            >
                <div class="mb-2 flex w-full items-center gap-2" :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gray-900 text-white shadow-sm transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                        aria-label="{{ config('app.name') }}"
                    >
                        <x-icon name="rectangle-stack" class="h-5 w-5" />
                    </a>

                    <span x-cloak x-show="sidebarExpanded" class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900 dark:text-white">
                        {{ config('app.name') }}
                    </span>
                </div>

                <button
                    type="button"
                    @click="sidebarExpanded = !sidebarExpanded; modulesOpen = false; userOpen = false"
                    class="group absolute -right-3.5 bottom-6 z-50 inline-flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-md transition hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:text-white dark:focus:ring-white/20"
                    :aria-label="sidebarExpanded ? 'Recolher menu' : 'Expandir menu'"
                    :title="sidebarExpanded ? 'Recolher menu' : 'Expandir menu'"
                >
                    <span x-cloak x-show="!sidebarExpanded">
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </span>
                    <span x-cloak x-show="sidebarExpanded">
                        <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    </span>
                </button>

                <a
                    href="{{ route('admin.work-orders.create') }}"
                    class="group relative mb-3 inline-flex h-10 items-center rounded-xl border border-gray-300 bg-white text-gray-700 shadow-sm transition hover:border-gray-900 hover:text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-white dark:hover:text-white"
                    :class="sidebarExpanded ? 'w-full justify-start gap-2.5 px-3' : 'w-10 justify-center self-center'"
                >
                    <x-icon name="plus" class="h-5 w-5 shrink-0" />
                    <span x-cloak x-show="sidebarExpanded" class="truncate text-xs font-semibold">Nova OS</span>
                    <span x-cloak x-show="!sidebarExpanded" class="pointer-events-none absolute left-full z-50 ml-3 whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-white dark:text-gray-900">
                        Nova OS
                    </span>
                </a>

                <nav class="flex w-full flex-1 flex-col gap-1 overflow-y-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($primaryNav as $item)
                        <a
                            href="{{ $item['url'] }}"
                            data-module="{{ \Illuminate\Support\Str::slug($item['label']) }}"
                            @class([
                                'group relative inline-flex h-10 shrink-0 items-center rounded-xl transition',
                                'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900' => request()->routeIs($item['active']),
                                'text-gray-500 hover:bg-white hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white' => ! request()->routeIs($item['active']),
                            ])
                            :class="sidebarExpanded ? 'w-full justify-start gap-3 px-3' : 'w-10 justify-center self-center'"
                            @if (request()->routeIs($item['active'])) aria-current="page" @endif
                        >
                            <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                            <span x-cloak x-show="sidebarExpanded" class="truncate text-sm font-medium">{{ $item['label'] }}</span>
                            <span x-cloak x-show="!sidebarExpanded" class="pointer-events-none absolute left-full z-50 ml-3 whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-white dark:text-gray-900">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    @endforeach
                </nav>

                <div class="flex w-full flex-col gap-1 border-t border-gray-200/80 pt-2 dark:border-white/10">
                    <div class="relative">
                        <button
                            type="button"
                            @click="modulesOpen = !modulesOpen"
                            :aria-expanded="modulesOpen"
                            class="group relative inline-flex h-10 items-center rounded-xl text-gray-500 transition hover:bg-white hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                            :class="sidebarExpanded ? 'w-full justify-start gap-3 px-3' : 'w-10 justify-center self-center'"
                            aria-label="Todos os módulos"
                        >
                            <x-icon name="squares-2x2" class="h-5 w-5 shrink-0" />
                            <span x-cloak x-show="sidebarExpanded" class="truncate text-sm font-medium">Módulos</span>
                            <span x-cloak x-show="!sidebarExpanded" class="pointer-events-none absolute left-full z-50 ml-3 whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-white dark:text-gray-900">
                                Módulos
                            </span>
                        </button>

                        <div
                            x-show="modulesOpen"
                            @click.away="modulesOpen = false"
                            x-transition.origin.bottom.left
                            :style="sidebarExpanded
                                ? 'left: 0; bottom: 100%; margin-bottom: 0.5rem;'
                                : 'left: 100%; bottom: 0; margin-left: 0.75rem;'"
                            style="display: none;"
                            class="absolute z-50 w-64 rounded-2xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                        >
                            <p class="px-1 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Módulos</p>
                            <div class="grid grid-cols-2 gap-1">
                                @foreach ($moduleNav as $item)
                                    <a
                                        href="{{ $item['url'] }}"
                                        data-module="{{ \Illuminate\Support\Str::slug($item['label']) }}"
                                        @class([
                                            'flex items-center gap-2 rounded-xl px-2.5 py-2 text-xs font-medium transition',
                                            'bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white' => request()->routeIs($item['active']),
                                            'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' => ! request()->routeIs($item['active']),
                                        ])
                                    >
                                        <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <a
                        href="{{ route('settings') }}"
                        class="group relative inline-flex h-10 items-center rounded-xl text-gray-500 transition hover:bg-white hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                        :class="sidebarExpanded ? 'w-full justify-start gap-3 px-3' : 'w-10 justify-center self-center'"
                    >
                        <x-icon name="adjustments-horizontal" class="h-5 w-5 shrink-0" />
                        <span x-cloak x-show="sidebarExpanded" class="truncate text-sm font-medium">Configurações</span>
                        <span x-cloak x-show="!sidebarExpanded" class="pointer-events-none absolute left-full z-50 ml-3 whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-white dark:text-gray-900">
                            Configurações
                        </span>
                    </a>

                    <div class="relative mt-1">
                        <button
                            type="button"
                            @click="userOpen = !userOpen"
                            :aria-expanded="userOpen"
                            class="flex items-center gap-2.5 rounded-xl p-1.5 transition hover:bg-white focus:outline-none dark:hover:bg-white/10"
                            :class="sidebarExpanded ? 'w-full justify-start' : 'justify-center'"
                            aria-label="Menu do usuário"
                        >
                            <span class="relative shrink-0">
                                <img
                                    src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=1f2937&color=fff&size=64"
                                    alt="{{ auth()->user()->name }}"
                                    class="h-9 w-9 rounded-full"
                                >
                                <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-[#f4f4f2] bg-emerald-500 dark:border-[#171716]"></span>
                            </span>
                            <span x-cloak x-show="sidebarExpanded" class="min-w-0 flex-1 text-left">
                                <span class="block truncate text-xs font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                                <span class="block truncate text-[10px] text-gray-400 dark:text-gray-500">{{ auth()->user()->email }}</span>
                            </span>
                            <span x-cloak x-show="sidebarExpanded" class="shrink-0 text-gray-400 dark:text-gray-500">
                                <x-icon name="chevron-up-down" class="h-4 w-4" />
                            </span>
                        </button>

                        <div
                            x-show="userOpen"
                            @click.away="userOpen = false"
                            x-transition.origin.bottom.left
                            :style="sidebarExpanded
                                ? 'left: 0; bottom: 100%; margin-bottom: 0.5rem;'
                                : 'left: 100%; bottom: 0; margin-left: 0.75rem;'"
                            style="display: none;"
                            class="absolute z-50 w-56 rounded-2xl border border-gray-200 bg-white p-1.5 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                        >
                            <div class="px-3 py-2.5">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                            <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Perfil</a>
                            <a href="{{ route('settings') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Configurações</a>
                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Sair</button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen min-w-0 flex-col lg:pl-0">
                <header class="sticky top-0 z-20 border-b border-gray-200/80 bg-[#fafaf9]/85 backdrop-blur-md dark:border-white/10 dark:bg-[#101010]/85">
                    <div class="mx-auto flex h-16 w-full max-w-[1440px] items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            @click="sidebarOpen = true"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-200/70 hover:text-gray-900 lg:hidden dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                            aria-label="Abrir menu"
                        >
                            <x-icon name="bars-3" class="h-5 w-5" />
                        </button>

                        <div class="hidden min-w-0 items-center gap-2 text-sm sm:flex">
                            <span class="text-gray-400 dark:text-gray-500">Administração</span>
                            <x-icon name="chevron-right" class="h-3.5 w-3.5 text-gray-300 dark:text-gray-600" />
                            <span class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
                        </div>

                        <div class="ml-auto flex flex-1 items-center justify-end gap-2 sm:flex-none">
                            <label class="relative hidden w-full max-w-xs items-center md:flex lg:w-80">
                                <span class="pointer-events-none absolute left-3 text-gray-400 dark:text-gray-500">
                                    <x-icon name="magnifying-glass" class="h-4 w-4" />
                                </span>
                                <input
                                    x-ref="search"
                                    type="search"
                                    placeholder="Pesquisar no sistema…"
                                    @keydown.window.slash.prevent="$refs.search.focus()"
                                    @keydown.window.meta.k.prevent="$refs.search.focus()"
                                    class="h-9 w-full rounded-xl border border-gray-200 bg-white pl-9 pr-12 text-sm text-gray-700 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-gray-400 focus:ring-2 focus:ring-gray-900/5 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:border-gray-500 dark:focus:ring-white/10"
                                    aria-label="Pesquisar"
                                >
                                <kbd class="pointer-events-none absolute right-2.5 hidden rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-[10px] font-medium text-gray-400 lg:inline dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500">/</kbd>
                            </label>

                            <button
                                type="button"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-200/70 hover:text-gray-900 md:hidden dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                                aria-label="Pesquisar"
                            >
                                <x-icon name="magnifying-glass" class="h-5 w-5" />
                            </button>

                            <div class="relative">
                                <button
                                    type="button"
                                    @click="notificationsOpen = !notificationsOpen"
                                    :aria-expanded="notificationsOpen"
                                    class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-200/70 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                                    aria-label="Notificações"
                                >
                                    <x-icon name="bell" class="h-5 w-5" />
                                    <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-rose-500 ring-2 ring-[#fafaf9] dark:ring-[#101010]"></span>
                                </button>

                                <div
                                    x-show="notificationsOpen"
                                    @click.away="notificationsOpen = false"
                                    x-transition
                                    style="display: none;"
                                    class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                >
                                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Notificações</p>
                                        <button type="button" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Marcar como lidas</button>
                                    </div>
                                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <a href="{{ route('admin.work-orders.index') }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Ordens de serviço pendentes</span>
                                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Existem OS aguardando atendimento.</span>
                                            </span>
                                        </a>
                                        <a href="{{ route('admin.files.index') }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-sky-500"></span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Novos arquivos enviados</span>
                                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Documentos recentes estão disponíveis.</span>
                                            </span>
                                        </a>
                                        <a href="{{ route('admin.agenda.index') }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Compromissos de hoje</span>
                                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Confira a agenda antes de encerrar o dia.</span>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <a
                                href="{{ route('admin.work-orders.create') }}"
                                class="hidden h-9 items-center gap-1.5 rounded-xl bg-gray-900 px-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 sm:inline-flex dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                            >
                                <x-icon name="plus" class="h-4 w-4" />
                                Nova OS
                            </a>
                        </div>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-[1440px] flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                    @if (session('status'))
                        <noscript>
                            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-300">
                                {{ session('status') }}
                            </div>
                        </noscript>
                    @endif

                    {{ $slot }}
                </main>

                <footer class="border-t border-gray-200/80 px-4 py-5 text-center text-xs text-gray-400 sm:px-6 lg:px-8 dark:border-white/10 dark:text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
                </footer>
            </div>
        </div>

        <x-toasts />
        <x-confirm-modal />
        <x-document-preview-modal />
    </body>
</html>
