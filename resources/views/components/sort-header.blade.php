@props(['column', 'label', 'icon' => null])

@php
    $activeSort = request('sort', 'created_at');
    $activeDirection = in_array(request('direction'), ['asc', 'desc'], true)
        ? request('direction')
        : ($activeSort === 'created_at' ? 'desc' : 'asc');
    $isActive = $activeSort === $column;
    $nextDirection = $isActive && $activeDirection === 'asc' ? 'desc' : 'asc';

    $query = request()->query();
    unset($query['page']);
    $query['sort'] = $column;
    $query['direction'] = $nextDirection;
    $url = url()->current().'?'.http_build_query($query);
@endphp

<th {{ $attributes->merge(['class' => 'px-5 py-3 font-medium']) }}>
    <a
        href="{{ $url }}"
        class="inline-flex items-center gap-1.5 {{ $isActive ? 'text-gray-900' : 'hover:text-gray-900' }}"
    >
        @if ($icon)
            <x-icon :name="$icon" class="h-4 w-4" />
        @endif
        {{ $label }}
        <x-icon
            :name="$isActive ? ($activeDirection === 'asc' ? 'chevron-up' : 'chevron-down') : 'chevron-up-down'"
            class="h-3.5 w-3.5 {{ $isActive ? 'text-gray-900' : 'text-gray-400' }}"
        />
    </a>
</th>
