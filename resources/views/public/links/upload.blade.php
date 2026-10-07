<x-layouts.public title="{{ $shortLink->title }}">
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
        <span class="inline-flex rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">Envios de documentos</span>

        <h1 class="mt-3 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $shortLink->title }}</h1>
        @if ($shortLink->description)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $shortLink->description }}</p>
        @endif

        <form method="POST" action="{{ route('public.short-links.documents.store', $shortLink->code) }}" enctype="multipart/form-data" class="mt-5 space-y-3">
            @csrf

            <input
                type="file"
                name="documents[]"
                multiple
                required
                class="block w-full text-sm text-gray-600 dark:text-gray-300 dark:border dark:border-gray-700 dark:bg-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 dark:file:bg-gray-800 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 dark:file:text-gray-200 hover:file:bg-gray-200 dark:hover:file:bg-gray-700"
            >
            @error('documents')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                Enviar documentos
            </button>
        </form>

        @if ($documents->isNotEmpty())
            <div class="mt-6 border-t border-gray-200 dark:border-gray-800 pt-4">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Documentos enviados</h2>
                <ul class="mt-2 divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($documents as $document)
                        <li class="flex items-center justify-between gap-3 py-2 text-sm">
                            <span class="truncate">{{ $document->original_name }}</span>
                            <span class="flex shrink-0 items-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                                {{ Number::fileSize($document->size) }}
                                <a href="{{ route('public.short-links.documents.download', $document) }}" class="text-indigo-600 dark:text-indigo-300 hover:underline">
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