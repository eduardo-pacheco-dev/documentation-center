<?php

namespace App\Plugins;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class PluginManifest
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes;

    /**
     * @throws \JsonException
     */
    public function __construct(
        private readonly string $path,
        private readonly Filesystem $files,
    ) {
        $decoded = json_decode(
            $this->files->get($this->path),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->attributes = is_array($decoded) ? $decoded : [];
    }

    /**
     * The full manifest contents.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * Read an attribute using dot notation.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * Set an attribute using dot notation.
     */
    public function set(string $key, mixed $value): self
    {
        Arr::set($this->attributes, $key, $value);

        return $this;
    }

    /**
     * Persist the manifest back to disk.
     */
    public function save(): void
    {
        $this->files->put(
            $this->path,
            json_encode($this->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL,
        );
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
