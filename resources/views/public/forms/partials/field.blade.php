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
    $hasNestedError = collect($errors->keys())->contains(fn (string $key) => str_starts_with($key, $errorKey.'.'));
    $hasError = $errors->has($errorKey) || $hasNestedError;
    $inputClass = 'block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-base text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2 sm:text-sm dark:bg-gray-900 dark:text-gray-100 '
        .($hasError
            ? 'border-red-300 focus:border-red-500 focus:ring-red-500/30 dark:border-red-800'
            : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500/30 dark:border-gray-700');
    $id = 'field-'.$fieldId;
    $helpId = $id.'-help';
    $errorId = $id.'-error';
    $isGroup = in_array($type, ['radio', 'checkbox', 'scale'], true);
    $describedBy = trim(implode(' ', array_filter([
        ! empty($field['help']) ? $helpId : null,
        $errors->has($errorKey) ? $errorId : null,
    ])));
    $optionClass = 'flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-[15px] text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/70 has-[:checked]:text-indigo-700 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-gray-700 dark:hover:bg-gray-800/60 dark:has-[:checked]:border-indigo-400 dark:has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:text-indigo-200 dark:has-[:checked]:ring-indigo-400/30';
    $choiceClass = 'h-5 w-5 shrink-0 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-900';
@endphp

<div
    data-question="{{ $fieldId }}"
    data-has-upload="{{ ! empty($existingImages) || ! empty($existingFiles) ? '1' : '0' }}"
    class="space-y-2.5"
>
    <div class="flex flex-wrap items-center gap-2">
        @if ($isGroup)
            <p id="{{ $id }}-label" class="block text-[15px] font-semibold leading-snug text-gray-900 dark:text-gray-100">
                {{ $label }}
                @if ($required)
                    <span class="text-red-500" aria-hidden="true">*</span>
                @endif
            </p>
        @else
            <label for="{{ $id }}" class="block text-[15px] font-semibold leading-snug text-gray-900 dark:text-gray-100">
                {{ $label }}
                @if ($required)
                    <span class="text-red-500" aria-hidden="true">*</span>
                @endif
            </label>
        @endif

        <x-form-field-status :status="$field['status'] ?? null" />
    </div>

    @if (! empty($field['help']))
        <p id="{{ $helpId }}" class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $field['help'] }}</p>
    @endif

    <div>
        @switch($type)
            @case('textarea')
                <textarea
                    id="{{ $id }}"
                    name="answers[{{ $fieldId }}]"
                    rows="4"
                    @required($required)
                    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
                    @if ($hasError) aria-invalid="true" @endif
                    class="{{ $inputClass }} resize-y"
                >{{ $oldValue }}</textarea>
                @break

            @case('number')
                <input
                    id="{{ $id }}"
                    type="number"
                    inputmode="decimal"
                    step="any"
                    name="answers[{{ $fieldId }}]"
                    value="{{ $oldValue }}"
                    @required($required)
                    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
                    @if ($hasError) aria-invalid="true" @endif
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
                    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
                    @if ($hasError) aria-invalid="true" @endif
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
                    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
                    @if ($hasError) aria-invalid="true" @endif
                    class="{{ $inputClass }}"
                >
                @break

            @case('radio')
                <div class="space-y-2" role="group" aria-labelledby="{{ $id }}-label">
                    @forelse ($options as $option)
                        <label class="{{ $optionClass }}">
                            <input
                                type="radio"
                                name="answers[{{ $fieldId }}]"
                                value="{{ $option }}"
                                @checked((string) $oldValue === $option)
                                @required($required)
                                class="{{ $choiceClass }}"
                            >
                            <span class="min-w-0 flex-1">{{ $option }}</span>
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

                <div class="space-y-2" role="group" aria-labelledby="{{ $id }}-label">
                    @forelse ($options as $option)
                        <label class="{{ $optionClass }}">
                            <input
                                type="checkbox"
                                name="answers[{{ $fieldId }}][]"
                                value="{{ $option }}"
                                @checked(in_array($option, $selected, true))
                                class="{{ $choiceClass }} rounded"
                            >
                            <span class="min-w-0 flex-1">{{ $option }}</span>
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
                    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
                    @if ($hasError) aria-invalid="true" @endif
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

                <div class="flex flex-wrap gap-2" role="group" aria-labelledby="{{ $id }}-label">
                    @for ($value = $scaleMin; $value <= $scaleMax; $value++)
                        <label class="min-w-11 flex-1 cursor-pointer">
                            <input
                                type="radio"
                                name="answers[{{ $fieldId }}]"
                                value="{{ $value }}"
                                @checked((string) $oldValue === (string) $value)
                                @required($required)
                                class="peer sr-only"
                            >
                            <span class="flex min-h-12 items-center justify-center rounded-xl border border-gray-200 bg-white px-2 text-base font-semibold tabular-nums text-gray-700 shadow-sm transition peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:peer-checked:border-indigo-500 dark:peer-checked:bg-indigo-500 dark:peer-focus-visible:ring-offset-gray-900">
                                {{ $value }}
                            </span>
                        </label>
                    @endfor
                </div>

                @if (! empty($field['scaleMinLabel']) || ! empty($field['scaleMaxLabel']))
                    <div class="mt-2 flex items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $field['scaleMinLabel'] ?? '' }}</span>
                        <span class="text-right">{{ $field['scaleMaxLabel'] ?? '' }}</span>
                    </div>
                @endif
                @break

            @case('image')
                <div class="space-y-3">
                    <div class="flex flex-wrap gap-2" data-image-thumbnails>
                        @forelse ($existingImages as $image)
                            @php($thumbnail = $field['imageThumbnails'][$loop->index] ?? $image)
                            <a
                                href="{{ $image }}"
                                target="_blank"
                                rel="noopener"
                                data-image-existing
                                class="group relative block h-20 w-20 overflow-hidden rounded-xl ring-1 ring-gray-200 transition active:scale-[0.98] dark:ring-gray-800"
                            >
                                <img src="{{ $thumbnail }}" alt="{{ $label }}" loading="lazy" class="h-full w-full object-cover">
                                <span class="pointer-events-none absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-950/60 text-white">
                                    <x-icon name="eye" class="h-3.5 w-3.5" />
                                </span>
                            </a>
                        @empty
                            <div data-image-empty class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-gray-300 text-[11px] font-medium text-gray-400 dark:border-gray-700 dark:text-gray-500">
                                <x-icon name="photo" class="h-5 w-5" />
                                Vazio
                            </div>
                        @endforelse
                    </div>

                    <div class="grid grid-cols-2 gap-2" data-image-field>
                        <label class="flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 active:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/60 dark:hover:text-indigo-300 dark:active:bg-gray-800">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <x-icon name="photo" class="h-4 w-4" />
                            </span>
                            Galeria

                            <input
                                id="{{ $id }}"
                                data-image-input
                                type="file"
                                name="answers[{{ $fieldId }}][]"
                                accept="image/*"
                                multiple
                                class="sr-only"
                            >
                        </label>

                        <label class="flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 active:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/60 dark:hover:text-indigo-300 dark:active:bg-gray-800">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <x-icon name="camera" class="h-4 w-4" />
                            </span>
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

                    <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                        Escolha imagens da galeria ou tire uma foto com a câmera. Até 5 imagens e 15 MB por imagem.
                    </p>

                    <p data-image-limit-message hidden class="text-xs font-medium text-amber-600 dark:text-amber-400">
                        Máximo de 5 imagens por campo. As imagens excedentes não foram adicionadas.
                    </p>
                </div>
                @break

            @case('file')
                @if (! empty($existingFiles))
                    <ul class="mb-3 space-y-2">
                        @foreach ($existingFiles as $index => $file)
                            <li>
                                <a
                                    href="{{ $file }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex min-h-11 items-center gap-2.5 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-indigo-600 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-gray-800 dark:bg-gray-900 dark:text-indigo-400 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                        <x-icon name="paper-clip" class="h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 flex-1 truncate">{{ $fileName($file, $index) }}</span>
                                    <x-icon name="arrow-down-tray" class="h-4 w-4 shrink-0 text-gray-400" />
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
                    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
                    @if ($hasError) aria-invalid="true" @endif
                    class="block min-h-11 w-full cursor-pointer rounded-xl border border-gray-300 bg-white text-base text-gray-600 shadow-sm transition file:mr-3 file:rounded-lg file:border-0 file:bg-gray-900 file:px-3.5 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 sm:text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-white dark:file:text-gray-900 dark:hover:file:bg-gray-200"
                >

                <p class="mt-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
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
                    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
                    @if ($hasError) aria-invalid="true" @endif
                    class="{{ $inputClass }}"
                >
        @endswitch
    </div>

    @error($errorKey)
        <p id="{{ $errorId }}" role="alert" class="flex items-start gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
            <x-icon name="exclamation-triangle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            <span>{{ $message }}</span>
        </p>
    @enderror

    @if (in_array($type, ['image', 'file'], true))
        @error($errorKey.'.*')
            <p role="alert" class="flex items-start gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                <x-icon name="exclamation-triangle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                <span>{{ $message }}</span>
            </p>
        @enderror
    @endif
</div>
