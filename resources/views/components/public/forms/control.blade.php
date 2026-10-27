@props([
    'id',
    'name',
    'type' => 'text',
    'value' => null,
    'options' => [],
    'required' => false,
    'invalid' => false,
    'describedBy' => null,
    'placeholder' => 'Selecione...',
    'rows' => 4,
])

@php
    $isTextarea = $type === 'textarea';
    $isSelect = in_array($type, ['dropdown', 'select'], true);
    $isNumber = $type === 'number';
    $inputType = in_array($type, ['number', 'date', 'time'], true) ? $type : 'text';

    $classes = 'block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-base text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2 sm:text-sm dark:bg-gray-900 dark:text-gray-100 '
        .($invalid
            ? 'border-red-300 focus:border-red-500 focus:ring-red-500/30 dark:border-red-800'
            : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500/30 dark:border-gray-700');

    $describedBy = filled($describedBy) ? $describedBy : null;
@endphp

@if ($isTextarea)
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @required($required)
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($invalid) aria-invalid="true" @endif
        {{ $attributes->class([$classes, 'resize-y']) }}
    >{{ $value }}</textarea>
@elseif ($isSelect)
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @required($required)
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($invalid) aria-invalid="true" @endif
        {{ $attributes->class($classes) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $option)
            <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $option }}</option>
        @endforeach
    </select>
@else
    <input
        id="{{ $id }}"
        type="{{ $inputType }}"
        @if ($isNumber) inputmode="decimal" step="any" @endif
        name="{{ $name }}"
        value="{{ $value }}"
        @required($required)
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($invalid) aria-invalid="true" @endif
        {{ $attributes->class($classes) }}
    >
@endif
