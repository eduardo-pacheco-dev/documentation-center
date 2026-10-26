@php
    $fieldId = (string) ($field['id'] ?? '');
    $type = $field['type'] ?? 'text';
    $label = ($field['label'] ?? null) ?: ($fieldId ?: 'Pergunta');
    $required = (bool) ($field['required'] ?? false);
    $options = collect($field['options'] ?? [])
        ->filter(fn (mixed $option) => is_string($option) && $option !== '')
        ->values()
        ->all();
    $errorKey = 'answers.'.$fieldId;
    $oldValue = old($errorKey, $field['value'] ?? '');
    $existingImages = $field['images'] ?? [];
    $inputClass = 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $id = 'field-'.$fieldId;
@endphp

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
        {{ $label }}
        @if ($required)
            <span class="text-red-500" aria-hidden="true">*</span>
        @endif
    </label>

    @if (! empty($field['help']))
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $field['help'] }}</p>
    @endif

    <div class="mt-2">
        @switch($type)
            @case('textarea')
                <textarea
                    id="{{ $id }}"
                    name="answers[{{ $fieldId }}]"
                    rows="3"
                    @required($required)
                    class="{{ $inputClass }}"
                >{{ $oldValue }}</textarea>
                @break

            @case('number')
                <input
                    id="{{ $id }}"
                    type="number"
                    step="any"
                    name="answers[{{ $fieldId }}]"
                    value="{{ $oldValue }}"
                    @required($required)
                    class="{{ $inputClass }}"
                >
                @break

            @case('date')
                <input
                    id="{{ $id }}"
                    type="date"
                    name="answers[{{ $fieldId }}]"
                    value="{{ $oldValue }}"
                    @required($required)
                    class="{{ $inputClass }}"
                >
                @break

            @case('time')
                <input
                    id="{{ $id }}"
                    type="time"
                    name="answers[{{ $fieldId }}]"
                    value="{{ $oldValue }}"
                    @required($required)
                    class="{{ $inputClass }}"
                >
                @break

            @case('radio')
                <div class="space-y-2">
                    @forelse ($options as $option)
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input
                                type="radio"
                                name="answers[{{ $fieldId }}]"
                                value="{{ $option }}"
                                @checked((string) $oldValue === $option)
                                @required($required)
                                class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                            >
                            <span>{{ $option }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma opção disponível.</p>
                    @endforelse
                </div>
                @break

            @case('checkbox')
                @php
                    $selected = old($errorKey, filled($field['value'] ?? null)
                        ? array_map('trim', explode(',', (string) $field['value']))
                        : []);
                    $selected = is_array($selected) ? $selected : [$selected];
                @endphp

                <div class="space-y-2">
                    @forelse ($options as $option)
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input
                                type="checkbox"
                                name="answers[{{ $fieldId }}][]"
                                value="{{ $option }}"
                                @checked(in_array($option, $selected, true))
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                            >
                            <span>{{ $option }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma opção disponível.</p>
                    @endforelse
                </div>
                @break

            @case('dropdown')
                <select
                    id="{{ $id }}"
                    name="answers[{{ $fieldId }}]"
                    @required($required)
                    class="{{ $inputClass }}"
                >
                    <option value="">Selecione...</option>
                    @foreach ($options as $option)
                        <option value="{{ $option }}" @selected((string) $oldValue === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @break

            @case('scale')
                @php
                    $scaleMin = (int) ($field['scaleMin'] ?? 1);
                    $scaleMax = (int) ($field['scaleMax'] ?? 5);
                @endphp

                <div class="flex flex-wrap items-center gap-2">
                    @for ($value = $scaleMin; $value <= $scaleMax; $value++)
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="answers[{{ $fieldId }}]"
                                value="{{ $value }}"
                                @checked((string) $oldValue === (string) $value)
                                @required($required)
                                class="peer sr-only"
                            >
                            <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:peer-checked:border-indigo-500 dark:peer-checked:bg-indigo-500">
                                {{ $value }}
                            </span>
                        </label>
                    @endfor
                </div>

                @if (! empty($field['scaleMinLabel']) || ! empty($field['scaleMaxLabel']))
                    <div class="mt-1 flex items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $field['scaleMinLabel'] ?? '' }}</span>
                        <span>{{ $field['scaleMaxLabel'] ?? '' }}</span>
                    </div>
                @endif
                @break

            @case('image')
                @if (! empty($existingImages))
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($existingImages as $image)
                            <a
                                href="{{ $image }}"
                                target="_blank"
                                rel="noopener"
                                class="block overflow-hidden rounded-lg ring-1 ring-gray-200 dark:ring-gray-800"
                            >
                                <img src="{{ $image }}" alt="{{ $label }}" loading="lazy" class="h-16 w-24 object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif

                <input
                    id="{{ $id }}"
                    type="file"
                    name="answers[{{ $fieldId }}][]"
                    accept="image/*"
                    multiple
                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:text-gray-300 dark:file:bg-gray-800 dark:file:text-gray-200 dark:hover:file:bg-gray-700"
                >
                @break

            @default
                <input
                    id="{{ $id }}"
                    type="text"
                    name="answers[{{ $fieldId }}]"
                    value="{{ $oldValue }}"
                    @required($required)
                    class="{{ $inputClass }}"
                >
        @endswitch
    </div>

    @error($errorKey)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    @if ($type === 'image')
        @error($errorKey.'.*')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    @endif
</div>
