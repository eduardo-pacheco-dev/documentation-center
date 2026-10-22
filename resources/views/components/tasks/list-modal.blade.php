@php
    use App\Enums\TodoColor;
    use App\Enums\TodoIcon;

    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-200';
@endphp

<x-ui.modal id="task-list-modal" max-width="max-w-md">
    <x-slot:header>
        <h2 id="task-list-modal-title" data-list-modal-title class="text-base font-semibold text-gray-900 dark:text-gray-100">Nova lista</h2>
    </x-slot:header>

    <form
        data-list-form
        data-store-url="{{ route('admin.todo-lists.store') }}"
        method="POST"
        action="{{ route('admin.todo-lists.store') }}"
        class="space-y-4"
    >
        @csrf
        <input type="hidden" name="_method" value="POST" data-list-method>

        <div>
            <label for="task-list-name" class="{{ $label }}">Nome</label>
            <input
                id="task-list-name"
                data-list-name
                type="text"
                name="name"
                required
                maxlength="120"
                placeholder="Ex.: Trabalho"
                class="{{ $input }}"
            >
        </div>

        <div>
            <span class="{{ $label }}">Cor</span>
            <div class="mt-2 flex flex-wrap gap-2.5">
                @foreach (TodoColor::cases() as $color)
                    <label class="cursor-pointer" title="{{ $color->label() }}">
                        <input type="radio" name="color" value="{{ $color->value }}" data-list-color class="peer sr-only">
                        <span class="block h-7 w-7 rounded-full {{ $color->dotClasses() }} peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-offset-white {{ $color->ringClasses() }} dark:peer-checked:ring-offset-[#1c1c1b]"></span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <span class="{{ $label }}">Ícone</span>
            <div class="mt-2 grid grid-cols-6 gap-2">
                @foreach (TodoIcon::cases() as $icon)
                    <label class="cursor-pointer" title="{{ $icon->label() }}">
                        <input type="radio" name="icon" value="{{ $icon->value }}" data-list-icon class="peer sr-only">
                        <span class="flex h-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 dark:border-white/10 dark:text-gray-400 dark:peer-checked:border-indigo-500 dark:peer-checked:bg-indigo-500/10 dark:peer-checked:text-indigo-300">
                            <x-icon :name="$icon->value" class="h-4 w-4" />
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <x-ui.action type="button" variant="secondary" data-modal-close>Cancelar</x-ui.action>
            <x-ui.action type="submit" icon="check">Salvar</x-ui.action>
        </div>
    </form>
</x-ui.modal>
