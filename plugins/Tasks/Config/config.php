<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Limites de listagem
    |--------------------------------------------------------------------------
    |
    | Quantidade máxima de tarefas carregadas em cada seção do workspace.
    |
    */

    'limits' => [
        'tasks' => 150,
        'completed' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exportação
    |--------------------------------------------------------------------------
    |
    | Nome do arquivo gerado na exportação das tarefas em Excel.
    |
    */

    'export' => [
        'filename' => 'tarefas.xlsx',
    ],

];
