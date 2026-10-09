<section class="mt-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            <x-icon name="paper-clip" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Anexos
        </h2>
    </div>

    <form
        method="POST"
        action="{{ route('admin.erbs.documents.store', $erb) }}"
        enctype="multipart/form-data"
        class="mt-3 rounded-xl border border-dashed border-gray-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
    >
        @csrf

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <label for="erb-documents" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adicionar anexos</label>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">PDF, Office, imagens ou texto — até 20 MB por arquivo.</p>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="file"
                    name="documents[]"
                    id="erb-documents"
                    multiple
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.odt,.ods,.jpg,.jpeg,.png"
                    class="block w-full max-w-xs text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-gray-700 dark:text-gray-300 dark:file:bg-white dark:file:text-gray-900 dark:hover:file:bg-gray-200"
                >
                <button
                    type="submit"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
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

    @if ($erb->documents->isEmpty())
        <p class="mt-3 rounded-xl border border-dashed border-gray-300 bg-white px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            Nenhum anexo cadastrado.
        </p>
    @else
        <ul class="mt-3 divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @foreach ($erb->documents as $document)
                <li class="flex items-center gap-3 px-4 py-3">
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
                            class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                            aria-label="Visualizar anexo"
                        >
                            <x-icon name="eye" class="h-4 w-4" />
                        </a>

                        <form method="POST" action="{{ route('admin.erbs.documents.destroy', [$erb, $document]) }}" data-confirm-delete="Tem certeza que deseja remover este anexo?">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-950 dark:hover:text-red-400"
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
