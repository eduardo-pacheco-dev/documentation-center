@props(['name', 'items', 'label', 'placeholder', 'addLabel', 'hint' => null])

@php
    $items = collect(is_array($items) ? $items : [])
        ->map(fn (mixed $item) => (string) $item)
        ->values()
        ->all();

    if ($items === []) {
        $items = [''];
    }

    $inputClass = 'block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $removeClass = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-500 dark:hover:bg-red-500/10 dark:hover:text-red-400';
@endphp

<fieldset class="space-y-2" data-list-root data-list-name="{{ $name }}">
    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</legend>

    <div class="space-y-2" data-list-rows>
        @foreach ($items as $index => $item)
            <div class="flex items-center gap-2" data-list-row>
                <span
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                    data-list-position
                >
                    {{ $index + 1 }}
                </span>

                <input
                    type="text"
                    name="{{ $name }}[{{ $index }}]"
                    value="{{ $item }}"
                    placeholder="{{ $placeholder }}"
                    class="{{ $inputClass }}"
                    data-list-input
                >

                <button
                    type="button"
                    class="{{ $removeClass }}"
                    data-list-remove
                    @disabled(count($items) <= 1)
                >
                    <span class="sr-only">Remover item</span>
                    <x-icon name="trash" class="h-4 w-4" />
                </button>
            </div>
        @endforeach
    </div>

    <button
        type="button"
        data-list-add
        class="inline-flex items-center gap-1.5 rounded-xl border border-dashed border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 transition hover:border-indigo-400 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:text-indigo-400"
    >
        <x-icon name="plus" class="h-4 w-4" />
        {{ $addLabel }}
    </button>

    @if ($hint)
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
</fieldset>
