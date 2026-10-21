@props(['title' => 'Dashboard'])

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
    <body class="bg-[#f4f4f2] text-gray-900 antialiased dark:bg-[#171716] dark:text-gray-100">
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

            <x-dashboard.sidebar />

            <div class="mr-4 flex min-h-screen min-w-0 flex-1 flex-col py-4">
                <div class="mb-2 flex items-center gap-2 px-1 lg:hidden">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        aria-label="Abrir menu"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-200/70 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                    >
                        <x-icon name="bars-3" class="h-5 w-5" />
                    </button>
                    <span class="truncate text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $title }}</span>
                </div>

                <main class="w-full flex-1 rounded-xl bg-white px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
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
