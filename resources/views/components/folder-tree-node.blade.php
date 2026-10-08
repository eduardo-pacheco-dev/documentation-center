@props(['node', 'activeTreePath', 'activeId', 'navQuery'])

@php
    $hasChildren = $node['children'] !== [];
    $isOpen = in_array($node['id'], $activeTreePath, true);
    $isActive = $activeId !== null && $node['id'] === $activeId;
@endphp

<li data-tree-node>
    <div class="flex items-center gap-0.5 pr-1">
        @if ($hasChildren)
            <button
                type="button"
                data-tree-toggle
                aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                aria-label="Alternar subpastas de {{ $node['name'] }}"
                class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            >
                <x-icon name="chevron-down" class="h-3.5 w-3.5 transition-transform {{ $isOpen ? '' : '-rotate-90' }}" />
            </button>
        @else
            <span class="inline-block h-6 w-6 shrink-0" aria-hidden="true"></span>
        @endif

        <a
            href="{{ route('admin.files.index', array_merge($navQuery, ['folder' => $node['id']])) }}"
            @if ($isActive) aria-current="page" @endif
            title="{{ $node['name'] }}"
            class="flex min-w-0 flex-1 items-center gap-2 rounded-md px-2 py-1.5 text-sm {{ $isActive ? 'bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800' }}"
        >
            <x-icon name="folder" class="h-4 w-4 shrink-0 {{ $isActive ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}" />
            <span class="truncate">{{ $node['name'] }}</span>
        </a>
    </div>

    @if ($hasChildren)
        <ul class="ml-2.5 space-y-0.5 border-l border-gray-200 pl-2 pt-0.5 dark:border-gray-800 {{ $isOpen ? '' : 'hidden' }}">
            @foreach ($node['children'] as $child)
                <x-folder-tree-node :node="$child" :active-tree-path="$activeTreePath" :active-id="$activeId" :nav-query="$navQuery" />
            @endforeach
        </ul>
    @endif
</li>
