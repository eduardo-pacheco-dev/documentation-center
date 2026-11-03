<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Campos do cadastro de estações
    |--------------------------------------------------------------------------
    |
    | A mesma definição alimenta o formulário das páginas de criação/edição e
    | o modal em etapas da listagem. Cada grupo representa uma etapa.
    |
    */

    'fields' => [
        [
            'title' => 'Identificação',
            'description' => 'Chaves, classificação e status do elemento de rede.',
            'fields' => [
                ['name' => 'site_id', 'label' => 'ID do site', 'required' => true, 'placeholder' => 'Ex.: SPJAB051M'],
                ['name' => 'endereco_id', 'label' => 'ID do endereço', 'placeholder' => 'Ex.: SPSPO_2360'],
                ['name' => 'tipo_de_elemento', 'label' => 'Tipo de elemento', 'placeholder' => 'Ex.: MSAN, eNodeB, ERB'],
                ['name' => 'tecnologia', 'label' => 'Tecnologia', 'placeholder' => 'Ex.: IP, LTE, 5G'],
                ['name' => 'tipo_de_conexao', 'label' => 'Tipo de conexão'],
                ['name' => 'classificacao', 'label' => 'Classificação', 'placeholder' => 'Ex.: Acesso'],
                ['name' => 'status', 'label' => 'Status', 'placeholder' => 'Ex.: Aquisitado'],
            ],
        ],
        [
            'title' => 'Infraestrutura',
            'description' => 'Identificador da estação, detentor da área, tipo de infraestrutura e torre.',
            'fields' => [
                ['name' => 'station_id', 'label' => 'ID da estação', 'placeholder' => 'Ex.: SPJAB051M'],
                ['name' => 'detentor_area', 'label' => 'Detentor da área', 'placeholder' => 'Ex.: Eletropaulo'],
                ['name' => 'tipo_de_infra', 'label' => 'Tipo de infraestrutura', 'placeholder' => 'Ex.: Greenfield'],
                ['name' => 'tipo_de_ev', 'label' => 'Tipo de EV'],
                ['name' => 'tipo_da_torre', 'label' => 'Tipo da torre'],
                ['name' => 'regional', 'label' => 'Regional', 'placeholder' => 'Ex.: TSP'],
            ],
        ],
        [
            'title' => 'Endereço',
            'description' => 'Informe o CEP para preencher o endereço automaticamente e ajuste o que precisar.',
            'component' => 'address',
            'fields' => [
                ['name' => 'cep', 'label' => 'CEP', 'placeholder' => '00000-000', 'inputmode' => 'numeric', 'maxlength' => 9],
                ['name' => 'logradouro', 'label' => 'Logradouro', 'placeholder' => 'Ex.: Rua das Palmas'],
                ['name' => 'numero', 'label' => 'Número', 'placeholder' => 'Ex.: 213'],
                ['name' => 'complemento', 'label' => 'Complemento', 'placeholder' => 'Ex.: Bloco B, sala 4'],
                ['name' => 'bairro', 'label' => 'Bairro', 'placeholder' => 'Ex.: Jabaquara'],
                ['name' => 'municipio', 'label' => 'Município', 'placeholder' => 'Ex.: São Paulo'],
                ['name' => 'estado', 'label' => 'Estado (UF)'],
            ],
        ],
        [
            'title' => 'Localização',
            'description' => 'Coordenadas geográficas em graus decimais ou em graus, minutos e segundos.',
            'fields' => [
                ['name' => 'latitude', 'label' => 'Latitude', 'placeholder' => 'Ex.: -23.665278 ou 23°39\'55"S', 'hint' => 'Aceita graus decimais ou graus, minutos e segundos (GMS).'],
                ['name' => 'longitude', 'label' => 'Longitude', 'placeholder' => 'Ex.: -46.641111 ou 46°38\'28"W', 'hint' => 'Aceita graus decimais ou graus, minutos e segundos (GMS).'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Unidades federativas
    |--------------------------------------------------------------------------
    |
    | Alimentam o select de ESTADO na etapa de endereço.
    |
    */

    'estados' => [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ],

];
