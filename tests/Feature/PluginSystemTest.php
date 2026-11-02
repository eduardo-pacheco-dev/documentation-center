<?php

use App\Models\User;
use App\Plugins\Exceptions\PluginInstallException;
use App\Plugins\Exceptions\PluginNotFoundException;
use App\Plugins\Plugin;
use App\Plugins\PluginActivator;
use App\Plugins\PluginInstaller;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\Example\Providers\ExampleServiceProvider;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginActivator::class);
    app()->forgetInstance(PluginRepository::class);
});

afterEach(function () {
    File::delete($this->statusesPath);
});

it('discovers the bundled example plugin', function () {
    $plugin = app(PluginRepository::class)->findOrFail('example');

    expect($plugin)->toBeInstanceOf(Plugin::class)
        ->and($plugin->getAlias())->toBe('example')
        ->and($plugin->getVersion())->toBe('1.0.0')
        ->and($plugin->getName())->toBe('Example')
        ->and($plugin->getPath())->toBe(base_path('plugins/Example'))
        ->and($plugin->providers())->toContain(ExampleServiceProvider::class)
        ->and($plugin->getExtraPath('Routes/web.php'))->toBe(base_path('plugins/Example/Routes/web.php'));
});

it('normalizes aliases to kebab case', function () {
    $plugin = app(PluginRepository::class)->findOrFail('Example');

    expect($plugin->getAlias())->toBe('example')
        ->and($plugin->getStudlyName())->toBe('Example');
});

it('fails when a plugin does not exist', function () {
    app(PluginRepository::class)->findOrFail('missing-plugin');
})->throws(PluginNotFoundException::class);

it('enables and disables plugins through the status file', function () {
    $plugins = app(PluginRepository::class);
    $plugin = $plugins->findOrFail('example');

    expect($plugin->disabled())->toBeTrue();

    $plugin->enable();

    expect($plugin->enabled())->toBeTrue()
        ->and(json_decode(File::get($this->statusesPath), true))->toBe(['example' => true]);

    $plugin->disable();

    expect($plugin->disabled())->toBeTrue()
        ->and(json_decode(File::get($this->statusesPath), true))->toBe(['example' => false]);
});

it('legacy manifest active flag is used when there is no stored status', function () {
    File::put($this->statusesPath, json_encode([]));

    app()->forgetInstance(PluginActivator::class);

    expect(app(PluginRepository::class)->findOrFail('example')->disabled())->toBeTrue();
});

it('registers the routes and views of an enabled plugin', function () {
    app(PluginRepository::class)->findOrFail('example')->enable();

    app()->register(ExampleServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    expect(Route::has('plugins.example.index'))->toBeTrue();

    $this->get('/plugins/example')
        ->assertOk()
        ->assertSee('Example')
        ->assertSee('Plugin carregado com sucesso.');
});

it('lists plugins through the artisan command', function () {
    $this->artisan('plugin:list')
        ->expectsOutputToContain('example')
        ->assertSuccessful();
});

it('enables and disables plugins through the artisan commands', function () {
    $this->artisan('plugin:enable', ['alias' => 'example'])->assertSuccessful();
    $this->artisan('plugin:list', ['--only' => 'enabled'])->expectsOutputToContain('example')->assertSuccessful();

    $this->artisan('plugin:disable', ['alias' => 'example'])->assertSuccessful();
    $this->artisan('plugin:list', ['--only' => 'disabled'])->expectsOutputToContain('example')->assertSuccessful();
});

it('fails when enabling an unknown plugin through the artisan command', function () {
    $this->artisan('plugin:enable', ['alias' => 'missing-plugin'])
        ->expectsOutputToContain('was not found')
        ->assertFailed();
});

it('shows the plugins section inside the settings page for administrators', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/settings')
        ->assertOk()
        ->assertSee('Instalar plugin via ZIP')
        ->assertSee('Example')
        ->assertSee('1.0.0');
});

