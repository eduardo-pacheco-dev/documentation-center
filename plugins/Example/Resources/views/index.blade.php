<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('example::general.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 p-8 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    <main class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">Plugin</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ __('example::general.name') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('example::general.description') }}</p>

        <dl class="mt-6 grid gap-3 text-sm">
            <div class="flex justify-between gap-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                <dt class="text-gray-500 dark:text-gray-400">Versão</dt>
                <dd class="font-mono">{{ plugin_version('example') }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                <dt class="text-gray-500 dark:text-gray-400">Alias</dt>
                <dd class="font-mono">{{ plugin('example')?->getAlias() }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                <dt class="text-gray-500 dark:text-gray-400">Saudação (config)</dt>
                <dd>{{ config('example.greeting') }}</dd>
            </div>
        </dl>
    </main>
</body>
</html>
