<x-layouts.public title="{{ $shortLink->title }}">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Download de documentos</span>

        <h1 class="mt-3 text-lg font-semibold text-gray-900">{{ $shortLink->title }}</h1>
        @if ($shortLink->description)
            <p class="mt-1 text-sm text-gray-500">{{ $shortLink->description }}</p>
        @endif

        @if ($documents->isNotEmpty())
            <div class="mt-5 divide-y divide-gray-100 rounded-md border border-gray-200">
                @foreach ($documents as $document)
                    <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <span class="truncate">{{ $document->original_name }}</span>
                        <span class="flex shrink-0 items-center gap-3">
                            <span class="text-xs text-gray-400">{{ Number::fileSize($document->size) }}</span>
                            <a
                                href="{{ route('public.short-links.documents.download', $document) }}"
                                class="rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700"
                            >
                                Baixar
                            </a>
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-5 text-sm text-gray-500">Nenhum documento disponível para download neste link.</p>
        @endif
    </div>
</x-layouts.public>