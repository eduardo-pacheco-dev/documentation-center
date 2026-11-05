<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Paginação e limites
    |--------------------------------------------------------------------------
    |
    | Quantidade de registros exibidos no painel e limite de linhas aceito em
    | cada importação de planilha.
    |
    */

    'per_page' => 20,

    'max_import_rows' => 5000,

    /*
    |--------------------------------------------------------------------------
    | Status operacionais
    |--------------------------------------------------------------------------
    |
    | Agrupamentos usados pelos indicadores e widgets. A comparação é feita
    | sem diferenciar maiúsculas/minúsculas e aceita trechos do status.
    |
    */

    'accepted_status_terms' => ['aceit', 'conclu', 'entreg', 'finaliz'],

    'pending_status_terms' => ['aguardando', 'pend', 'impeditiv', 'corre'],

];
