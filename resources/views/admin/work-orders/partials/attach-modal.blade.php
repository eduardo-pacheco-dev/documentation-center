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
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Os arquivos ficam vinculados ao item da OS selecionado.</p>
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
            class="mt-5 space-y-4"
        >
            @csrf
            <input type="hidden" name="modal" value="attach">

            <div>
                <label for="work-order-attach-item" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item da OS</label>
                <select
                    id="work-order-attach-item"
                    name="item_id"
                    required
                    class="mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                    <option value="">Selecione o item</option>
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
                    multiple
                    required
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.odt,.ods,.jpg,.jpeg,.png"
                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">PDF, Office, imagens ou texto — até 20 MB por arquivo.</p>

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
                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <x-icon name="arrow-up-tray" class="h-4 w-4" />
                    Enviar
                </button>
            </div>
        </form>
    </div>
</div>
