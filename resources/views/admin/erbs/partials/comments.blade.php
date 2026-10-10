<section class="mt-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            <x-icon name="chat-bubble-oval-left-ellipsis" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            Comentários
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $erb->comments->count() }}
            </span>
        </h2>
    </div>

    <form
        method="POST"
        action="{{ route('admin.erbs.comments.store', $erb) }}"
        class="mt-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
    >
        @csrf

        <label for="erb-comment-body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adicionar comentário</label>
        <textarea
            name="body"
            id="erb-comment-body"
            rows="3"
            placeholder="Escreva um comentário sobre esta ERB..."
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >{{ old('body') }}</textarea>
        @error('body')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <div class="mt-3 flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="chat-bubble-oval-left-ellipsis" class="h-4 w-4" />
                Comentar
            </button>
        </div>
    </form>

    @if ($erb->comments->isEmpty())
        <p class="mt-3 rounded-xl border border-dashed border-gray-300 bg-white px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            Nenhum comentário registrado.
        </p>
    @else
        <ul class="mt-3 divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @foreach ($erb->comments as $comment)
                <li class="flex items-start gap-3 px-4 py-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-icon name="user" class="h-5 w-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $comment->user?->name ?? 'Usuário removido' }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <x-relative-time :value="$comment->created_at" />
                            </span>
                        </div>

                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
                    </div>

                    <form method="POST" action="{{ route('admin.erbs.comments.destroy', [$erb, $comment]) }}" data-confirm-delete="Tem certeza que deseja remover este comentário?" class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-950 dark:hover:text-red-400"
                            aria-label="Remover comentário"
                        >
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</section>
