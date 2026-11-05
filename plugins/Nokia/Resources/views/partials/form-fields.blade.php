@props([
    'prefix' => '',
    'site' => null,
])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';

    $fields = config('nokia.fields', []);
    $checklists = config('nokia.checklists', []);
@endphp

<fieldset>
    <legend class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Identificação</legend>
    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Informe os identificadores do site e as ordens de serviço relacionadas.</p>

    <div class="mt-3 grid gap-4 sm:grid-cols-2">
        @foreach ($fields as $field)
            <div>
                <label for="{{ $prefix }}{{ $field['name'] }}" class="{{ $labelClass }}">
                    {{ $field['label'] }}@if ($field['required'] ?? false)<span class="ml-0.5 text-red-500" aria-hidden="true">*</span>@endif
                </label>
                <input
                    id="{{ $prefix }}{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    type="text"
                    value="{{ old($field['name'], $site?->{$field['name']}) }}"
                    @if ($field['required'] ?? false) required @endif
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    @if ($field['name'] === 'site_id') data-autofocus @endif
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

    <div class="mt-3 grid gap-3 sm:grid-cols-2">
        @foreach ($checklists as $field => $label)
            @php
                $checked = (bool) old($field, $site?->{$field} ?? false);
            @endphp

            <label
                for="{{ $prefix }}{{ $field }}"
                class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-indigo-200 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <input type="hidden" name="{{ $field }}" value="{{ $checked ? '1' : '0' }}">
                <input
                    id="{{ $prefix }}{{ $field }}"
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
