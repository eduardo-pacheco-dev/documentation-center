<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-grey-100 font-sans text-grey-15 antialiased">
        <main class="relative flex min-h-screen w-full items-center justify-center px-5 py-10 sm:px-8">
            {{ $slot }}
        </main>

        <x-toasts />
    </body>
</html>
