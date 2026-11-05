<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Listagem
    |--------------------------------------------------------------------------
    |
    | Quantidade de follow-ups exibidos por página na listagem.
    |
    */

    'per_page' => 15,

    /*
    |--------------------------------------------------------------------------
    | Prazos
    |--------------------------------------------------------------------------
    |
    | Número de dias usados para destacar prazos próximos e o limite de itens
    | exibidos no widget do dashboard.
    |
    */

    'due_soon_days' => 7,

    'widget_limit' => 5,

    /*
    |--------------------------------------------------------------------------
    | E-mail
    |--------------------------------------------------------------------------
    |
    | Assunto padrão do envio manual e horários do resumo periódico enviado
    | para o criador de cada follow-up em aberto.
    |
    */

    'email' => [

        'subject_prefix' => 'Follow-up',

        'summary_times' => ['08:00', '17:00'],

        'summary_days' => [0, 1, 2, 3, 4, 5, 6],

        'summary_enabled' => true,

    ],

];