it('hides the plugins section from regular users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings')
        ->assertOk()
        ->assertDontSee('Instalar plugin via ZIP');
});

it('allows administrators to enable and disable a plugin', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->patch('/settings/plugins/example', ['enabled' => 1])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(json_decode(File::get($this->statusesPath), true))->toBe(['example' => true]);

    $this->actingAs($user)
        ->patch('/settings/plugins/example', ['enabled' => 0])
        ->assertRedirect();

    expect(json_decode(File::get($this->statusesPath), true))->toBe(['example' => false]);
});

it('validates the enabled field when toggling a plugin', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->patch('/settings/plugins/example', [])
        ->assertSessionHasErrors('enabled');
});

it('requires an administrator to manage plugins', function () {
    $this->actingAs(User::factory()->create())
        ->post('/settings/plugins', [
            'file' => UploadedFile::fake()->create('plugin.zip', 8, 'application/zip'),
        ])
        ->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->patch('/settings/plugins/example', ['enabled' => 1])
        ->assertForbidden();
});

it('requires authentication to manage plugins', function () {
    $this->patch('/settings/plugins/example', ['enabled' => 1])->assertRedirect('/login');
});

it('returns not found for unknown plugin aliases', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->patch('/settings/plugins/missing-plugin', ['enabled' => 1])
        ->assertNotFound();
});

it('scaffolds a new plugin through the artisan command', function () {
    $path = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $path]);

    File::ensureDirectoryExists($path);

    $this->artisan('plugin:make', ['alias' => 'My Plugin'])->assertSuccessful();

    expect($path.'/MyPlugin/plugin.json')->toBeReadableFile()
        ->and($path.'/MyPlugin/Providers/MyPluginServiceProvider.php')->toBeReadableFile()
        ->and($path.'/MyPlugin/Routes/web.php')->toBeReadableFile()
        ->and($path.'/MyPlugin/Resources/views/index.blade.php')->toBeReadableFile();

    $manifest = json_decode(File::get($path.'/MyPlugin/plugin.json'), true);

    expect($manifest['alias'])->toBe('my-plugin')
        ->and($manifest['providers'])->toBe(['Plugins\\MyPlugin\\Providers\\MyPluginServiceProvider']);

    File::deleteDirectory($path);
});

it('installs a plugin from a zip archive', function () {
    $pluginsPath = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $pluginsPath]);
    File::ensureDirectoryExists($pluginsPath);
    app()->forgetInstance(PluginRepository::class);

    $archive = pluginArchive([
        'plugin.json' => pluginManifestJson('uploaded-plugin', ['version' => '1.2.3']),
        'Routes/web.php' => "<?php\n",
    ]);

    $plugin = app(PluginInstaller::class)->install($archive);

    expect($plugin->getAlias())->toBe('uploaded-plugin')
        ->and($plugin->getVersion())->toBe('1.2.3')
        ->and($plugin->disabled())->toBeTrue()
        ->and($pluginsPath.'/UploadedPlugin/plugin.json')->toBeReadableFile()
        ->and($pluginsPath.'/UploadedPlugin/Routes/web.php')->toBeReadableFile();

    File::delete($archive);
    File::deleteDirectory($pluginsPath);
});

it('installs a plugin wrapped in a single directory', function () {
    $pluginsPath = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $pluginsPath]);
    File::ensureDirectoryExists($pluginsPath);
    app()->forgetInstance(PluginRepository::class);

    $archive = pluginArchive([
        'MyWrappedPlugin/plugin.json' => pluginManifestJson('wrapped-plugin'),
        'MyWrappedPlugin/README.md' => '# Wrapped',
    ]);

    $plugin = app(PluginInstaller::class)->install($archive);

    expect($plugin->getAlias())->toBe('wrapped-plugin')
        ->and($pluginsPath.'/WrappedPlugin/README.md')->toBeReadableFile();

    File::delete($archive);
    File::deleteDirectory($pluginsPath);
});

