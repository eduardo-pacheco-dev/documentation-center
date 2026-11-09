<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Checklist de atividades
    |--------------------------------------------------------------------------
    |
    | Cada item é armazenado como um booleano na tabela nokia_sites e exibido
    | no formulário e na listagem de sites.
    |
    */

    'checklists' => [
        'checklist_fam_entrega' => 'FAM Entrega',
        'checklist_fam_instalacao' => 'FAM Instalação',
        'checklist_fam_panoramica' => 'FAM Panorâmica',
    ],

    /*
    |--------------------------------------------------------------------------
    | Campos do site
    |--------------------------------------------------------------------------
    |
    | Definição compartilhada entre o formulário de página e o modal.
    |
    */

    'import' => [
        'memory_limit' => '512M',
        'max_rows' => 1000,
        'max_columns' => 100,
    ],

    'fields' => [
        ['name' => 'site_id', 'label' => 'Site ID', 'required' => true, 'placeholder' => 'SPABC123'],
        ['name' => 'end_id', 'label' => 'End ID', 'required' => false, 'placeholder' => 'END-12345'],
        ['name' => 'oc', 'label' => 'OC', 'required' => false, 'placeholder' => 'OC-1234'],
        ['name' => 'os_fam_entrega', 'label' => 'OS FAM Entrega', 'required' => false, 'placeholder' => 'OS-E-12345'],
        ['name' => 'os_fam_instalacao', 'label' => 'OS FAM Instalação', 'required' => false, 'placeholder' => 'OS-I-12345'],
        ['name' => 'regional', 'label' => 'Regional', 'required' => false, 'placeholder' => 'TNO'],
        ['name' => 'subcon', 'label' => 'Subcon', 'required' => false, 'placeholder' => 'AFL'],
    ],

];
