@props([
    'id',
    'fieldId',
    'name',
    'cameraName',
    'label',
    'images' => [],
    'thumbnails' => [],
    'imageDecisions' => [],
    'isNa' => false,
])

<div {{ $attributes->class(['space-y-3']) }}>
    <div class="flex flex-wrap gap-2" data-image-thumbnails>
        <div
            data-image-empty
            @if (! empty($images) || $isNa) hidden @endif
            class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-gray-300 text-[11px] font-medium text-gray-400 dark:border-gray-700 dark:text-gray-500"
        >
            <x-icon name="photo" class="h-5 w-5" />
            Vazio
        </div>

        <div
            data-image-na
            @if (! $isNa) hidden @endif
            class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-gray-300 bg-gray-100 text-[11px] font-semibold text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
            role="img"
            aria-label="Não aplicável"
            title="Não aplicável"
        >
            <x-icon name="minus-circle" class="h-5 w-5" />
            N/A
        </div>

        @foreach ($images as $image)
            <x-public.forms.image-thumbnail
                :href="$image"
                :src="$thumbnails[$loop->index] ?? $image"
                :alt="$label"
                :status="$imageDecisions[$loop->index] ?? null"
            />
        @endforeach
    </div>

    <div class="grid grid-cols-3 gap-2" data-image-field>
        <input
            type="hidden"
            name="na_answers[{{ $fieldId }}]"
            value="{{ $isNa ? '1' : '0' }}"
            data-image-na-input
        >

        <x-public.forms.upload-button icon="photo" label="Galeria">
            <input
                id="{{ $id }}"
                data-image-input
                type="file"
                name="{{ $name }}"
                accept="image/*"
                multiple
                class="sr-only"
            >
        </x-public.forms.upload-button>

        <x-public.forms.upload-button icon="camera" label="Tirar foto">
            <input
                data-camera-input
                type="file"
                name="{{ $cameraName }}"
                accept="image/*"
                capture="environment"
                class="sr-only"
            >
        </x-public.forms.upload-button>

        <x-public.forms.upload-button
            tag="button"
            type="button"
            icon="minus-circle"
            label="N/A"
            data-image-na-toggle
            aria-pressed="{{ $isNa ? 'true' : 'false' }}"
            aria-label="Não aplicável"
            title="Não aplicável"
            class="aria-pressed:border-gray-400 aria-pressed:bg-gray-100 aria-pressed:text-gray-900 aria-pressed:hover:border-gray-400 aria-pressed:hover:text-gray-900 dark:aria-pressed:border-gray-500 dark:aria-pressed:bg-gray-800 dark:aria-pressed:text-white dark:aria-pressed:hover:border-gray-500 dark:aria-pressed:hover:text-white"
        />
    </div>

    <x-ui.field-help>
        Escolha imagens da galeria ou tire uma foto com a câmera. Até 5 imagens e 15 MB por imagem.
    </x-ui.field-help>

    <p data-image-limit-message hidden class="text-xs font-medium text-amber-600 dark:text-amber-400">
        Máximo de 5 imagens por campo. As imagens excedentes não foram adicionadas.
    </p>
</div>
