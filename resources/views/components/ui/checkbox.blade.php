@props(['name', 'value' => '1', 'label' => null, 'delay' => null])

@php
    $labelAttributes = $attributes->class(['auth-reveal' => $delay !== null]);

    if ($delay !== null) {
        $labelAttributes = $labelAttributes->merge(['style' => "--auth-delay: {$delay}ms"]);
    }
@endphp

<label {{ $labelAttributes->merge(['class' => 'flex w-fit cursor-pointer items-center gap-2.5 text-sm text-grey-45']) }}>
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        class="h-4 w-4 rounded border-grey-80 accent-secondary-blue-400 focus:ring-secondary-blue-400/30"
    />
    {{ $label ?? $slot }}
</label>
