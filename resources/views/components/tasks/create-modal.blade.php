@props([
    'lists',
    'agendaEvents',
    'activeList' => null,
    'view' => 'my_day',
])

@php
    $fieldLabel = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $fieldInput = 'mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-200';
    $checkbox = 'h-4 w-4 rounded border-gray-300 accent-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-white/[0.03]';
@endphp

<x-ui.modal
    id="task-create-modal"
    title="Nova tarefa"
    description="Preencha os detalhes e escolha a lista de destino."
>
    <form method="POST" action="{{ route('admin.tasks.store') }}" class="space-y-4">
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
            <label for="task-create-agenda-event" class="{{ $fieldLabel }}">Evento da agenda</label>
            <select id="task-create-agenda-event" name="agenda_event_id" class="{{ $fieldInput }}">
                <option value="">Sem vínculo</option>
                @foreach ($agendaEvents as $event)
                    <option value="{{ $event->getKey() }}">{{ $event->title }} — {{ $event->start_at->format('d/m/Y H:i') }}</option>
                @endforeach
            </select>
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
            <x-ui.action type="button" variant="secondary" data-modal-close>Cancelar</x-ui.action>
            <x-ui.action type="submit" icon="plus">Criar tarefa</x-ui.action>
        </div>
    </form>
</x-ui.modal>