it('rejects an archive without a manifest', function () {
    $archive = pluginArchive(['README.md' => '# No manifest']);

    try {
        app(PluginInstaller::class)->install($archive);
    } finally {
        File::delete($archive);
    }
})->throws(PluginInstallException::class);

it('rejects an archive holding more than one plugin', function () {
    $archive = pluginArchive([
        'First/plugin.json' => pluginManifestJson('first-plugin'),
        'Second/plugin.json' => pluginManifestJson('second-plugin'),
    ]);

    try {
        app(PluginInstaller::class)->install($archive);
    } finally {
        File::delete($archive);
    }
})->throws(PluginInstallException::class);

it('rejects an archive with an invalid alias', function () {
    $archive = pluginArchive(['plugin.json' => pluginManifestJson('../evil')]);

    try {
        app(PluginInstaller::class)->install($archive);
    } finally {
        File::delete($archive);
    }
})->throws(PluginInstallException::class);

it('refuses to overwrite an installed plugin', function () {
    $archive = pluginArchive(['plugin.json' => pluginManifestJson('example')]);

    try {
        app(PluginInstaller::class)->install($archive);
    } finally {
        File::delete($archive);
    }
})->throws(PluginInstallException::class);

it('installs a plugin through the admin upload form', function () {
    $pluginsPath = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $pluginsPath]);
    File::ensureDirectoryExists($pluginsPath);
    app()->forgetInstance(PluginRepository::class);

    $archive = pluginArchive(['plugin.json' => pluginManifestJson('form-plugin')]);

    $this->actingAs(User::factory()->admin()->create())
        ->post('/settings/plugins', [
            'file' => new UploadedFile($archive, 'plugin.zip', 'application/zip', null, true),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    expect($pluginsPath.'/FormPlugin/plugin.json')->toBeReadableFile();

    File::delete($archive);
    File::deleteDirectory($pluginsPath);
});

it('validates the uploaded plugin archive', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/settings/plugins', [
            'file' => UploadedFile::fake()->create('plugin.txt', 8, 'text/plain'),
        ])
        ->assertSessionHasErrors('file');
});

it('installs a plugin through the artisan command', function () {
    $pluginsPath = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $pluginsPath]);
    File::ensureDirectoryExists($pluginsPath);
    app()->forgetInstance(PluginRepository::class);

    $archive = pluginArchive(['plugin.json' => pluginManifestJson('cli-plugin')]);

    $this->artisan('plugin:install', ['file' => $archive])
        ->expectsOutputToContain('cli-plugin')
        ->assertSuccessful();

    expect($pluginsPath.'/CliPlugin/plugin.json')->toBeReadableFile();

    File::delete($archive);
    File::deleteDirectory($pluginsPath);
});

/**
 * Build a ZIP archive holding the given files.
 *
 * @param  array<string, string>  $files
 */
function pluginArchive(array $files): string
{
    $path = storage_path('framework/testing/plugin_'.Str::random(10).'.zip');

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($files as $name => $contents) {
        $zip->addFromString($name, $contents);
    }

    $zip->close();

    return $path;
}

/**
 * Build a minimal plugin.json payload.
 *
 * @param  array<string, mixed>  $overrides
 */
