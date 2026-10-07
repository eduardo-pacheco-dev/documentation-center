<x-layouts.public title="{{ $shortLink->title }}">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <span class="inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Envios de documentos</span>

        <h1 class="mt-3 text-lg font-semibold text-gray-900">{{ $shortLink->title }}</h1>
        @if ($shortLink->description)
            <p class="mt-1 text-sm text-gray-500">{{ $shortLink->description }}</p>
        @endif

        <form method="POST" action="{{ route('public.short-links.documents.store', $shortLink->code) }}" enctype="multipart/form-data" class="mt-5 space-y-3">
            @csrf

            <input
                type="file"
                name="documents[]"
                multiple
                required
                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200"
            >
            @error('documents')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
                Enviar documentos
            </button>
        </form>

        @if ($documents->isNotEmpty())
            <div class="mt-6 border-t border-gray-200 pt-4">
                <h2 class="text-sm font-semibold text-gray-700">Documentos enviados</h2>
                <ul class="mt-2 divide-y divide-gray-100">
                    @foreach ($documents as $document)
                        <li class="flex items-center justify-between gap-3 py-2 text-sm">
                            <span class="truncate">{{ $document->original_name }}</span>
                            <span class="flex shrink-0 items-center gap-3 text-xs text-gray-400">
                                {{ Number::fileSize($document->size) }}
                                <a href="{{ route('public.short-links.documents.download', $document) }}" class="text-indigo-600 hover:underline">
                                    Baixar
                                </a>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layouts.public>