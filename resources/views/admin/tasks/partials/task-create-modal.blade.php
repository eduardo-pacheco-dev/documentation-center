@php
    $fieldLabel = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $fieldInput = 'mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-200';
    $checkbox = 'h-4 w-4 rounded border-gray-300 accent-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-white/[0.03]';
@endphp

<div
    id="task-create-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="task-create-modal-title"
    class="fixed inset-0 z-[100] hidden"
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-lg -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 dark:bg-[#1c1c1b]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="task-create-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Nova tarefa</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preencha os detalhes e escolha a lista de destino.</p>
            </div>
            <button
                type="button"
                data-modal-close
                class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>

        <form method="POST" action="{{ route('admin.tasks.store') }}" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="task-create-title" class="{{ $fieldLabel }}">Nome</label>
                <input
                    id="task-create-title"
                    type="text"
                    name="title"
                    required
                    maxlength="255"
                    placeholder="O que precisa ser feito?"
                    class="{{ $fieldInput }}"
                >
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="task-create-list" class="{{ $fieldLabel }}">Lista</label>
                    <select id="task-create-list" name="todo_list_id" class="{{ $fieldInput }}">
                        @foreach ($lists as $list)
                            <option value="{{ $list->getKey() }}" @selected($activeList?->is($list))>{{ $list->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="task-create-due" class="{{ $fieldLabel }}">Vencimento</label>
                    <input id="task-create-due" type="date" name="due_at" class="{{ $fieldInput }}">
                </div>
            </div>

            <div>
                <label for="task-create-notes" class="{{ $fieldLabel }}">Notas</label>
                <textarea
                    id="task-create-notes"
                    name="notes"
                    rows="3"
                    maxlength="5000"
                    placeholder="Detalhes opcionais..."
                    class="{{ $fieldInput }}"
                ></textarea>
            </div>

            <div class="flex flex-wrap items-center gap-5">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="is_important" value="1" @checked($view === 'important') class="{{ $checkbox }}">
                    Importante
                </label>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="my_day" value="1" @checked($view === 'my_day') class="{{ $checkbox }}">
                    Meu Dia
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
                <button
                    type="button"
                    data-modal-close
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-900 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:text-white"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Criar tarefa
                </button>
            </div>
        </form>
    </div>
</div>