function pluginManifestJson(string $alias, array $overrides = []): string
{
    return json_encode([
        'name' => Str::headline($alias),
        'alias' => $alias,
        'version' => '1.0.0',
        'active' => false,
        'providers' => [],
        ...$overrides,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

it('removes a plugin and its files', function () {
    $pluginsPath = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $pluginsPath]);
    app()->forgetInstance(PluginRepository::class);

    $pluginPath = pluginDirectory($pluginsPath, 'removable-plugin');

    app(PluginRepository::class)->findOrFail('removable-plugin')->enable();

    $this->actingAs(User::factory()->admin()->create())
        ->delete('/settings/plugins/removable-plugin')
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($pluginPath)->not->toBeDirectory()
        ->and(json_decode(File::get($this->statusesPath), true))->toBe([])
        ->and(app(PluginRepository::class)->has('removable-plugin'))->toBeFalse();

    File::deleteDirectory($pluginsPath);
});

it('removes a plugin through the artisan command', function () {
    $pluginsPath = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $pluginsPath]);
    app()->forgetInstance(PluginRepository::class);

    $pluginPath = pluginDirectory($pluginsPath, 'cli-removable');

    $this->artisan('plugin:remove', ['alias' => 'cli-removable', '--force' => true])
        ->expectsOutputToContain('cli-removable')
        ->assertSuccessful();

    expect($pluginPath)->not->toBeDirectory();

    File::deleteDirectory($pluginsPath);
});

it('asks for confirmation before removing a plugin through the artisan command', function () {
    $pluginsPath = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $pluginsPath]);
    app()->forgetInstance(PluginRepository::class);

    $pluginPath = pluginDirectory($pluginsPath, 'kept-plugin');

    $this->artisan('plugin:remove', ['alias' => 'kept-plugin'])
        ->expectsConfirmation('Remove plugin [kept-plugin]? Its files will be deleted.', 'no')
        ->assertSuccessful();

    expect($pluginPath)->toBeDirectory();

    File::deleteDirectory($pluginsPath);
});

it('requires an administrator to remove plugins', function () {
    $this->actingAs(User::factory()->create())
        ->delete('/settings/plugins/example')
        ->assertForbidden();
});

it('returns not found when removing an unknown plugin', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->delete('/settings/plugins/missing-plugin')
        ->assertNotFound();
});

/**
 * Create a minimal plugin directory in the given plugins path.
 */
function pluginDirectory(string $pluginsPath, string $alias): string
{
    $directory = rtrim($pluginsPath, '/').'/'.Str::studly($alias);

    File::ensureDirectoryExists($directory);
    File::put($directory.'/plugin.json', pluginManifestJson($alias));

    return $directory;
}

it('runs the plugin migrations when enabling it through the artisan command', function () {
    $path = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $path]);
    app()->forgetInstance(PluginRepository::class);

    pluginWithMigration($path, 'migrated-plugin');

    $this->artisan('plugin:enable', ['alias' => 'migrated-plugin'])
        ->expectsOutputToContain('migration(s) executed')
        ->assertSuccessful();

    expect(Schema::hasTable('migrated_widgets'))->toBeTrue();

    Schema::dropIfExists('migrated_widgets');
    File::deleteDirectory($path);
});

it('runs the plugin migrations when enabling it from the settings page', function () {
    $path = storage_path('framework/testing/plugins_'.Str::random(8));

    config(['plugins.paths.plugins' => $path]);
    app()->forgetInstance(PluginRepository::class);

    pluginWithMigration($path, 'settings-migrated');

    $this->actingAs(User::factory()->admin()->create())
        ->patch('/settings/plugins/settings-migrated', ['enabled' => 1])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Schema::hasTable('migrated_widgets'))->toBeTrue();

    Schema::dropIfExists('migrated_widgets');
    File::deleteDirectory($path);
});

/**
 * Create a plugin containing a migration that creates the migrated_widgets table.
 */
function pluginWithMigration(string $pluginsPath, string $alias): void
{
    $directory = rtrim($pluginsPath, '/').'/'.Str::studly($alias);

    File::ensureDirectoryExists($directory.'/Database/Migrations');

    File::put($directory.'/plugin.json', pluginManifestJson($alias));

    File::put(
        $directory.'/Database/Migrations/2026_01_01_000000_create_migrated_widgets_table.php',
        <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migrated_widgets', function (Blueprint $table): void {
            $table->id();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migrated_widgets');
    }
};
PHP,
    );
}
