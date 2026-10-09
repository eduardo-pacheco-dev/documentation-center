<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name') }}</title>

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
    <body class="flex min-h-screen flex-col bg-gray-50 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
        <header class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
                <div class="flex items-center gap-2">
                    <div x-data="{ navOpen: false, expanded: false }" class="relative">
                        <button
                            type="button"
                            @click="navOpen = !navOpen"
                            :aria-expanded="navOpen"
                            aria-label="Abrir menu de navegação"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        >
                            <x-icon name="bars-3" class="h-5 w-5" />
                        </button>

                        @php
                            $navItems = [
                                ['url' => route('admin.dashboard'), 'active' => 'admin.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                                ['url' => route('admin.links.index'), 'active' => 'admin.links.*', 'icon' => 'link', 'label' => 'Links'],
                                ['url' => route('admin.files.index'), 'active' => 'admin.files.*', 'icon' => 'document-text', 'label' => 'Arquivos'],
                                ['url' => route('admin.projects.index'), 'active' => 'admin.projects.*', 'icon' => 'briefcase', 'label' => 'Projetos'],
                                ['url' => route('admin.clients.index'), 'active' => 'admin.clients.*', 'icon' => 'building-office', 'label' => 'Clientes'],
                                ['url' => route('admin.erbs.index'), 'active' => 'admin.erbs.*', 'icon' => 'signal', 'label' => 'ERBs'],
                                ['url' => route('admin.catalog.index'), 'active' => 'admin.catalog.*', 'icon' => 'cube', 'label' => 'Catálogo'],
                                ['url' => route('admin.work-orders.index'), 'active' => 'admin.work-orders.*', 'icon' => 'clipboard-document-list', 'label' => 'Ordens de serviço'],
                            ];

                            if (auth()->user()->is_admin) {
                                $navItems[] = ['url' => route('admin.users.index'), 'active' => 'admin.users.*', 'icon' => 'users', 'label' => 'Usuários'];
                            }

                            $visibleLimit = 9;
                        @endphp

                        <div
                            x-show="navOpen"
                            @click.away="navOpen = false"
                            x-transition
                            style="display: none;"
                            class="absolute left-0 z-50 mt-2 w-64 rounded-md border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                        >
                            <div class="grid grid-cols-3 gap-1">
                                @foreach ($navItems as $index => $item)
                                    <a
                                        href="{{ $item['url'] }}"
                                        @if ($index >= $visibleLimit) x-show="expanded" style="display: none;" @endif
                                        class="flex flex-col items-center gap-1 rounded-md px-2 py-3 text-xs font-medium {{ request()->routeIs($item['active']) ? 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' }}"
                                    >
                                        <x-icon :name="$item['icon']" class="h-5 w-5" />
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>

                            @if (count($navItems) > $visibleLimit)
                                <button
                                    type="button"
                                    @click="expanded = !expanded"
                                    :aria-expanded="expanded"
                                    class="mt-1 flex w-full items-center justify-center gap-1 rounded-md px-2 py-2 text-xs font-medium text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                >
                                    <span x-text="expanded ? 'Ver menos' : 'Ver mais'"></span>
                                    <span class="transition-transform" :class="expanded ? 'rotate-180' : ''">
                                        <x-icon name="chevron-down" class="h-4 w-4" />
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold">
                        {{ config('app.name') }}
                    </a>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button
                        @click="open = !open"
                        class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
                        type="button"
                    >
                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff&size=32"
                            alt="{{ auth()->user()->name }}"
                            class="h-8 w-8 rounded-full"
                        >
                        <span class="hidden text-gray-700 sm:inline dark:text-gray-200">{{ auth()->user()->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-gray-500 dark:text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 z-50 mt-2 w-48 rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                        style="display: none;"
                    >
                        <a
                            href="{{ route('profile') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 {{ request()->routeIs('profile') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                        >
                            Perfil
                        </a>
                        <a
                            href="{{ route('settings') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 {{ request()->routeIs('settings') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                        >
                            Configurações
                        </a>
                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800"
                            >
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="border-t border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
        </footer>
    </body>
</html>
