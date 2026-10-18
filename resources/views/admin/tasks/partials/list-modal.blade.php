@php
    use App\Enums\TodoColor;
    use App\Enums\TodoIcon;

    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200';
@endphp

<div
    id="task-list-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="task-list-modal-title"
    class="fixed inset-0 z-[100] hidden"
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-md -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-3">
            <h2 id="task-list-modal-title" data-list-modal-title class="text-base font-semibold text-gray-900 dark:text-gray-100">Nova lista</h2>
            <button
                type="button"
                data-modal-close
                class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>

        <form
            data-list-form
            data-store-url="{{ route('admin.todo-lists.store') }}"
            method="POST"
            action="{{ route('admin.todo-lists.store') }}"
            class="mt-4 space-y-4"
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
                            <span class="block h-7 w-7 rounded-full {{ $color->dotClasses() }} peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-offset-white {{ $color->ringClasses() }} dark:peer-checked:ring-offset-gray-900"></span>
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
                            <span class="flex h-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 dark:border-gray-700 dark:text-gray-400 dark:peer-checked:border-indigo-500 dark:peer-checked:bg-indigo-500/10 dark:peer-checked:text-indigo-300">
                                <x-icon :name="$icon->value" class="h-4 w-4" />
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" data-modal-close class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    Cancelar
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <x-icon name="check" class="h-4 w-4" />
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>
