@php
    use App\Services\Forms\FormModuleSettings;

    $typesValue = old('types', $settings->types());
    $typesValue = is_array($typesValue) ? implode("\n", $typesValue) : (string) $typesValue;

    $statusesValue = old('statuses', $settings->statuses());
    $statusesValue = is_array($statusesValue) ? implode("\n", $statusesValue) : (string) $statusesValue;

    $fieldClass = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $hintClass = 'mt-1 text-xs text-gray-500 dark:text-gray-400';
    $cardClass = 'overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900';
@endphp

<x-layouts.admin title="Configurações de Formulários">
    <div class="border-b border-gray-200 pb-6 dark:border-gray-800">
        <nav class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
            <a href="{{ route('admin.forms.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Formulários</a>
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            <span class="text-gray-700 dark:text-gray-200">Configurações</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm sm:inline-flex dark:bg-indigo-500">
                    <x-icon name="cog-6-tooth" class="h-6 w-6" />
                </span>

                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Configurações de Formulários</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Ajuste os tipos, status e preferências deste módulo. As configurações são individuais para a sua conta.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('admin.forms.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="chevron-left" class="h-4 w-4" />
                Voltar para formulários
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-6 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <x-icon name="exclamation-circle" class="mt-0.5 h-4 w-4 shrink-0" />
            <p>Não foi possível salvar as configurações. Corrija os itens destacados e tente novamente.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.forms.settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="{{ $cardClass }}">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="adjustments-horizontal" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Tipos de formulário
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Opções disponíveis ao criar ou editar um formulário.</p>
                </div>

                <div class="p-5">
                    <label for="types" class="{{ $labelClass }}">Tipos</label>
                    <textarea
                        name="types"
                        id="types"
                        rows="7"
                        required
                        class="{{ $fieldClass }} font-mono"
                    >{{ $typesValue }}</textarea>
                    <p class="{{ $hintClass }}">Informe um tipo por linha. Os tipos atuais dos formulários existentes continuam disponíveis.</p>
                    @error('types')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @foreach ($errors->get('types.*') as $messages)
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $messages[0] }}</p>
                    @endforeach
                </div>
            </section>

            <section class="{{ $cardClass }}">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="flag" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Status de formulário
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Situações disponíveis para acompanhar os formulários.</p>
                </div>

                <div class="p-5">
                    <label for="statuses" class="{{ $labelClass }}">Status</label>
                    <textarea
                        name="statuses"
                        id="statuses"
                        rows="7"
                        required
                        class="{{ $fieldClass }} font-mono"
                    >{{ $statusesValue }}</textarea>
                    <p class="{{ $hintClass }}">Informe um status por linha. Os status atuais dos formulários existentes continuam disponíveis.</p>
                    @error('statuses')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @foreach ($errors->get('statuses.*') as $messages)
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $messages[0] }}</p>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="{{ $cardClass }}">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="squares-2x2" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Preferências do módulo
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Valores usados como padrão ao abrir a lista e criar novos formulários.</p>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="default_template_id" class="{{ $labelClass }}">Template padrão</label>
                    <select name="default_template_id" id="default_template_id" class="{{ $fieldClass }}">
                        <option value="">Começar do zero</option>
                        @foreach ($templates as $template)
                            <option
                                value="{{ $template->id }}"
                                @selected((string) old('default_template_id', (string) ($settings->defaultTemplateId() ?? '')) === (string) $template->id)
                            >
                                {{ $template->name }}@if ($template->type) &mdash; {{ $template->type }}@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="{{ $hintClass }}">Pré-selecionado ao criar um novo formulário.</p>
                    @error('default_template_id')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="default_view" class="{{ $labelClass }}">Visualização padrão</label>
                    <select name="default_view" id="default_view" class="{{ $fieldClass }}" required>
                        @foreach (FormModuleSettings::VIEWS as $value => $label)
                            <option value="{{ $value }}" @selected(old('default_view', $settings->defaultView()) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="{{ $hintClass }}">Modo de exibição inicial da lista de formulários.</p>
                    @error('default_view')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="per_page" class="{{ $labelClass }}">Itens por página</label>
                    <select name="per_page" id="per_page" class="{{ $fieldClass }}" required>
                        @foreach (FormModuleSettings::PER_PAGE_OPTIONS as $option)
                            <option value="{{ $option }}" @selected((int) old('per_page', $settings->perPage()) === $option)>{{ $option }} itens</option>
                        @endforeach
                    </select>
                    <p class="{{ $hintClass }}">Quantidade de formulários exibidos por página.</p>
                    @error('per_page')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-800">
            <a
                href="{{ route('admin.forms.index') }}"
                class="rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="check" class="h-4 w-4" />
                Salvar configurações
            </button>
        </div>
    </form>
</x-layouts.admin>
