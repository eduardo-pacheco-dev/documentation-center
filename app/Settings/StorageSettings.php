<?php

namespace App\Settings;

use App\Models\StorageSetting;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Resolves the storage provider used by the plugins and configures its disk.
 */
final class StorageSettings
{
    public const DISK_LOCAL = 'plugins';

    public const DISK_AWS = 'plugin_aws';

    public const DISK_AZURE = 'plugin_azure';

    public const DISK_CLOUDFLARE = 'plugin_cloudflare';

    /**
     * The current settings, falling back to the defaults without persisting.
     */
    public static function current(): StorageSetting
    {
        try {
            if (Schema::hasTable((new StorageSetting)->getTable())) {
                $settings = StorageSetting::query()->first();

                if ($settings !== null) {
                    return $settings;
                }
            }
        } catch (Throwable) {
            // The settings are unavailable; use safe defaults for this request.
        }

        return new StorageSetting([
            'storage_provider' => StorageSetting::PROVIDER_LOCAL,
            'aws_region' => 'us-east-1',
            'aws_use_path_style' => false,
            'azure_endpoint_suffix' => 'core.windows.net',
        ]);
    }

    /**
     * Persist the storage settings.
     *
     * @param  array<string, mixed>  $data
     */
    public static function save(array $data): StorageSetting
    {
        $settings = StorageSetting::query()->firstOrNew([]);

        $settings->fill($data);
        $settings->save();

        return $settings;
    }

    /**
     * The provider selected by an administrator.
     */
    public static function provider(): string
    {
        $provider = self::current()->storage_provider;

        return in_array($provider, StorageSetting::providers(), true)
            ? $provider
            : StorageSetting::PROVIDER_LOCAL;
    }

    /**
     * The disk used for new plugin files, falling back to local when the
     * selected provider is not fully configured.
     */
    public static function diskName(): string
    {
        $settings = self::current();

        return match (self::providerFor($settings)) {
            StorageSetting::PROVIDER_AWS => self::configured(StorageSetting::PROVIDER_AWS, $settings)
                ? self::DISK_AWS
                : self::localDiskName(),
            StorageSetting::PROVIDER_AZURE => self::configured(StorageSetting::PROVIDER_AZURE, $settings)
                ? self::DISK_AZURE
                : self::localDiskName(),
            StorageSetting::PROVIDER_CLOUDFLARE => self::configured(StorageSetting::PROVIDER_CLOUDFLARE, $settings)
                ? self::DISK_CLOUDFLARE
                : self::localDiskName(),
            default => self::localDiskName(),
        };
    }

    /**
     * Resolve a disk, registering the configured cloud providers first.
     */
    public static function disk(?string $name = null): Filesystem
    {
        self::configure();

        return Storage::disk($name ?? self::diskName());
    }

    /**
     * Register every fully configured provider so existing files stay
     * reachable after an administrator changes the active provider.
     */
    public static function configure(?StorageSetting $settings = null): void
    {
        $settings ??= self::current();

        self::configureAws($settings);
        self::configureAzure($settings);
        self::configureCloudflare($settings);
    }

    /**
     * Whether a provider has enough credentials to be used.
     */
    public static function configured(string $provider, ?StorageSetting $settings = null): bool
    {
        $settings ??= self::current();

        return match ($provider) {
            StorageSetting::PROVIDER_LOCAL => true,
            StorageSetting::PROVIDER_AWS => filled($settings->aws_key)
                && filled($settings->aws_secret)
                && filled($settings->aws_bucket),
            StorageSetting::PROVIDER_AZURE => filled($settings->azure_account_name)
                && filled($settings->azure_account_key)
                && filled($settings->azure_container),
            StorageSetting::PROVIDER_CLOUDFLARE => filled($settings->cloudflare_account_id)
                && filled($settings->cloudflare_access_key_id)
                && filled($settings->cloudflare_secret_access_key)
                && filled($settings->cloudflare_bucket),
            default => false,
        };
    }

    /**
     * The prefix used for plugin directories inside cloud buckets.
     */
    public static function prefix(): string
    {
        return trim((string) config('plugins.storage.prefix', 'plugins'), '/');
    }

    private static function providerFor(StorageSetting $settings): string
    {
        return in_array($settings->storage_provider, StorageSetting::providers(), true)
            ? $settings->storage_provider
            : StorageSetting::PROVIDER_LOCAL;
    }

    private static function localDiskName(): string
    {
        return (string) config('plugins.storage.disk', self::DISK_LOCAL);
    }

    private static function configureAws(StorageSetting $settings): void
    {
        if (! self::configured(StorageSetting::PROVIDER_AWS, $settings)) {
            return;
        }

        config([
            'filesystems.disks.'.self::DISK_AWS => [
                'driver' => 's3',
                'key' => $settings->aws_key,
                'secret' => $settings->aws_secret,
                'region' => $settings->aws_region ?: 'us-east-1',
                'bucket' => $settings->aws_bucket,
                'endpoint' => $settings->aws_endpoint ?: null,
                'url' => $settings->aws_url ?: null,
                'use_path_style_endpoint' => (bool) $settings->aws_use_path_style,
                'root' => self::prefix(),
                'visibility' => 'private',
                'throw' => true,
            ],
        ]);
    }

    private static function configureAzure(StorageSetting $settings): void
    {
        if (! self::configured(StorageSetting::PROVIDER_AZURE, $settings)) {
            return;
        }

        config([
            'filesystems.disks.'.self::DISK_AZURE => [
                'driver' => 'azure-storage-blob',
                'credential' => 'shared_key',
                'account_name' => $settings->azure_account_name,
                'account_key' => $settings->azure_account_key,
                'container' => $settings->azure_container,
                'endpoint_suffix' => $settings->azure_endpoint_suffix ?: 'core.windows.net',
                'url' => $settings->azure_url ?: null,
                'root' => self::prefix(),
                'is_public_container' => false,
            ],
        ]);
    }

    private static function configureCloudflare(StorageSetting $settings): void
    {
        if (! self::configured(StorageSetting::PROVIDER_CLOUDFLARE, $settings)) {
            return;
        }

        config([
            'filesystems.disks.'.self::DISK_CLOUDFLARE => [
                'driver' => 's3',
                'key' => $settings->cloudflare_access_key_id,
                'secret' => $settings->cloudflare_secret_access_key,
                'region' => 'auto',
                'bucket' => $settings->cloudflare_bucket,
                'endpoint' => 'https://'.$settings->cloudflare_account_id.'.r2.cloudflarestorage.com',
                'url' => $settings->cloudflare_public_url ?: null,
                'use_path_style_endpoint' => true,
                'root' => self::prefix(),
                'visibility' => 'private',
                'throw' => true,
            ],
        ]);
    }
}
