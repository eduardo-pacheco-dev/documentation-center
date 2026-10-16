@php
    $openItemId = (int) (old('item_id') ?? request('item'));
    $itemDocumentsErrors = $errors->has('documents') || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'documents'));
@endphp

<div
    id="work-order-item-modal-{{ $item->getKey() }}"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="work-order-item-modal-title-{{ $item->getKey() }}"
    @if ($openItemId === $item->getKey()) data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h2 id="work-order-item-modal-title-{{ $item->getKey() }}" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="cube" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Detalhes do item
                </h2>
                <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
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

        <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Descrição</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $item->description }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Item do catálogo</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $item->catalogItem?->name ?? '—' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Unidade</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $item->unit }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Status</dt>
                <dd class="mt-1">
                    @can('update', $workOrder)
                        @php
                            $selectedStatus = ($openItemId === $item->getKey() && old('status'))
                                ? old('status')
                                : $item->status->value;
                        @endphp
                        <form
                            method="POST"
                            action="{{ route('admin.work-orders.items.status.update', [$workOrder, $item]) }}"
                        >
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="item_id" value="{{ $item->getKey() }}">

                            <select
                                name="status"
                                onchange="this.form.submit()"
                                class="w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                            >
                                @foreach (\App\Enums\WorkOrderItemStatus::cases() as $statusOption)
                                    <option value="{{ $statusOption->value }}" @selected($selectedStatus === $statusOption->value)>{{ $statusOption->label() }}</option>
                                @endforeach
                            </select>
                        </form>

                        @if ($openItemId === $item->getKey() && $errors->has('status'))
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $errors->first('status') }}</p>
                        @endif
                    @else
                        <x-work-order-item-status :item="$item" />
                    @endcan
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Quantidade</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $quantity($item->quantity) }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Valor unitário</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $money((float) $item->unit_price) }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Subtotal</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $item->total) }}</dd>
            </div>
        </dl>

        <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                    <x-icon name="paper-clip" class="h-4 w-4" />
                </span>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Anexos</h3>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $item->documents->count() }}
                </span>
            </div>

            @can('update', $workOrder)
                <form
                    method="POST"
                    action="{{ route('admin.work-orders.items.documents.store', [$workOrder, $item]) }}"
                    enctype="multipart/form-data"
                    class="mt-3"
                >
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->getKey() }}">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input
                            type="file"
                            name="documents[]"
                            multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.odt,.ods,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500 dark:text-gray-300"
                        >
                        <button
                            type="submit"
                            class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        >
                            <x-icon name="arrow-up-tray" class="h-4 w-4" />
                            Enviar
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">PDF, Office, imagens ou texto — até 20 MB por arquivo.</p>

                    @if ($itemDocumentsErrors && $openItemId === $item->getKey())
                        @foreach ($errors->getMessages() as $key => $messages)
                            @if (str_starts_with($key, 'documents'))
                                @foreach ($messages as $message)
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                </form>
            @endcan

            @if ($item->documents->isEmpty())
                <div class="mt-3 rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum anexo cadastrado.</p>
                </div>
            @else
                <ul class="mt-3 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/70 dark:divide-gray-800 dark:border-gray-800">
                    @foreach ($item->documents as $document)
                        <li class="flex items-center gap-3 px-3 py-3 transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
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

                                @can('update', $workOrder)
                                    <form method="POST" action="{{ route('admin.work-orders.items.documents.destroy', [$workOrder, $item, $document]) }}" data-confirm-delete="Tem certeza que deseja remover este anexo?">
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
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
