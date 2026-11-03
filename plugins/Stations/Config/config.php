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
                ['name' => 'site_id', 'label' => 'SITE_ID', 'required' => true, 'placeholder' => 'SPJAB051M'],
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
            'description' => 'Identificador da estação, detentor da área, tipo de infraestrutura e torre.',
            'fields' => [
                ['name' => 'station_id', 'label' => 'STATION_ID', 'placeholder' => 'SPJAB051M'],
                ['name' => 'detentor_area', 'label' => 'DETENTOR_AREA', 'placeholder' => 'ELETROPAULO'],
                ['name' => 'tipo_de_infra', 'label' => 'TIPO_DE_INFRA', 'placeholder' => 'Greenfield'],
                ['name' => 'tipo_de_ev', 'label' => 'TIPO_DE_EV'],
                ['name' => 'tipo_da_torre', 'label' => 'TIPO_DA_TORRE'],
                ['name' => 'regional', 'label' => 'REGIONAL', 'placeholder' => 'TSP'],
            ],
        ],
        [
            'title' => 'Endereço',
            'description' => 'Informe o CEP para preencher o endereço automaticamente e ajuste o que precisar.',
            'component' => 'address',
            'fields' => [
                ['name' => 'cep', 'label' => 'CEP', 'placeholder' => '00000-000', 'inputmode' => 'numeric', 'maxlength' => 9],
                ['name' => 'tipo_de_logradouro', 'label' => 'TIPO_DE_LOGRADOURO', 'placeholder' => 'RUA'],
                ['name' => 'logradouro', 'label' => 'LOGRADOURO', 'placeholder' => 'LAS PALMAS'],
                ['name' => 'numero', 'label' => 'NUMERO'],
                ['name' => 'complemento', 'label' => 'COMPLEMENTO'],
                ['name' => 'bairro', 'label' => 'BAIRRO'],
                ['name' => 'municipio', 'label' => 'MUNICIPIO'],
                ['name' => 'estado', 'label' => 'ESTADO'],
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
