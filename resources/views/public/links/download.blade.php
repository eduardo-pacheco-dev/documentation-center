<x-layouts.public title="{{ $shortLink->title }}">
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
        <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">Download de documentos</span>

        <h1 class="mt-3 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $shortLink->title }}</h1>
        @if ($shortLink->description)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $shortLink->description }}</p>
        @endif

        @if ($documents->isNotEmpty())
            <div class="mt-5 divide-y divide-gray-100 dark:divide-gray-800 rounded-md border border-gray-200 dark:border-gray-800">
                @foreach ($documents as $document)
                    <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <span class="truncate">{{ $document->original_name }}</span>
                        <span class="flex shrink-0 items-center gap-3">
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ Number::fileSize($document->size) }}</span>
                            <a
                                href="{{ route('public.short-links.documents.download', $document) }}"
                                class="rounded-md bg-gray-900 dark:bg-white px-3 py-1.5 text-xs font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                            >
                                Baixar
                            </a>
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-5 text-sm text-gray-500 dark:text-gray-400">Nenhum documento disponível para download neste link.</p>
        @endif
    </div>
</x-layouts.public>