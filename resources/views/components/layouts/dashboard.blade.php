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

            <x-dashboard.sidebar />

            <div class="flex py-4 min-h-screen min-w-0 flex-col ">

                <main class="mx-auto w-full max-w-[1440px] flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8 bg-white rounded-xl">
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
