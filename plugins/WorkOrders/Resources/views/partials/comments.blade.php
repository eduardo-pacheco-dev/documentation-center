<section class="mt-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="flex items-center gap-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <x-icon name="chat-bubble-oval-left-ellipsis" class="h-4 w-4" />
            </span>
            Comentários
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ ($comments->total() ?? $workOrder->comments->count()) }} {{ ($comments->total() ?? $workOrder->comments->count()) === 1 ? 'comentário' : 'comentários' }}
            </span>
        </h2>
    </div>

    @can('update', $workOrder)
        <form
            method="POST"
            action="{{ route('admin.work-orders.comments.store', $workOrder) }}"
            enctype="multipart/form-data"
            class="mt-3 rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            x-data="commentImagePicker()"
            @dragover="onDragOver($event)"
            @dragleave="onDragLeave($event)"
            @drop="handleDrop($event)"
            @paste="handlePaste($event)"
        >
            @csrf

            <label for="work-order-comment-body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adicionar comentário</label>
            <textarea
                name="body"
                id="work-order-comment-body"
                rows="3"
                placeholder="Escreva um comentário sobre esta ordem de serviço..."
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <input
                type="file"
                name="images[]"
                x-ref="images"
                multiple
                accept="image/png,image/jpeg,image/webp,image/gif"
                class="sr-only"
                @change="handleSelect($event)"
            >

            <div
                role="button"
                tabindex="0"
                class="mt-3 flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border border-dashed px-4 py-6 text-center transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                :class="dragging
                    ? 'border-indigo-400 bg-indigo-50/70 dark:border-indigo-500/60 dark:bg-indigo-500/10'
                    : 'border-gray-300 hover:border-indigo-300 hover:bg-gray-50/70 dark:border-gray-700 dark:hover:border-indigo-500/40 dark:hover:bg-gray-800/40'"
                @click="$refs.images.click()"
                @keydown.enter.prevent="$refs.images.click()"
                @keydown.space.prevent="$refs.images.click()"
            >
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="photo" class="h-4 w-4" />
                </span>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Arraste imagens, clique para buscar ou cole com Ctrl + V</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">JPG, PNG, WEBP ou GIF — até 10 imagens, 10 MB cada.</p>
            </div>

            @foreach ($errors->getMessages() as $key => $messages)
                @if (str_starts_with($key, 'images'))
                    @foreach ($messages as $message)
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @endforeach
                @endif
            @endforeach

            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" x-cloak x-show="error" x-text="error"></p>

            <template x-if="files.length > 0">
                <ul class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                    <template x-for="file in files" :key="file.key">
                        <li class="group relative overflow-hidden rounded-xl border border-gray-200/70 dark:border-gray-800">
                            <img :src="file.url" :alt="file.name" class="h-20 w-full object-cover">

                            <button
                                type="button"
                                class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-900/70 text-white transition hover:bg-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                                :aria-label="'Remover ' + file.name"
                                @click="removeFile(file.key)"
                            >
                                <x-icon name="x-mark" class="h-3.5 w-3.5" />
                            </button>

                            <span class="absolute inset-x-0 bottom-0 truncate bg-gray-900/60 px-1.5 py-0.5 text-[10px] font-medium text-white" x-text="formatSize(file.size)"></span>
                        </li>
                    </template>
                </ul>
            </template>

            <div class="mt-3 flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <x-icon name="chat-bubble-oval-left-ellipsis" class="h-4 w-4" />
                    Comentar
                </button>
            </div>
        </form>
    @endcan

    @if (($comments->total() ?? $workOrder->comments->count()) === 0)
        <div class="mt-3 rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-12 text-center dark:border-gray-700 dark:bg-gray-900">
            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500">
                <x-icon name="chat-bubble-oval-left-ellipsis" class="h-5 w-5" />
            </span>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nenhum comentário registrado.</p>
        </div>
    @else
        <ul class="mt-3 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @foreach ($comments as $comment)
                <li class="flex items-start gap-3 px-4 py-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="user" class="h-5 w-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $comment->user?->name ?? 'Usuário removido' }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <x-relative-time :value="$comment->created_at" />
                            </span>
                        </div>

                        @if (filled($comment->body))
                            <p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
                        @endif

                        @if ($comment->documents->isNotEmpty())
                            <ul class="mt-2 flex flex-wrap gap-2">
                                @foreach ($comment->documents as $image)
                                    <li>
                                        <a
                                            href="{{ route('admin.files.preview', $image) }}"
                                            target="_blank"
                                            rel="noopener"
                                            title="Visualizar {{ $image->original_name }}"
                                            data-preview-open
                                            data-preview-url="{{ route('admin.files.preview', $image) }}"
                                            data-preview-name="{{ $image->original_name }}"
                                            data-preview-mime="{{ $image->mime_type ?? '' }}"
                                            data-preview-size="{{ Number::fileSize($image->size) }}"
                                            class="group relative block overflow-hidden rounded-xl border border-gray-200/70 transition hover:border-indigo-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40 dark:border-gray-800 dark:hover:border-indigo-500/40"
                                        >
                                            <img
                                                src="{{ route('admin.files.preview', $image) }}"
                                                alt="{{ $image->original_name }}"
                                                loading="lazy"
                                                class="h-24 w-24 object-cover"
                                            >
                                            <span class="absolute inset-0 flex items-center justify-center bg-gray-900/0 text-white opacity-0 transition group-hover:bg-gray-900/40 group-hover:opacity-100">
                                                <x-icon name="eye" class="h-5 w-5" />
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    @can('update', $workOrder)
                        <form method="POST" action="{{ route('admin.work-orders.comments.destroy', [$workOrder, $comment]) }}" data-confirm-delete="Tem certeza que deseja remover este comentário?">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-950 dark:hover:text-red-400"
                                aria-label="Remover comentário"
                            >
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    @endcan
                </li>
            @endforeach
        </ul>

        @if (isset($comments) && $comments->hasPages())
            <div class="mt-3 flex items-center justify-center">
                {{ $comments->links() }}
            </div>
        @endif
    @endif
