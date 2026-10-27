@props([
    'id',
    'name',
    'value' => null,
    'required' => false,
    'min' => 1,
    'max' => 5,
    'minLabel' => null,
    'maxLabel' => null,
])

<div {{ $attributes->class(['flex flex-wrap gap-2']) }} role="group" aria-labelledby="{{ $id }}-label">
    @for ($option = (int) $min; $option <= (int) $max; $option++)
        <label class="min-w-11 flex-1 cursor-pointer">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $option }}"
                @checked((string) $value === (string) $option)
                @required($required)
                class="peer sr-only"
            >
            <span class="flex min-h-12 items-center justify-center rounded-xl border border-gray-200 bg-white px-2 text-base font-semibold tabular-nums text-gray-700 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:peer-checked:border-indigo-500 dark:peer-checked:bg-indigo-500 dark:peer-focus-visible:ring-offset-gray-900">
                {{ $option }}
            </span>
        </label>
    @endfor
</div>

@if (filled($minLabel) || filled($maxLabel))
    <div class="mt-2 flex items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
        <span>{{ $minLabel }}</span>
        <span class="text-right">{{ $maxLabel }}</span>
    </div>
@endif
