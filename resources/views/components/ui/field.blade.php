@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'autofocus' => false,
    'enterkeyhint' => null,
    'revealable' => false,
    'required' => true,
    'delay' => null,
])

@php
    $id = $name;
    $hasError = $errors->has($name);
    $errorId = $id.'-error';

    $inputClasses = [
        'block w-full rounded-s border bg-grey-100 py-3 pl-3 text-base text-grey-15 shadow-[0_1px_2px_rgba(31,31,31,0.04)] outline-none transition placeholder:text-grey-65 focus:ring-4 sm:text-[15px]',
        'pr-12' => $revealable,
        'pr-2' => ! $revealable,
        'border-red-500 focus:border-red-500 focus:ring-red-500/15' => $hasError,
        'border-grey-80 focus:border-secondary-blue-400 focus:ring-secondary-blue-400/15' => ! $hasError,
    ];
@endphp

<div {{ $attributes->class(['auth-reveal' => $delay !== null])->merge($delay !== null ? ['style' => "--auth-delay: {$delay}ms"] : []) }}>
    @if ($label)
        <label for="{{ $id }}" class="mb-2 block text-sm font-semibold text-grey-15">{{ $label }}</label>
    @endif

    <div @class(['relative' => $revealable])>
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if (! is_null($value)) value="{{ $value }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($autofocus) autofocus @endif
            @if ($enterkeyhint) enterkeyhint="{{ $enterkeyhint }}" @endif
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
            @class($inputClasses)
        />

        @if ($revealable)
            <button
                type="button"
                data-password-toggle="{{ $id }}"
                aria-controls="{{ $id }}"
                aria-pressed="false"
                aria-label="Mostrar senha"
                class="absolute inset-y-0 right-1 flex w-11 items-center justify-center rounded-s text-grey-45 transition hover:text-grey-15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary-blue-400/40 active:scale-90"
            >
                <x-icon name="eye" data-password-icon="visible" class="h-5 w-5" />
                <x-icon name="eye-slash" data-password-icon="hidden" class="hidden h-5 w-5" />
            </button>
        @endif
    </div>

    @error($name)
        <p id="{{ $errorId }}" class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
