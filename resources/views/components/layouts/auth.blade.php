<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-grey-100 font-sans text-grey-15 antialiased">
        <div class="flex min-h-screen flex-col">
            <main class="relative flex flex-1 items-center justify-center px-4 py-8 sm:px-8 sm:py-10">
                {{ $slot }}
            </main>

            <footer class="relative z-10 px-4 pb-6 pt-2 text-center text-xs text-grey-45 sm:text-sm">
                <p>&copy; {{ date('Y') }} AFL Engenharia. Todos os direitos reservados.</p>
            </footer>
        </div>

        <x-toasts />
    </body>
</html>
