@props([
    'id',
    'name',
    'type' => 'radio',
    'options' => [],
    'value' => null,
    'required' => false,
])

@php
    $isCheckbox = $type === 'checkbox';

    $selected = $isCheckbox
        ? (is_array($value) ? $value : (filled($value) ? array_map('trim', explode(',', (string) $value)) : []))
        : [(string) $value];

    $inputName = $isCheckbox ? $name.'[]' : $name;

    $optionClass = 'flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-[15px] text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-gray-700 dark:hover:bg-gray-800/60 dark:has-[:checked]:border-indigo-400 dark:has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:text-indigo-200 dark:has-[:checked]:ring-indigo-400/30';

    $choiceClass = 'h-5 w-5 shrink-0 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-900';
@endphp

<div {{ $attributes->class(['space-y-2']) }} role="group" aria-labelledby="{{ $id }}-label">
    @forelse ($options as $option)
        <label class="{{ $optionClass }}">
            <input
                type="{{ $type }}"
                name="{{ $inputName }}"
                value="{{ $option }}"
                @checked(in_array($option, $selected, true))
                @if (! $isCheckbox && $required) required @endif
                class="{{ $choiceClass }}{{ $isCheckbox ? ' rounded' : '' }}"
            >
            <span class="min-w-0 flex-1">{{ $option }}</span>
        </label>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma opção disponível.</p>
    @endforelse
</div>
