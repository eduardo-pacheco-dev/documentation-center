<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plugin Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace used by the classes shipped inside a plugin. It maps to the
    | "plugins" path declared below through the composer PSR-4 autoload.
    |
    */

    'namespace' => 'Plugins',

    'paths' => [

        /*
        |--------------------------------------------------------------------------
        | Plugins Path
        |--------------------------------------------------------------------------
        |
        | Every directory inside this path containing a manifest is treated as a
        | plugin. The directory name is the plugin's studly name.
        |
        */

        'plugins' => base_path('plugins'),

        /*
        |--------------------------------------------------------------------------
        | Plugin Assets Path
        |--------------------------------------------------------------------------
        |
        | Destination used when a plugin publishes its public assets.
        |
        */

        'assets' => public_path('plugins'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Manifest File
    |--------------------------------------------------------------------------
    |
    | The file read from every plugin directory to describe the plugin.
    |
    */

    'manifest' => 'plugin.json',

    /*
    |--------------------------------------------------------------------------
    | Status File
    |--------------------------------------------------------------------------
    |
    | Persistent storage for the enabled/disabled state. Kept on the storage
    | disk (and out of version control) so enabling a plugin never rewrites
    | the manifest tracked in git.
    |
    */

    'statuses' => env('PLUGIN_STATUSES', storage_path('plugin_statuses.json')),

    /*
    |--------------------------------------------------------------------------
    | Temporary Upload Path
    |--------------------------------------------------------------------------
    |
    | Where uploaded plugin archives are extracted before being moved into
    | the plugins directory.
    |
    */

    'tmp' => storage_path('framework/plugin-install'),

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Optionally cache the discovered manifests and statuses. Disabled by
    | default so enabling or disabling a plugin is visible immediately.
    |
    */

    'cache' => [
        'enabled' => env('PLUGIN_CACHE_ENABLED', false),
        'key' => 'plugins',
        'lifetime' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloading
    |--------------------------------------------------------------------------
    |
    | Mirrors the Akaunting module contract: plugin level "files" and composer
    | autoloaders can be loaded during the "register" or "boot" stage.
    |
    */

    'autoload' => [
        'translations' => true,
        'files' => 'register',
        'composer' => 'register',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Every plugin receives an isolated directory inside the configured disk.
    | A plugin may override the disk in its manifest through the
    | "storage.disk" attribute, while the visibility applies to new files.
    |
    */

    'storage' => [
        'disk' => env('PLUGIN_STORAGE_DISK', 'plugins'),
        'root' => env('PLUGIN_STORAGE_ROOT', ''),
        'visibility' => env('PLUGIN_STORAGE_VISIBILITY', 'private'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Composer Template
    |--------------------------------------------------------------------------
    |
    | Values used by the "plugin:make" command when scaffolding a plugin.
    |
    */

    'composer' => [
        'vendor' => 'documentation-center',
        'author' => [
            'name' => 'Documentation Center',
            'email' => 'contato@example.com',
        ],
    ],

];
