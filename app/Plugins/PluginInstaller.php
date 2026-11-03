<?php

namespace App\Plugins;

use App\Plugins\Exceptions\PluginInstallException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Installs a plugin shipped as a ZIP archive.
 *
 * The archive may hold the manifest at its root or inside a single top level
 * directory (the usual result of zipping a folder). Entries are validated
 * before extraction so an archive cannot write outside the plugins directory.
 */
class PluginInstaller
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
        private readonly PluginRepository $plugins,
    ) {}

    /**
     * Install the plugin contained in the given archive.
     *
     * @throws PluginInstallException
     */
    public function install(string $archivePath): Plugin
    {
        if (! class_exists(ZipArchive::class)) {
            throw PluginInstallException::zipUnsupported();
        }

        $temporaryPath = $this->temporaryPath();

        $this->extract($archivePath, $temporaryPath);

        try {
            $root = $this->locatePluginRoot($temporaryPath);

            $manifest = new PluginManifest($root.'/plugin.json', $this->files);
            $alias = $this->validateManifest($manifest);

            $destination = $this->pluginsPath().'/'.Str::studly($alias);

            if ($this->files->isDirectory($destination)) {
                throw PluginInstallException::alreadyInstalled($alias);
            }

            $this->files->ensureDirectoryExists(dirname($destination));

            $moved = $this->files->moveDirectory($root, $destination);

            // Windows can briefly lock a freshly extracted directory (antivirus
            // or the search indexer), so retry and fall back to copying.
            for ($attempt = 0; ! $moved && $attempt < 2; $attempt++) {
                usleep(100_000);
                clearstatcache();
                $moved = $this->files->moveDirectory($root, $destination);
            }

            if (! $moved) {
                $moved = $this->files->copyDirectory($root, $destination);
            }

            if (! $moved) {
                throw PluginInstallException::couldNotMove($destination);
            }
        } finally {
            if ($this->files->isDirectory($temporaryPath)) {
                $this->files->deleteDirectory($temporaryPath);
            }
        }

        $this->plugins->clear();

        $plugin = $this->plugins->findOrFail($alias);

        $this->app['events']->dispatch(sprintf('plugin.%s.installed', $alias), [$plugin]);

        return $plugin;
    }

    /**
     * Extract the archive into the temporary path, rejecting unsafe entries.
     *
     * @throws PluginInstallException
     */
    private function extract(string $archivePath, string $temporaryPath): void
    {
        $zip = new ZipArchive;

        if ($zip->open($archivePath) !== true) {
            throw PluginInstallException::invalidArchive();
        }

        try {
            $this->guardAgainstUnsafeEntries($zip);

            $this->files->ensureDirectoryExists($temporaryPath);

            if (! $zip->extractTo($temporaryPath)) {
                throw PluginInstallException::couldNotExtract();
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Reject absolute paths and path traversal attempts inside the archive.
     *
     * @throws PluginInstallException
     */
    private function guardAgainstUnsafeEntries(ZipArchive $zip): void
    {
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entry = (string) $zip->getNameIndex($index);

            $normalized = str_replace('\\', '/', $entry);

            if (
                str_starts_with($normalized, '/')
                || str_contains($normalized, "\0")
                || preg_match('#(^|/)\.\.(/|$)#', $normalized) === 1
            ) {
                throw PluginInstallException::unsafeEntry($entry);
            }
        }
    }

    /**
     * Find the directory holding the plugin manifest.
     *
     * @throws PluginInstallException
     */
    private function locatePluginRoot(string $temporaryPath): string
    {
        $manifest = (string) config('plugins.manifest', 'plugin.json');

        if ($this->files->exists($temporaryPath.'/'.$manifest)) {
            return $temporaryPath;
        }

        $candidates = array_values(array_filter(
            $this->files->directories($temporaryPath),
            fn (string $directory): bool => $this->files->exists($directory.'/'.$manifest),
        ));

        return match (count($candidates)) {
            0 => throw PluginInstallException::missingManifest(),
            1 => $candidates[0],
            default => throw PluginInstallException::multiplePlugins(),
        };
    }

    /**
     * Validate the manifest and return the plugin alias.
     *
     * @throws PluginInstallException
     */
    private function validateManifest(PluginManifest $manifest): string
    {
        $alias = $manifest->get('alias');

        if (! is_string($alias) || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $alias) !== 1) {
            throw PluginInstallException::invalidAlias();
        }

        $providers = $manifest->get('providers', []);

        if ($providers !== null && ! is_array($providers)) {
            throw new PluginInstallException('O campo "providers" do plugin.json precisa ser uma lista.');
        }

        return $alias;
    }

    private function pluginsPath(): string
    {
        return rtrim((string) config('plugins.paths.plugins'), '/');
    }

    private function temporaryPath(): string
    {
        return rtrim((string) config('plugins.tmp'), '/').'/'.Str::random(16);
    }
}
