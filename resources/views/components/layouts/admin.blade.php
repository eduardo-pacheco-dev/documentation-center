<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
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

                <div class="flex items-center gap-4 text-sm">
                    <span class="hidden text-gray-500 sm:inline">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-100"
                        >
                            Sair
                        </button>
                    </form>
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
