<?php

namespace App\Plugins;

use App\Plugins\Exceptions\PluginNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Discovers plugins on disk and keeps them in memory for the current request.
 */
class PluginRepository
{
    /**
     * @var Collection<string, Plugin>|null
     */
    private ?Collection $plugins = null;

    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
        private readonly string $path,
    ) {}

    /**
     * Every discovered plugin, keyed by alias.
     *
     * @return Collection<string, Plugin>
     */
    public function all(): Collection
    {
        return $this->plugins ??= $this->scan();
    }

    /**
     * Find a plugin by alias.
     */
    public function get(string $alias): ?Plugin
    {
        return $this->all()->get(Str::kebab($alias));
    }

    /**
     * Find a plugin by alias or fail.
     */
    public function findOrFail(string $alias): Plugin
    {
        return $this->get($alias) ?? throw PluginNotFoundException::named($alias);
    }

    /**
     * Determine whether the given alias is discovered.
     */
    public function has(string $alias): bool
    {
        return $this->get($alias) !== null;
    }

    /**
     * The enabled plugins.
     *
     * @return Collection<string, Plugin>
     */
    public function enabled(): Collection
    {
        return $this->all()->filter(fn (Plugin $plugin): bool => $plugin->enabled());
    }

    /**
     * The disabled plugins.
     *
     * @return Collection<string, Plugin>
     */
    public function disabled(): Collection
    {
        return $this->all()->filter(fn (Plugin $plugin): bool => $plugin->disabled());
    }

    /**
     * Forget the in-memory plugins so the next access rescans the disk.
     */
    public function clear(): void
    {
        $this->plugins = null;
    }

    /**
     * The navigation items contributed by the enabled plugins.
     *
     * @return list<array{label: string, route: string, icon: string, active: string, primary: bool}>
     */
    public function navigation(): array
    {
        return $this->enabled()
            ->flatMap(fn (Plugin $plugin): array => $plugin->navigation())
            ->values()
            ->all();
    }

    /**
     * The admin module keys declared by every plugin, enabled or not.
     *
     * The core navigation skips these modules so the plugin navigation is the
     * single source of truth and respects the plugin enabled state.
     *
     * @return list<string>
     */
    public function modules(): array
    {
        return $this->all()
            ->flatMap(fn (Plugin $plugin): array => $plugin->navigation())
            ->pluck('module')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The scanned paths.
     *
     * @return list<string>
     */
    public function paths(): array
    {
        return [$this->path];
    }

    /**
     * Scan the plugins path.
     *
     * @return Collection<string, Plugin>
     */
    private function scan(): Collection
    {
        if (! $this->files->isDirectory($this->path)) {
            return collect();
        }

        $manifest = (string) config('plugins.manifest', 'plugin.json');

        $plugins = collect($this->files->directories($this->path))
            ->filter(fn (string $directory): bool => $this->files->exists($directory.'/'.$manifest))
            ->map(function (string $directory) use ($manifest): ?Plugin {
                try {
                    $plugin = new Plugin($this->app, basename($directory), $directory);

                    return $plugin->manifest()->get('alias')
                        ? $this->rebuild($plugin, (string) $plugin->manifest()->get('alias'))
                        : $plugin;
                } catch (\JsonException $exception) {
                    $this->app['log']->warning('Skipping plugin with an invalid manifest.', [
                        'path' => $directory.'/'.$manifest,
                        'error' => $exception->getMessage(),
                    ]);

                    return null;
                }
            })
            ->filter()
            ->keyBy(fn (Plugin $plugin): string => $plugin->getAlias());

        return $plugins
            ->sortBy(fn (Plugin $plugin): array => [(int) $plugin->get('priority', 0), $plugin->getAlias()])
            ->values()
            ->keyBy(fn (Plugin $plugin): string => $plugin->getAlias());
    }

    /**
     * Rebuild a plugin using the alias declared in its manifest.
     */
    private function rebuild(Plugin $plugin, string $alias): Plugin
    {
        return $alias === $plugin->getAlias()
            ? $plugin
            : new Plugin($this->app, $alias, $plugin->getPath());
    }
}
