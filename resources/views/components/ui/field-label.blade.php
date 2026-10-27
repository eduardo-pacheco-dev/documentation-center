@props([
    'label',
    'for' => null,
    'group' => false,
    'required' => false,
    'status' => null,
])

@php
    $labelClass = 'block text-[15px] font-semibold leading-snug text-gray-900 dark:text-gray-100';
@endphp

<div {{ $attributes->class(['flex flex-wrap items-center gap-2']) }}>
    @if ($group)
        <p @if ($for) id="{{ $for }}-label" @endif class="{{ $labelClass }}">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </p>
    @else
        <label @if ($for) for="{{ $for }}" @endif class="{{ $labelClass }}">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <x-form-field-status :status="$status" />
</div>
