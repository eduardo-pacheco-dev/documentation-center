@php
    $cardClass = 'rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]';
@endphp

<section class="{{ $cardClass }} p-5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Documentos</h2>
        <x-ui.badge tone="gray">{{ $station->documents->count() }}</x-ui.badge>
    </div>

    <form method="POST" action="{{ route('stations.documents.store', $station) }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-2">
        @csrf

        <div class="w-full max-w-sm">
            <label for="station-documents" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Anexar arquivos</label>
            <input
                id="station-documents"
                name="documents[]"
                type="file"
                multiple
                class="mt-1 block w-full cursor-pointer rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300"
            >
            @error('documents') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            @error('documents.*') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <x-ui.action type="submit" variant="secondary" icon="arrow-up-tray" class="h-10">
            Anexar
        </x-ui.action>
    </form>

    @if ($station->documents->isEmpty())
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum documento anexado.</p>
    @else
        <ul class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 dark:divide-white/10 dark:border-white/10">
            @foreach ($station->documents as $document)
                <li class="flex flex-wrap items-center gap-3 px-4 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="document-text" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $document->original_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ strtoupper($document->extension()) }} · {{ \Illuminate\Support\Number::fileSize($document->size) }}
                            · <x-relative-time :value="$document->created_at" />
                        </p>
                    </div>

                    <x-ui.action :href="route('stations.documents.download', [$station, $document])" size="sm" variant="secondary" icon="arrow-down-tray">
                        Baixar
                    </x-ui.action>

                    <form
                        method="POST"
                        action="{{ route('stations.documents.destroy', [$station, $document]) }}"
                        data-confirm="Remover o arquivo {{ $document->original_name }}?"
                        data-confirm-title="Remover documento"
                        data-confirm-label="Remover"
                    >
                        @csrf
                        @method('DELETE')

                        <x-ui.action type="submit" size="sm" variant="danger" icon="trash">
                            Remover
                        </x-ui.action>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</section>
