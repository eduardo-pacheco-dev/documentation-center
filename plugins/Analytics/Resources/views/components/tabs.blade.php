@props(['tabs' => []])

<div {{ $attributes->class('flex flex-wrap items-center gap-1 overflow-x-auto border-b border-gray-200 dark:border-white/10') }} role="tablist" aria-label="Seções do analytics">
    @foreach ($tabs as $key => $tab)
        <button
            type="button"
            role="tab"
            id="tab-{{ $key }}"
            data-tab="{{ $key }}"
            aria-controls="panel-{{ $key }}"
            aria-selected="{{ $key === 'overview' ? 'true' : 'false' }}"
            class="group relative inline-flex shrink-0 items-center gap-2 border-b-2 border-transparent px-3 py-2.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
        >
            {{ $tab['label'] }}

            @if (($tab['count'] ?? null) !== null)
                <span class="text-[10px] font-medium leading-none text-gray-400 dark:text-gray-500">{{ $tab['count'] }}</span>
            @endif
        </button>
    @endforeach
</div>
