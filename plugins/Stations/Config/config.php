<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Station code
    |--------------------------------------------------------------------------
    |
    | Prefix used when generating a sequential code for new stations.
    |
    */

    'code_prefix' => 'ST',

    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    |
    | Disk and directory used to store station attachments.
    |
    */

    'documents' => [
        'disk' => 'local',
        'path' => 'stations/documents',
        'max_files' => 10,
        'max_size' => 20480,
        'types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt', 'ods', 'jpg', 'jpeg', 'png'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Address lookup
    |--------------------------------------------------------------------------
    |
    | External services used to fill the address fields. They are optional:
    | when unreachable, the form still accepts manual input.
    |
    */

    'lookup' => [
        'zip_url' => 'https://viacep.com.br/ws/{zip}/json/',
        'coordinates_url' => 'https://nominatim.openstreetmap.org/reverse',
        'user_agent' => env('NOMINATIM_USER_AGENT', 'Documentation Center'),
        'timeout' => 10,
    ],

];
