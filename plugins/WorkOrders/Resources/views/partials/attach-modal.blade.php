@php
    $attachModalOpen = $errors->any() && old('modal') === 'attach';
@endphp

<div
    id="work-order-attach-modal"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="work-order-attach-modal-title"
    @if ($attachModalOpen) data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="work-order-attach-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="paper-clip" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Anexar arquivo
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Anexe os arquivos direto à ordem de serviço ou vincule-os a um item.</p>
            </div>

            <button
                type="button"
                class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                data-modal-close
            >
                <span class="sr-only">Fechar</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        @if ($attachModalOpen)
            <div
                data-modal-error
                class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                <p class="font-medium">Não foi possível enviar os anexos. Corrija os campos destacados e tente novamente.</p>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.work-orders.documents.store', $workOrder) }}"
            enctype="multipart/form-data"
            class="relative mt-5 space-y-4"
            x-data="documentPicker()"
            @dragover="onDragOver($event)"
            @dragleave="onDragLeave($event)"
            @drop="handleDrop($event)"
            @submit="startUpload($event)"
        >
            <div
                x-cloak
                x-show="uploading"
                class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 rounded-2xl bg-white/85 px-6 text-center backdrop-blur-sm dark:bg-gray-900/85"
                role="status"
                aria-live="polite"
            >
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="arrow-path" class="h-5 w-5 animate-spin" />
                </span>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Enviando arquivo…</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Aguarde enquanto o arquivo é carregado.</p>
            </div>

            @csrf
            <input type="hidden" name="modal" value="attach">

            <div>
                <label for="work-order-attach-item" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item da OS <span class="font-normal text-gray-400 dark:text-gray-500">(opcional)</span></label>
                <select
                    id="work-order-attach-item"
                    name="item_id"
                    class="mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    <option value="">Anexar direto à ordem de serviço</option>
                    @foreach ($workOrder->items as $item)
                        <option value="{{ $item->getKey() }}" @selected((string) old('item_id') === (string) $item->getKey())>
                            {{ $item->description }}
                        </option>
                    @endforeach
                </select>

                @if ($errors->has('item_id'))
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $errors->first('item_id') }}</p>
                @endif
            </div>

            <div>
                <label for="work-order-attach-documents" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivos</label>

                <input
                    type="file"
                    id="work-order-attach-documents"
                    name="documents[]"
                    x-ref="documents"
                    multiple
                    required
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.odt,.ods,.jpg,.jpeg,.png,.zip,.rar"
                    class="sr-only"
                    @change="handleSelect($event)"
                >

                <div
                    role="button"
                    tabindex="0"
                    class="mt-1 flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border border-dashed px-4 py-6 text-center transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                    :class="dragging
                        ? 'border-indigo-400 bg-indigo-50/70 dark:border-indigo-500/60 dark:bg-indigo-500/10'
                        : 'border-gray-300 hover:border-indigo-300 hover:bg-gray-50/70 dark:border-gray-700 dark:hover:border-indigo-500/40 dark:hover:bg-gray-800/40'"
                    @click="$refs.documents.click()"
                    @keydown.enter.prevent="$refs.documents.click()"
                    @keydown.space.prevent="$refs.documents.click()"
                >
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="arrow-up-tray" class="h-4 w-4" />
                    </span>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Arraste os arquivos aqui ou clique para buscar</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">PDF, Office, imagens, texto, ZIP ou RAR — até 100 MB por arquivo.</p>
                </div>

                <template x-if="files.length > 0">
                    <ul class="mt-2 max-h-40 space-y-1 overflow-auto">
                        <template x-for="file in files" :key="file.key">
                            <li class="flex items-center gap-2 rounded-lg border border-gray-200/70 px-2.5 py-1.5 dark:border-gray-800">
                                <span class="min-w-0 flex-1 truncate text-xs font-medium text-gray-700 dark:text-gray-300" x-text="file.name"></span>
                                <span class="shrink-0 text-[10px] text-gray-400 dark:text-gray-500" x-text="formatSize(file.size)"></span>
                                <button
                                    type="button"
                                    class="shrink-0 rounded-md p-0.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-950 dark:hover:text-red-400"
                                    :aria-label="'Remover ' + file.name"
                                    @click="removeFile(file.key)"
                                >
                                    <x-icon name="x-mark" class="h-3.5 w-3.5" />
                                </button>
                            </li>
                        </template>
                    </ul>
                </template>

                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" x-cloak x-show="error" x-text="error"></p>

                @foreach ($errors->getMessages() as $key => $messages)
                    @if (str_starts_with($key, 'documents'))
                        @foreach ($messages as $message)
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @endforeach
                    @endif
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    class="rounded-xl border border-gray-200/70 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:text-gray-200 dark:hover:border-indigo-500/40"
                    data-modal-close
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    :disabled="uploading"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <x-icon name="arrow-up-tray" class="h-4 w-4" />
                    Enviar
                </button>
            </div>
        </form>
    </div>
</div>
