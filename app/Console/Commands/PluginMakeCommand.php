<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PluginMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:make {alias : The plugin alias, for example: my-plugin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new plugin with its manifest, provider, routes and views';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        $alias = Str::kebab(Str::slug((string) $this->argument('alias')));

        if ($alias === '') {
            $this->error('The plugin alias is required.');

            return self::FAILURE;
        }

        $studly = Str::studly($alias);
        $name = Str::headline($alias);
        $path = rtrim((string) config('plugins.paths.plugins'), '/').'/'.$studly;

        if ($files->isDirectory($path)) {
            $this->error("Plugin [{$alias}] already exists at {$path}.");

            return self::FAILURE;
        }

        $namespace = trim((string) config('plugins.namespace', 'Plugins'), '\\').'\\'.$studly;

        $replacements = [
            '__NAMESPACE__' => $namespace,
            '__STUDLY__' => $studly,
            '__ALIAS__' => $alias,
            '__NAME__' => $name,
            '__DESCRIPTION__' => "The {$name} plugin.",
        ];

        $files->ensureDirectoryExists($path);

        $files->put(
            $path.'/plugin.json',
            json_encode([
                'name' => $name,
                'alias' => $alias,
                'description' => $replacements['__DESCRIPTION__'],
                'version' => '1.0.0',
                'icon' => 'cog-6-tooth',
                'active' => false,
                'priority' => 0,
                'providers' => [$namespace.'\\Providers\\'.$studly.'ServiceProvider'],
                'aliases' => [],
                'files' => [],
                'requires' => [],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL,
        );

        $files->put($path.'/composer.json', $this->render('composer.stub', [
            ...$replacements,
            '__VENDOR__' => (string) config('plugins.composer.vendor', 'documentation-center'),
            '__AUTHOR_NAME__' => (string) config('plugins.composer.author.name', 'Documentation Center'),
            '__AUTHOR_EMAIL__' => (string) config('plugins.composer.author.email', 'contato@example.com'),
        ]));

        $stubs = [
            'provider.stub' => 'Providers/'.$studly.'ServiceProvider.php',
            'routes.stub' => 'Routes/web.php',
            'view.stub' => 'Resources/views/index.blade.php',
            'lang.stub' => 'Resources/lang/en/general.php',
            'config.stub' => 'Config/config.php',
            'readme.stub' => 'README.md',
        ];

        foreach ($stubs as $stub => $destination) {
            $target = $path.'/'.$destination;

            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $this->render($stub, $replacements));
        }

        $this->components->info("Plugin [{$alias}] created at {$path}.");
        $this->components->bulletList([
            'Enable it with: php artisan plugin:enable '.$alias,
            'Run "composer dump-autoload" so the plugin namespace is autoloaded.',
        ]);

        return self::SUCCESS;
    }

    /**
     * Render a stub with the given replacements.
     *
     * @param  array<string, string>  $replacements
     */
    private function render(string $stub, array $replacements): string
    {
        $contents = (string) file_get_contents(__DIR__.'/stubs/plugin/'.$stub);

        return str_replace(array_keys($replacements), array_values($replacements), $contents);
    }
}