</section>

<script>
    window.commentImagePicker = window.commentImagePicker || function () {
        return {
            files: [],
            dragging: false,
            error: '',
            maxFiles: 10,
            maxSize: 10 * 1024 * 1024,
            allowedTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],

            handleSelect(event) {
                this.addFiles(event.target.files);
            },

            addFiles(list) {
                this.error = '';

                Array.from(list ?? []).forEach((file) => {
                    if (this.files.length >= this.maxFiles) {
                        this.error = 'Você pode anexar no máximo ' + this.maxFiles + ' imagens.';
                        return;
                    }

                    if (! this.allowedTypes.includes(file.type)) {
                        this.error = 'Apenas imagens JPG, PNG, WEBP ou GIF são aceitas.';
                        return;
                    }

                    if (file.size > this.maxSize) {
                        this.error = 'Cada imagem deve ter no máximo 10 MB.';
                        return;
                    }

                    this.files.push({
                        key: file.name + '-' + file.size + '-' + this.files.length,
                        name: file.name || 'imagem',
                        size: file.size,
                        file: file,
                        url: URL.createObjectURL(file),
                    });
                });

                this.sync();
            },

            removeFile(key) {
                const index = this.files.findIndex((item) => item.key === key);

                if (index === -1) {
                    return;
                }

                URL.revokeObjectURL(this.files[index].url);
                this.files.splice(index, 1);
                this.sync();
            },

            sync() {
                const transfer = new DataTransfer();

                this.files.forEach((item) => transfer.items.add(item.file));

                this.$refs.images.files = transfer.files;
            },

            onDragOver(event) {
                if (! this.hasFiles(event)) {
                    return;
                }

                event.preventDefault();
                this.dragging = true;
            },

            onDragLeave(event) {
                if (! event.currentTarget.contains(event.relatedTarget)) {
                    this.dragging = false;
                }
            },

            handleDrop(event) {
                this.dragging = false;

                if (! this.hasFiles(event)) {
                    return;
                }

                event.preventDefault();
                this.addFiles(event.dataTransfer?.files);
            },

            hasFiles(event) {
                return Array.from(event.dataTransfer?.types ?? []).includes('Files');
            },

            handlePaste(event) {
                const pasted = Array.from(event.clipboardData?.items ?? [])
                    .filter((item) => item.kind === 'file' && item.type.startsWith('image/'))
                    .map((item) => item.getAsFile())
                    .filter((file) => file !== null);

                if (pasted.length === 0) {
                    return;
                }

                event.preventDefault();
                this.addFiles(pasted);
            },

            formatSize(bytes) {
                if (bytes < 1024) {
                    return bytes + ' B';
                }

                if (bytes < 1024 * 1024) {
                    return Math.round(bytes / 1024) + ' KB';
                }

                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            },
        };
    };
</script>
