<?php

namespace App\Plugins;

use DateTimeInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A storage sandbox scoped to a single plugin.
 *
 * Every path handled by this class is relative to the plugin directory on the
 * configured disk, so plugins can never read or overwrite files that belong to
 * the core application or to another plugin.
 */
final class PluginStorage
{
    public function __construct(private readonly Plugin $plugin) {}

    /**
     * Resolve the storage sandbox for a plugin instance or alias.
     */
    public static function for(Plugin|string $plugin): self
    {
        if (is_string($plugin)) {
            $plugin = app(PluginRepository::class)->findOrFail($plugin);
        }

        return new self($plugin);
    }

    /**
     * The plugin that owns this storage sandbox.
     */
    public function plugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * The name of the disk used by this plugin.
     */
    public function diskName(): string
    {
        $storage = $this->plugin->get('storage');

        if (is_array($storage) && is_string($storage['disk'] ?? null) && $storage['disk'] !== '') {
            return $storage['disk'];
        }

        return (string) config('plugins.storage.disk', 'plugins');
    }

    /**
     * The filesystem disk used by this plugin.
     */
    public function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }

    /**
     * The root key that isolates this plugin on the disk.
     */
    public function root(): string
    {
        $root = trim((string) config('plugins.storage.root', ''), '/');

        return $root === '' ? $this->plugin->getAlias() : $root.'/'.$this->plugin->getAlias();
    }

    /**
     * Build the full disk key for a path inside the plugin directory.
     */
    public function path(string $path = ''): string
    {
        return $this->key($path);
    }

    /**
     * Write contents to a file and return its plugin relative path.
     *
     * @param  array<string, mixed>  $options
     */
    public function put(string $path, string $contents, array $options = []): string
    {
        $key = $this->key($path);

        $this->disk()->put($key, $contents, $this->options($options));

        return $this->relative($key);
    }

    /**
     * Store an uploaded or local file, optionally under a generated name.
     *
     * @param  array<string, mixed>  $options
     */
    public function store(UploadedFile|File $file, string $directory = '', ?string $name = null, array $options = []): string
    {
        $key = $this->disk()->putFileAs(
            $this->key($directory),
            $file,
            $name ?? $file->hashName(),
            $this->options($options),
        );

        if (! is_string($key)) {
            throw new RuntimeException('Unable to store the file on the plugin storage disk.');
        }

        return $this->relative($key);
    }

    /**
     * Read the contents of a file.
     */
    public function get(string $path): ?string
    {
        return $this->disk()->get($this->key($path));
    }

    /**
     * Whether a file exists.
     */
    public function exists(string $path): bool
    {
        return $this->disk()->exists($this->key($path));
    }

    /**
     * Whether a file is missing.
     */
    public function missing(string $path): bool
    {
        return $this->disk()->missing($this->key($path));
    }

    /**
     * Delete a file.
     */
    public function delete(string $path): bool
    {
        return $this->disk()->delete($this->key($path));
    }

    /**
     * List the files directly inside a directory.
     *
     * @return list<string>
     */
    public function files(string $directory = ''): array
    {
        return array_map(
            fn (string $key): string => $this->relative($key),
            $this->disk()->files($this->key($directory)),
        );
    }

    /**
     * List every file below a directory.
     *
     * @return list<string>
     */
    public function allFiles(string $directory = ''): array
    {
        return array_map(
            fn (string $key): string => $this->relative($key),
            $this->disk()->allFiles($this->key($directory)),
        );
    }

    /**
     * The size of a file in bytes.
     */
    public function size(string $path): int
    {
        return $this->disk()->size($this->key($path));
    }

    /**
     * The public URL of a file, when the disk supports it.
     */
    public function url(string $path): string
    {
        return $this->disk()->url($this->key($path));
    }

    /**
     * A temporary URL for a private file, when the disk supports it.
     *
     * @param  array<string, mixed>  $options
     */
    public function temporaryUrl(string $path, DateTimeInterface $expiration, array $options = []): string
    {
        return $this->disk()->temporaryUrl($this->key($path), $expiration, $options);
    }

    /**
     * Stream a file as a download response.
     */
    public function download(string $path, ?string $name = null): StreamedResponse
    {
        return $this->disk()->download($this->key($path), $name);
    }

    /**
     * Normalize a plugin relative path into a full disk key.
     */
    private function key(string $path): string
    {
        $path = trim($path, '/');

        if ($path === '') {
            return $this->root();
        }

        if (str_contains($path, '\\')) {
            throw new InvalidArgumentException("Invalid plugin storage path [{$path}].");
        }

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidArgumentException("Invalid plugin storage path [{$path}].");
            }
        }

        return $this->root().'/'.$path;
    }

    /**
     * Strip the plugin root from a full disk key.
     */
    private function relative(string $key): string
    {
        $prefix = $this->root().'/';

        return str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key;
    }

    /**
     * Merge the configured visibility into the filesystem options.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function options(array $options): array
    {
        $visibility = $this->plugin->get('storage.visibility') ?? config('plugins.storage.visibility');

        return is_string($visibility) && $visibility !== ''
            ? $options + ['visibility' => $visibility]
            : $options;
    }
}
