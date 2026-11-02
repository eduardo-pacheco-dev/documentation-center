@props([
    'periods' => [],
    'days' => null,
    'scope' => 'user',
    'scopeQuery' => null,
    'isAdmin' => false,
])

@php
    $buttonClass = 'inline-flex h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-medium transition';
@endphp

<x-analytics::card class="mt-6 flex flex-wrap items-center justify-between gap-3 p-3">
    <div class="flex flex-wrap items-center gap-2">
        <span class="px-1 text-xs font-medium text-gray-400 dark:text-gray-500">Período</span>

        <div class="inline-flex rounded-xl border border-gray-200 p-1 dark:border-white/10" role="group" aria-label="Período">
            @foreach ($periods as $option)
                <a
                    href="{{ route('analytics.index', array_filter(['period' => $option, 'scope' => $scopeQuery])) }}"
                    class="{{ $buttonClass }} {{ ! request('from') && $days === (int) $option ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                    title="Últimos {{ $option }} dias"
                >
                    {{ $option }}d
                </a>
            @endforeach
        </div>

        @if ($isAdmin)
            <div class="inline-flex rounded-xl border border-gray-200 p-1 dark:border-white/10" role="group" aria-label="Escopo">
                <a
                    href="{{ route('analytics.index', ['period' => $days]) }}"
                    class="{{ $buttonClass }} {{ $scope === 'user' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                >
                    Meus dados
                </a>
                <a
                    href="{{ route('analytics.index', ['period' => $days, 'scope' => 'global']) }}"
                    class="{{ $buttonClass }} {{ $scope === 'global' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}"
                >
                    Global
                </a>
            </div>
        @endif
    </div>

    <p class="px-1 text-xs text-gray-400 dark:text-gray-500">
        Atualizado em {{ now()->format('d/m/Y H:i') }}
    </p>
</x-analytics::card>
