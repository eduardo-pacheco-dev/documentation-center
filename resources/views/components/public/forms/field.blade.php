@props(['field'])

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
    $value = old($errorKey, $field['value'] ?? '');
    $existingImages = $field['images'] ?? [];
    $existingFiles = $field['files'] ?? [];
    $hasNestedError = collect($errors->keys())->contains(fn (string $key) => str_starts_with($key, $errorKey.'.'));
    $hasError = $errors->has($errorKey) || $hasNestedError;
    $id = 'field-'.$fieldId;
    $helpId = $id.'-help';
    $errorId = $id.'-error';
    $isGroup = in_array($type, ['radio', 'checkbox', 'scale'], true);
    $describedBy = trim(implode(' ', array_filter([
        ! empty($field['help']) ? $helpId : null,
        $errors->has($errorKey) ? $errorId : null,
    ])));
    $name = 'answers['.$fieldId.']';
@endphp

<div
    {{ $attributes->class(['space-y-2.5']) }}
    data-question="{{ $fieldId }}"
    data-status="{{ $field['status'] ?? '' }}"
    data-has-upload="{{ ! empty($existingImages) || ! empty($existingFiles) ? '1' : '0' }}"
>
    <x-ui.field-label
        :label="$label"
        :for="$id"
        :group="$isGroup"
        :required="$required"
        :status="$field['status'] ?? null"
    />

    @if (! empty($field['help']))
        <x-ui.field-help :id="$helpId">{{ $field['help'] }}</x-ui.field-help>
    @endif

    <div>
        @switch($type)
            @case('radio')
            @case('checkbox')
                <x-public.forms.choice-group
                    :id="$id"
                    :name="$name"
                    :type="$type"
                    :options="$options"
                    :value="$value"
                    :required="$required"
                />
                @break

            @case('scale')
                <x-public.forms.scale
                    :id="$id"
                    :name="$name"
                    :value="$value"
                    :required="$required"
                    :min="$field['scaleMin'] ?? 1"
                    :max="$field['scaleMax'] ?? 5"
                    :min-label="$field['scaleMinLabel'] ?? null"
                    :max-label="$field['scaleMaxLabel'] ?? null"
                />
                @break

            @case('image')
                <x-public.forms.image-upload
                    :id="$id"
                    :name="$name.'[]'"
                    :camera-name="'camera_answers['.$fieldId.'][]'"
                    :label="$label"
                    :images="$existingImages"
                    :thumbnails="$field['imageThumbnails'] ?? []"
                    :image-decisions="$field['imageDecisions'] ?? []"
                />
                @break

            @case('file')
                <x-public.forms.file-upload
                    :id="$id"
                    :name="$name.'[]'"
                    :files="$existingFiles"
                    :file-names="$field['fileNames'] ?? []"
                    :described-by="$describedBy"
                    :invalid="$hasError"
                />
                @break

            @default
                <x-public.forms.control
                    :id="$id"
                    :type="$type"
                    :name="$name"
                    :value="$value"
                    :options="$options"
                    :required="$required"
                    :invalid="$hasError"
                    :described-by="$describedBy"
                />
        @endswitch
    </div>

    @error($errorKey)
        <x-ui.field-error :id="$errorId">{{ $message }}</x-ui.field-error>
    @enderror

    @if (in_array($type, ['image', 'file'], true))
        @error($errorKey.'.*')
            <x-ui.field-error>{{ $message }}</x-ui.field-error>
        @enderror
    @endif
</div>
