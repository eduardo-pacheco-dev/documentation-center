@php
    $fieldLabel = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $fieldInput = 'mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
    $checkbox = 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900';
    $quickButton = 'inline-flex items-center gap-1 rounded-xl border border-gray-200/70 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-indigo-500/40';
@endphp

<div
    id="task-detail-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="task-detail-modal-title"
    class="fixed inset-0 z-[100] hidden"
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 dark:bg-gray-900">
        <div class="flex items-start justify-between gap-3">
            <h2 id="task-detail-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Detalhes da tarefa</h2>
            <button
                type="button"
                data-modal-close
                class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>

        <form id="task-detail-update-form" data-task-update-form method="POST" action="#" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="task-detail-title" class="{{ $fieldLabel }}">Nome</label>
                <input
                    id="task-detail-title"
                    data-task-title
                    type="text"
                    name="title"
                    required
                    maxlength="255"
                    class="{{ $fieldInput }}"
                >
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="task-detail-list" class="{{ $fieldLabel }}">Lista</label>
                    <select id="task-detail-list" data-task-list name="todo_list_id" class="{{ $fieldInput }}">
                        @foreach ($lists as $list)
                            <option value="{{ $list->getKey() }}">{{ $list->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="task-detail-repeat" class="{{ $fieldLabel }}">Repetição</label>
                    <select id="task-detail-repeat" data-task-repeat name="repeat_type" class="{{ $fieldInput }}">
                        @foreach (App\Enums\TodoRepeat::cases() as $repeat)
                            <option value="{{ $repeat->value }}">{{ $repeat->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="task-detail-due" class="{{ $fieldLabel }}">Vencimento</label>
                    <input id="task-detail-due" data-task-due type="date" name="due_at" class="{{ $fieldInput }}">
                </div>

                <div>
                    <label for="task-detail-remind" class="{{ $fieldLabel }}">Lembrete</label>
                    <input id="task-detail-remind" data-task-remind type="datetime-local" name="remind_at" class="{{ $fieldInput }}">
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" data-task-due-quick="today" class="{{ $quickButton }}">Hoje</button>
                <button type="button" data-task-due-quick="tomorrow" class="{{ $quickButton }}">Amanhã</button>
                <button type="button" data-task-due-quick="next-week" class="{{ $quickButton }}">Próxima semana</button>
                <button type="button" data-task-due-quick="clear" class="{{ $quickButton }}">Sem data</button>
            </div>

            <div class="flex flex-wrap items-center gap-5">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input data-task-important type="checkbox" name="is_important" value="1" class="{{ $checkbox }}">
                    Importante
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input data-task-my-day type="checkbox" name="my_day" value="1" class="{{ $checkbox }}">
                    Adicionar ao Meu Dia
                </label>
            </div>

            <div>
                <label for="task-detail-notes" class="{{ $fieldLabel }}">Anotações</label>
                <textarea id="task-detail-notes" data-task-notes name="notes" rows="3" maxlength="5000" class="{{ $fieldInput }}"></textarea>
            </div>

        </form>

        <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <x-icon name="check-circle" class="h-4 w-4 text-gray-400" />
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Etapas</h3>
                <span data-task-steps-count class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">0</span>
            </div>

            <ul data-task-steps class="mt-2 space-y-1.5"></ul>

            <form data-task-step-form method="POST" action="#" class="mt-2 flex items-center gap-2">
                @csrf
                <label for="task-step-title" class="sr-only">Nova etapa</label>
                <input
                    id="task-step-title"
                    type="text"
                    name="title"
                    required
                    maxlength="255"
                    placeholder="Adicionar etapa"
                    class="min-w-0 flex-1 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Adicionar
                </button>
            </form>
        </div>

        <form id="task-detail-delete-form" data-task-delete-form method="POST" action="#" data-confirm-delete="Remover esta tarefa?" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <div class="mt-5 flex items-center justify-between gap-2">
            <button
                type="submit"
                form="task-detail-delete-form"
                class="inline-flex items-center gap-1.5 rounded-xl border border-red-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/30 dark:bg-gray-900 dark:text-red-400 dark:hover:bg-red-500/10"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Excluir
            </button>

            <div class="flex items-center gap-2">
                <button type="button" data-modal-close class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    Cancelar
                </button>
                <button type="submit" form="task-detail-update-form" class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                    <x-icon name="check" class="h-4 w-4" />
                    Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<template data-task-step-template>
    <li class="flex items-center gap-2 rounded-xl border border-gray-200/70 bg-white px-2.5 py-1.5 dark:border-gray-800 dark:bg-gray-900">
        <form data-step-form method="POST" action="#" class="flex min-w-0 flex-1 items-center gap-2">
            @csrf
            @method('PATCH')
            <input type="hidden" name="completed" value="0">
            <input
                type="checkbox"
                data-step-toggle
                name="completed"
                value="1"
                class="h-4 w-4 shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                aria-label="Concluir etapa"
            >
            <input
                type="text"
                data-step-title
                name="title"
                maxlength="255"
                class="min-w-0 flex-1 rounded-lg border border-transparent bg-transparent px-1.5 py-1 text-sm text-gray-700 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:text-gray-200 dark:focus:bg-gray-900"
            >
            <button type="submit" class="hidden">Salvar etapa</button>
        </form>

        <form data-step-delete-form method="POST" action="#" data-confirm-delete="Remover esta etapa?">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-800"
                aria-label="Remover etapa"
            >
                <x-icon name="x-mark" class="h-3.5 w-3.5" />
            </button>
        </form>
    </li>
</template>
