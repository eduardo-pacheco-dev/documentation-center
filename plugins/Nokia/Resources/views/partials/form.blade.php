@props([
    'action',
    'method' => 'POST',
    'site' => null,
    'submitLabel' => 'Salvar',
])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';

    $fields = [
        ['name' => 'site_id', 'label' => 'Site ID', 'required' => true, 'placeholder' => 'SPABC123'],
        ['name' => 'end_id', 'label' => 'End ID', 'placeholder' => 'END-12345'],
        ['name' => 'oc', 'label' => 'OC', 'placeholder' => 'OC-1234'],
        ['name' => 'os_fam_entrega', 'label' => 'OS FAM Entrega', 'placeholder' => 'OS-E-12345'],
        ['name' => 'os_fam_instalacao', 'label' => 'OS FAM Instalação', 'placeholder' => 'OS-I-12345'],
    ];

    $checklists = config('nokia.checklists', []);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-8">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <fieldset>
        <legend class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Identificação</legend>
        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Informe os identificadores do site e as ordens de serviço relacionadas.</p>

        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            @foreach ($fields as $field)
                <div>
                    <label for="{{ $field['name'] }}" class="{{ $labelClass }}">
                        {{ $field['label'] }}@if ($field['required'] ?? false)<span class="ml-0.5 text-red-500" aria-hidden="true">*</span>@endif
                    </label>
                    <input
                        id="{{ $field['name'] }}"
                        name="{{ $field['name'] }}"
                        type="text"
                        value="{{ old($field['name'], $site?->{$field['name']}) }}"
                        @if ($field['required'] ?? false) required @endif
                        placeholder="{{ $field['placeholder'] }}"
                        @error($field['name']) aria-invalid="true" @enderror
                        class="{{ $inputClass }}"
                    >
                    @error($field['name'])
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Checklist de atividades</legend>
        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Marque as atividades já concluídas para este site.</p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($checklists as $field => $label)
                @php
                    $checked = (bool) old($field, $site?->{$field} ?? false);
                @endphp

                <label
                    for="{{ $field }}"
                    class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-indigo-200 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:hover:border-indigo-500/40"
                >
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input
                        id="{{ $field }}"
                        name="{{ $field }}"
                        type="checkbox"
                        value="1"
                        @checked($checked)
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/30 dark:border-white/20 dark:bg-white/[0.03]"
                    >
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>
        <x-ui.action :href="route('admin.nokia.sites.index')" variant="secondary">Cancelar</x-ui.action>
    </div>
</form>
