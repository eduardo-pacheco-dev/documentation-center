<?php

namespace App\Plugins;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * A single plugin discovered on disk.
 *
 * Mirrors the Akaunting module contract: the plugin is a mini Laravel
 * application described by a manifest, identified by an alias and toggled
 * through the activator.
 */
class Plugin
{
    private ?PluginManifest $manifest = null;

    public function __construct(
        private readonly Application $app,
        private readonly string $alias,
        private readonly string $path,
    ) {}

    /**
     * The kebab-case alias used by the CLI, helpers and status storage.
     */
    public function getAlias(): string
    {
        return Str::kebab($this->alias);
    }

    /**
     * The kebab-case alias (Akaunting naming parity).
     */
    public function getLowerName(): string
    {
        return $this->getAlias();
    }

    /**
     * The namespace friendly name, e.g. "MyPlugin".
     */
    public function getStudlyName(): string
    {
        return Str::studly($this->getAlias());
    }

    /**
     * The human readable name, translated when the plugin ships translations.
     */
    public function getName(): string
    {
        $key = $this->getAlias().'::general.name';
        $name = trans($key);

        if ($name === $key) {
            $name = $this->get('name');
        }

        return is_string($name) && $name !== ''
            ? $name
            : Str::headline($this->getAlias());
    }

    /**
     * The human readable description, translated when available.
     */
    public function getDescription(): ?string
    {
        $key = $this->getAlias().'::general.description';
        $description = trans($key);

        if ($description === $key) {
            $description = $this->get('description');
        }

        return is_string($description) && $description !== '' ? $description : null;
    }

    /**
     * The plugin version declared in the manifest.
     */
    public function getVersion(): ?string
    {
        $version = $this->get('version');

        return is_string($version) ? $version : null;
    }

    /**
     * The path to the plugin directory.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * A path inside the plugin directory.
     */
    public function getExtraPath(string $path): string
    {
        return $this->getPath().'/'.ltrim($path, '/');
    }

    /**
     * The parsed manifest, cached for the plugin lifetime.
     */
    public function manifest(): PluginManifest
    {
        return $this->manifest ??= new PluginManifest(
            $this->getExtraPath((string) config('plugins.manifest', 'plugin.json')),
            $this->app->make(Filesystem::class),
        );
    }

    /**
     * Read an attribute from the manifest.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->manifest()->get($key, $default);
    }

    /**
     * The service providers declared by the plugin.
     *
     * @return list<string>
     */
    public function providers(): array
    {
        return array_values(array_filter(
            (array) $this->get('providers', []),
            fn (mixed $provider): bool => is_string($provider) && $provider !== '',
        ));
    }

    /**
     * The PHP files included during registration.
     *
     * @return list<string>
     */
    public function files(): array
    {
        return array_values(array_filter(
            (array) $this->get('files', []),
            fn (mixed $file): bool => is_string($file) && $file !== '',
        ));
    }

    /**
     * The plugin aliases declared by the plugin.
     *
     * @return array<string, string>
     */
    public function aliases(): array
    {
        $aliases = $this->get('aliases', []);

        return is_array($aliases) ? $aliases : [];
    }

    /**
     * The aliases of the plugins this plugin depends on.
     *
     * @return list<string>
     */
    public function requires(): array
    {
        return array_values(array_filter(
            (array) $this->get('requires', []),
            fn (mixed $require): bool => is_string($require) && $require !== '',
        ));
    }

    /**
     * Whether the plugin is currently enabled.
     */
    public function enabled(): bool
    {
        return $this->isStatus(true);
    }

    /**
     * Whether the plugin is currently disabled.
     */
    public function disabled(): bool
    {
        return $this->isStatus(false);
    }

    /**
     * Whether the plugin matches the given status.
     */
    public function isStatus(bool $status): bool
    {
        return $this->app->make(PluginActivator::class)->is($this, $status);
    }

    /**
     * Enable the plugin and dispatch its lifecycle events.
     */
    public function enable(): void
    {
        $this->app['events']->dispatch($this->eventName('enabling'), [$this]);

        $this->app->make(PluginActivator::class)->enable($this);

        $this->app['events']->dispatch($this->eventName('enabled'), [$this]);
    }

    /**
     * Disable the plugin and dispatch its lifecycle events.
     */
    public function disable(): void
    {
        $this->app['events']->dispatch($this->eventName('disabling'), [$this]);

        $this->app->make(PluginActivator::class)->disable($this);

        $this->app['events']->dispatch($this->eventName('disabled'), [$this]);
    }

    /**
     * The manifest attributes as a plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->manifest()->all();
    }

    public function __toString(): string
    {
        return $this->getStudlyName();
    }

    private function eventName(string $event): string
    {
        return sprintf('plugin.%s.%s', $this->getAlias(), $event);
    }
}
