@props([
    'icon',
    'label',
    'href' => null,
    'active' => false,
    'module' => null,
])

@php
    $tag = $href ? 'a' : 'button';

    $classes = [
        'group relative inline-flex h-10 shrink-0 items-center rounded-xl transition',
        'bg-gray-900 text-white dark:bg-white dark:text-gray-900' => $active,
        'text-gray-500 hover:bg-white hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white' => ! $active,
    ];
@endphp

<{{ $tag }}
    @if ($href)
        href="{{ $href }}"
    @else
        type="button"
    @endif
    @if ($module)
        data-module="{{ \Illuminate\Support\Str::slug($module) }}"
    @endif
    aria-label="{{ $label }}"
    @if ($active)
        aria-current="page"
    @endif
    :class="sidebarExpanded ? 'w-full justify-start gap-3 px-3' : 'w-10 justify-center self-center'"
    {{ $attributes->class($classes) }}
>
    <x-icon :name="$icon" class="h-5 w-5 shrink-0" />
    <span x-cloak x-show="sidebarExpanded" class="truncate text-sm font-medium">{{ $label }}</span>
    <x-ui.tooltip x-cloak x-show="!sidebarExpanded">{{ $label }}</x-ui.tooltip>
</{{ $tag }}>
