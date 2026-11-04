@props(['assignees', 'statuses', 'priorities', 'openOnLoad' => false])

<div
    x-data="{ open: false }"
    x-init="if (@js($openOnLoad)) open = true"
    x-on:open-follow-up-create.window="open = true"
    x-on:close-follow-up-create.window="open = false"
    x-on:keydown.escape.window="open = false"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.100ms
        class="fixed inset-0 z-[100]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="follow-up-create-title"
    >
        <div class="absolute inset-0 bg-gray-900/50" x-on:click="open = false"></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 shadow-xl dark:bg-[#1c1c1b]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="follow-up-create-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Novo follow-up</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie um acompanhamento com responsável, prazo e prioridade.</p>
                </div>

                <button
                    type="button"
                    x-on:click="open = false"
                    class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                    aria-label="Fechar"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-4">
                <x-follow-up::partials.form
                    :action="route('admin.follow-ups.store')"
                    :assignees="$assignees"
                    :statuses="$statuses"
                    :priorities="$priorities"
                    modal-mode
                />
            </div>
        </div>
    </div>
</div>
