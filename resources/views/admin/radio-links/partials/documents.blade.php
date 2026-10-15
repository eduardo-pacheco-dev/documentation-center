<section class="mt-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="flex items-center gap-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="paper-clip" class="h-4 w-4" />
            </span>
            Anexos
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $radioLink->documents->count() }}
            </span>
        </h2>
    </div>

    <form
        method="POST"
        action="{{ route('admin.radio-links.documents.store', $radioLink) }}"
        enctype="multipart/form-data"
        class="mt-3 rounded-2xl border border-dashed border-gray-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900"
    >
        @csrf

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <label for="radio-link-documents" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adicionar anexos</label>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">PDF, Office, imagens ou texto — até 20 MB por arquivo.</p>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="file"
                    name="documents[]"
                    id="radio-link-documents"
                    multiple
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.odt,.ods,.jpg,.jpeg,.png"
                    class="block w-full max-w-xs text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500 dark:text-gray-300"
                >
                <button
                    type="submit"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <x-icon name="arrow-up-tray" class="h-4 w-4" />
                    Enviar
                </button>
            </div>
        </div>

        @foreach ($errors->getMessages() as $key => $messages)
            @if (str_starts_with($key, 'documents'))
                @foreach ($messages as $message)
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @endforeach
            @endif
        @endforeach
    </form>

    @if ($radioLink->documents->isEmpty())
        <div class="mt-3 rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-12 text-center dark:border-gray-700 dark:bg-gray-900">
            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <x-icon name="paper-clip" class="h-5 w-5" />
            </span>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nenhum anexo cadastrado.</p>
        </div>
    @else
        <ul class="mt-3 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @foreach ($radioLink->documents as $document)
                <li class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                    <x-file-type-icon :name="$document->original_name" size="xs" />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $document->original_name }}">
                            {{ $document->original_name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ Number::fileSize($document->size) }}
                            ·
                            <x-relative-time :value="$document->created_at" />
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        <a
                            href="{{ route('admin.files.preview', $document) }}"
                            target="_blank"
                            rel="noopener"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                            aria-label="Visualizar anexo"
                        >
                            <x-icon name="eye" class="h-4 w-4" />
                        </a>

                        <form method="POST" action="{{ route('admin.radio-links.documents.destroy', [$radioLink, $document]) }}" data-confirm-delete="Tem certeza que deseja remover este anexo?">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-950 dark:hover:text-red-400"
                                aria-label="Remover anexo"
                            >
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
