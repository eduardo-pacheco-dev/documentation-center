<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
                <div class="flex items-center gap-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold">
                        {{ config('app.name') }}
                    </a>

                    <nav class="flex items-center gap-4 text-sm">
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="{{ request()->routeIs('admin.dashboard') ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900' }}"
                        >
                            Dashboard
                        </a>

                        <a
                            href="{{ route('admin.links.index') }}"
                            class="{{ request()->routeIs('admin.links.*') ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900' }}"
                        >
                            Links
                        </a>

                        <a
                            href="{{ route('admin.files.index') }}"
                            class="{{ request()->routeIs('admin.files.*') ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900' }}"
                        >
                            Arquivos
                        </a>

                        @if (auth()->user()->is_admin)
                            <a
                                href="{{ route('admin.users.index') }}"
                                class="{{ request()->routeIs('admin.users.*') ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900' }}"
                            >
                                Usuários
                            </a>
                        @endif
                    </nav>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button
                        @click="open = !open"
                        class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-gray-50"
                        type="button"
                    >
                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff&size=32"
                            alt="{{ auth()->user()->name }}"
                            class="h-8 w-8 rounded-full"
                        >
                        <span class="hidden text-gray-700 sm:inline">{{ auth()->user()->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 z-50 mt-2 w-48 rounded-md border border-gray-200 bg-white py-1 shadow-lg"
                        style="display: none;"
                    >
                        <a
                            href="{{ route('profile') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                            Perfil
                        </a>
                        <a
                            href="{{ route('settings') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                            Configurações
                        </a>
                        <div class="my-1 border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
