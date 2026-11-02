@php
    $cardClass = 'rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]';
@endphp

<section class="{{ $cardClass }} p-5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Comentários</h2>
        <x-ui.badge tone="gray">{{ $station->comments->count() }}</x-ui.badge>
    </div>

    <form method="POST" action="{{ route('stations.comments.store', $station) }}" class="mt-4">
        @csrf

        <label for="comment-body" class="sr-only">Comentário</label>
        <textarea
            id="comment-body"
            name="body"
            rows="3"
            placeholder="Escreva um comentário..."
            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:focus:bg-white/5"
        >{{ old('body') }}</textarea>

        @error('body') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

        <div class="mt-2 flex justify-end">
            <x-ui.action type="submit" variant="secondary" icon="paper-airplane" class="h-10">
                Comentar
            </x-ui.action>
        </div>
    </form>

    @if ($station->comments->isEmpty())
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum comentário ainda.</p>
    @else
        <ul class="mt-4 space-y-3">
            @foreach ($station->comments as $comment)
                <li class="rounded-2xl border border-gray-200/80 p-3 dark:border-white/10">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                            {{ strtoupper(substr($comment->user?->name ?? '?', 0, 1)) }}
                        </span>

                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $comment->user?->name ?? 'Usuário' }}</p>
                        <span class="text-xs text-gray-500 dark:text-gray-400"><x-relative-time :value="$comment->created_at" /></span>

                        @if ($comment->user_id === auth()->id())
                            <form
                                method="POST"
                                action="{{ route('stations.comments.destroy', [$station, $comment]) }}"
                                class="ml-auto"
                                data-confirm="Remover este comentário?"
                                data-confirm-title="Remover comentário"
                                data-confirm-label="Remover"
                            >
                                @csrf
                                @method('DELETE')

                                <x-ui.action type="submit" size="sm" variant="ghost" icon="trash">
                                    Remover
                                </x-ui.action>
                            </form>
                        @endif
                    </div>

                    <p class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</section>
