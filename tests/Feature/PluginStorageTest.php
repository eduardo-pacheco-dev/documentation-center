<?php

use App\Plugins\Exceptions\PluginNotFoundException;
use App\Plugins\PluginStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('plugins');

    config([
        'plugins.storage.disk' => 'plugins',
        'plugins.storage.root' => '',
        'plugins.storage.visibility' => 'private',
    ]);
});

it('resolves an isolated sandbox for a plugin', function () {
    $storage = plugin_storage('example');

    expect($storage)->toBeInstanceOf(PluginStorage::class)
        ->and($storage->plugin()->getAlias())->toBe('example')
        ->and($storage->diskName())->toBe('plugins')
        ->and($storage->root())->toBe('example')
        ->and($storage->path('images/logo.png'))->toBe('example/images/logo.png');
});

it('stores and reads files inside the plugin directory', function () {
    $storage = plugin_storage('example');

    $path = $storage->put('documents/readme.txt', 'conteúdo');

    expect($path)->toBe('documents/readme.txt')
        ->and($storage->get('documents/readme.txt'))->toBe('conteúdo')
        ->and($storage->exists('documents/readme.txt'))->toBeTrue()
        ->and($storage->missing('documents/missing.txt'))->toBeTrue()
        ->and(Storage::disk('plugins')->exists('example/documents/readme.txt'))->toBeTrue();
});

it('stores uploaded files with a generated name', function () {
    $storage = plugin_storage('example');

    $path = $storage->store(UploadedFile::fake()->createWithContent('document.pdf', 'conteúdo'), 'documents');

    expect($path)->toStartWith('documents/')
        ->and($storage->exists($path))->toBeTrue()
        ->and($storage->size($path))->toBeGreaterThan(0);
});

it('lists only the files that belong to the plugin', function () {
    $example = plugin_storage('example');
    $stations = plugin_storage('stations');

    $example->put('reports/a.txt', 'a');
    $example->put('reports/b.txt', 'b');
    $stations->put('reports/c.txt', 'c');

    expect($example->files('reports'))->toHaveCount(2)
        ->and($example->allFiles())->toContain('reports/a.txt', 'reports/b.txt')
        ->and($example->allFiles())->not->toContain('reports/c.txt')
        ->and($stations->allFiles())->toBe(['reports/c.txt']);
});

it('deletes files from the plugin directory', function () {
    $storage = plugin_storage('example');

    $storage->put('temp/file.txt', 'temporary');

    expect($storage->delete('temp/file.txt'))->toBeTrue()
        ->and($storage->exists('temp/file.txt'))->toBeFalse();
});

it('rejects paths that escape the plugin directory', function (string $path) {
    plugin_storage('example')->put($path, 'escape');
})->with([
    'parent directory' => ['../escape.txt'],
    'nested parent directory' => ['documents/../../escape.txt'],
    'backslash' => ['documents\\..\\escape.txt'],
])->throws(InvalidArgumentException::class);

it('throws when the plugin does not exist', function () {
    plugin_storage('missing-plugin');
})->throws(PluginNotFoundException::class);

it('uses the disk configured by the system', function () {
    Storage::fake('local');

    config(['plugins.storage.disk' => 'local']);

    $storage = plugin_storage('example');

    expect($storage->diskName())->toBe('local');

    $storage->put('config.txt', 'value');

    expect(Storage::disk('local')->exists('example/config.txt'))->toBeTrue();
});

it('honours a disk declared by the plugin manifest', function () {
    $plugin = plugin('example');

    $plugin->manifest()->set('storage.disk', 'local');

    expect(plugin_storage($plugin)->diskName())->toBe('local');
});
