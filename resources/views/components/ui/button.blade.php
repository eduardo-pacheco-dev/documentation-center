@props(['type' => 'submit', 'delay' => null])

@php
    $buttonAttributes = $attributes->class(['auth-reveal' => $delay !== null]);

    if ($delay !== null) {
        $buttonAttributes = $buttonAttributes->merge(['style' => "--auth-delay: {$delay}ms"]);
    }
@endphp

<button
    type="{{ $type }}"
    {{ $buttonAttributes->merge([
        'class' => 'inline-flex w-full cursor-pointer items-center justify-center rounded-sm border border-transparent bg-secondary-blue-400 px-4 py-3 text-[15px] font-semibold text-white shadow-[0_8px_20px_rgba(31,115,232,0.24)] transition duration-200 hover:-translate-y-0.5 hover:bg-secondary-blue-500 hover:shadow-[0_12px_26px_rgba(31,115,232,0.3)] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary-blue-400/25 active:translate-y-0 active:scale-[0.99] disabled:cursor-not-allowed disabled:border-grey-80 disabled:bg-grey-90 disabled:text-grey-65 disabled:shadow-none disabled:hover:translate-y-0',
    ]) }}
>
    {{ $slot }}
</button>
