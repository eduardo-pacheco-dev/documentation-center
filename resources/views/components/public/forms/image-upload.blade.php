@props([
    'id',
    'name',
    'cameraName',
    'label',
    'images' => [],
    'thumbnails' => [],
])

<div {{ $attributes->class(['space-y-3']) }}>
    <div class="flex flex-wrap gap-2" data-image-thumbnails>
        @forelse ($images as $image)
            <x-public.forms.image-thumbnail
                :href="$image"
                :src="$thumbnails[$loop->index] ?? $image"
                :alt="$label"
            />
        @empty
            <div data-image-empty class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-gray-300 text-[11px] font-medium text-gray-400 dark:border-gray-700 dark:text-gray-500">
                <x-icon name="photo" class="h-5 w-5" />
                Vazio
            </div>
        @endforelse
    </div>

    <div class="grid grid-cols-2 gap-2" data-image-field>
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
    </div>

    <x-ui.field-help>
        Escolha imagens da galeria ou tire uma foto com a câmera. Até 5 imagens e 15 MB por imagem.
    </x-ui.field-help>

    <p data-image-limit-message hidden class="text-xs font-medium text-amber-600 dark:text-amber-400">
        Máximo de 5 imagens por campo. As imagens excedentes não foram adicionadas.
    </p>
</div>
