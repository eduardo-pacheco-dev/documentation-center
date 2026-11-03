@props([
    'action',
    'method' => 'POST',
    'station' => null,
    'submitLabel' => 'Salvar',
])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';

    $groups = config('stations.fields', []);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-8">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    @foreach ($groups as $group)
        <fieldset>
            <legend class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ $group['title'] }}</legend>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $group['description'] }}</p>

            @if (($group['component'] ?? null) === 'address')
                <x-stations::partials.address-fields
                    :group="$group"
                    :station="$station"
                    :input-class="$inputClass"
                    :label-class="$labelClass"
                />
            @else
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    @foreach ($group['fields'] as $field)
                        <div @class(['sm:col-span-2' => ($field['span'] ?? 1) === 2])>
                            <label for="{{ $field['name'] }}" class="{{ $labelClass }}">{{ $field['label'] }}</label>
                            <input
                                id="{{ $field['name'] }}"
                                name="{{ $field['name'] }}"
                                type="text"
                                value="{{ old($field['name'], $station?->{$field['name']}) }}"
                                @if ($field['required'] ?? false) required @endif
                                @if (isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                                @if (isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                @if (isset($field['inputmode'])) inputmode="{{ $field['inputmode'] }}" @endif
                                @error($field['name']) aria-invalid="true" @enderror
                                class="{{ $inputClass }}"
                            >
                            @error($field['name'])
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            @endif
        </fieldset>
    @endforeach

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>
        <x-ui.action :href="route('admin.stations.index')" variant="secondary">Cancelar</x-ui.action>
    </div>
</form>
