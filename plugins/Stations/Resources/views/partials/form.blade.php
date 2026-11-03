@props([
    'action',
    'method' => 'POST',
    'station' => null,
    'submitLabel' => 'Salvar',
])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';

    $groups = [
        [
            'title' => 'Identificação',
            'description' => 'Chaves, classificação e status do elemento de rede.',
            'fields' => [
                ['name' => 'site_id', 'label' => 'SITE_ID', 'required' => true, 'placeholder' => 'SPJAB051M'],
                ['name' => 'station_id', 'label' => 'STATION_ID', 'placeholder' => 'SPJAB051M'],
                ['name' => 'endereco_id', 'label' => 'ENDERECO_ID', 'placeholder' => 'SPSPO_2360'],
                ['name' => 'tipo_de_elemento', 'label' => 'TIPO_DE_ELEMENTO', 'placeholder' => 'MSAN, ENODE B, ERB...'],
                ['name' => 'tecnologia', 'label' => 'TECNOLOGIA', 'placeholder' => 'IP, LTE, 5G...'],
                ['name' => 'tipo_de_conexao', 'label' => 'TIPO_DE_CONEXAO'],
                ['name' => 'classificacao', 'label' => 'CLASSIFICACAO', 'placeholder' => 'ACESSO'],
                ['name' => 'status', 'label' => 'STATUS', 'placeholder' => 'Aquisitado'],
            ],
        ],
        [
            'title' => 'Infraestrutura',
            'description' => 'Detentor da área, tipo de infraestrutura e torre.',
            'fields' => [
                ['name' => 'detentor_area', 'label' => 'DETENTOR_AREA', 'placeholder' => 'ELETROPAULO'],
                ['name' => 'tipo_de_infra', 'label' => 'TIPO_DE_INFRA', 'placeholder' => 'Greenfield'],
                ['name' => 'tipo_de_ev', 'label' => 'TIPO_DE_EV'],
                ['name' => 'tipo_da_torre', 'label' => 'TIPO_DA_TORRE'],
                ['name' => 'regional', 'label' => 'REGIONAL', 'placeholder' => 'TSP'],
            ],
        ],
        [
            'title' => 'Endereço',
            'description' => 'Localização do endereço associado à estação.',
            'fields' => [
                ['name' => 'tipo_de_logradouro', 'label' => 'TIPO_DE_LOGRADOURO', 'placeholder' => 'RUA'],
                ['name' => 'logradouro', 'label' => 'LOGRADOURO', 'span' => 2],
                ['name' => 'numero', 'label' => 'NUMERO'],
                ['name' => 'complemento', 'label' => 'COMPLEMENTO'],
                ['name' => 'bairro', 'label' => 'BAIRRO'],
                ['name' => 'municipio', 'label' => 'MUNICIPIO'],
                ['name' => 'estado', 'label' => 'ESTADO', 'maxlength' => 2, 'placeholder' => 'SP'],
                ['name' => 'cep', 'label' => 'CEP', 'placeholder' => '04339000'],
            ],
        ],
        [
            'title' => 'Localização',
            'description' => 'Coordenadas geográficas em graus decimais.',
            'fields' => [
                ['name' => 'latitude', 'label' => 'LATITUDE', 'placeholder' => '-23.665278', 'inputmode' => 'decimal'],
                ['name' => 'longitude', 'label' => 'LONGITUDE', 'placeholder' => '-46.641111', 'inputmode' => 'decimal'],
            ],
        ],
    ];
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
                            class="{{ $inputClass }}"
                        >
                        @error($field['name'])
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endforeach

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>
        <x-ui.action :href="route('admin.stations.index')" variant="secondary">Cancelar</x-ui.action>
    </div>
</form>
