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
    $existingFiles = $field['files'] ?? [];
    $fileName = fn ($file, $index) => $field['fileNames'][$index] ?? basename(parse_url((string) $file, PHP_URL_PATH) ?: (string) $file);
    $inputClass = 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $id = 'field-'.$fieldId;
@endphp

<div
    data-question="{{ $fieldId }}"
    data-has-upload="{{ ! empty($existingImages) || ! empty($existingFiles) ? '1' : '0' }}"
>
    <div class="flex flex-wrap items-center gap-2">
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>

        <x-form-field-status :status="$field['status'] ?? null" />
    </div>

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
                <div class="mb-2 flex flex-wrap gap-2" data-image-thumbnails>
                    @forelse ($existingImages as $image)
                        @php($thumbnail = $field['imageThumbnails'][$loop->index] ?? $image)
                        <a
                            href="{{ $image }}"
                            target="_blank"
                            rel="noopener"
                            data-image-existing
                            class="block h-16 w-16 overflow-hidden rounded-lg ring-1 ring-gray-200 dark:ring-gray-800"
                        >
                            <img src="{{ $thumbnail }}" alt="{{ $label }}" loading="lazy" class="h-full w-full object-cover">
                        </a>
                    @empty
                        <div data-image-empty class="flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-gray-300 text-xs font-medium text-gray-400 dark:border-gray-700 dark:text-gray-500">
                            Vazio
                        </div>
                    @endforelse
                </div>

                <div class="flex flex-wrap items-center gap-2" data-image-field>
                    <label class="inline-flex h-16 w-16 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-gray-300 bg-white px-1 text-center text-[11px] font-medium leading-tight text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        <x-icon name="photo" class="h-4 w-4" />
                        Galeria

                        <input
                            data-image-input
                            type="file"
                            name="answers[{{ $fieldId }}][]"
                            accept="image/*"
                            multiple
                            class="sr-only"
                        >
                    </label>

                    <label class="inline-flex h-16 w-16 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-gray-300 bg-white px-1 text-center text-[11px] font-medium leading-tight text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        <x-icon name="camera" class="h-4 w-4" />
                        Tirar foto

                        <input
                            data-camera-input
                            type="file"
                            name="camera_answers[{{ $fieldId }}][]"
                            accept="image/*"
                            capture="environment"
                            class="sr-only"
                        >
                    </label>
                </div>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Escolha imagens da galeria ou tire uma foto com a câmera. Até 5 imagens e 15 MB por imagem.
                </p>

                <p data-image-limit-message hidden class="mt-1 text-xs font-medium text-amber-600 dark:text-amber-400">
                    Máximo de 5 imagens por campo. As imagens excedentes não foram adicionadas.
                </p>
                @break

            @case('file')
                @if (! empty($existingFiles))
                    <ul class="mb-3 divide-y divide-gray-100 rounded-lg border border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                        @foreach ($existingFiles as $index => $file)
                            <li>
                                <a
                                    href="{{ $file }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-indigo-600 transition hover:bg-gray-50 dark:text-indigo-400 dark:hover:bg-gray-800/60"
                                >
                                    <x-icon name="paper-clip" class="h-4 w-4 shrink-0" />
                                    <span class="truncate">{{ $fileName($file, $index) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <input
                    id="{{ $id }}"
                    type="file"
                    name="answers[{{ $fieldId }}][]"
                    multiple
                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:text-gray-300 dark:file:bg-gray-800 dark:file:text-gray-200 dark:hover:file:bg-gray-700"
                >

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Até 5 arquivos e 30 MB por arquivo.
                </p>
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

    @if (in_array($type, ['image', 'file'], true))
        @error($errorKey.'.*')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    @endif
</div>
