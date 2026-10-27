@props(['title' => config('app.name'), 'withActionBar' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

        <title>{{ $title ?? config('app.name') }}</title>

        <script>
            document.documentElement.classList.remove('no-js');
            document.documentElement.classList.add('js');

            (function () {
                const theme = @json(auth()->user()?->theme ?? 'system');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-svh flex-col bg-[#f4f4f2] text-gray-900 antialiased dark:bg-[#171716] dark:text-gray-100">
        <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col px-4 {{ $withActionBar ? 'pb-28 pt-4 sm:pt-6' : 'justify-center py-12' }} sm:px-6">
            @if (session('status'))
                <noscript>
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ session('status') }}
                    </div>
                </noscript>
            @endif

            {{ $slot }}
        </main>

        <x-toasts />
    </body>
</html>
