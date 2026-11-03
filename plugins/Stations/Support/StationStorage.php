<?php

namespace Plugins\Stations\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Plugins\Stations\Models\StationStorageSetting;

final class StationStorage
{
    public const DISK_LOCAL = 'local';

    public const DISK_AWS = 'stations_aws';

    public const DISK_AZURE = 'stations_azure';

    public const DISK_CLOUDFLARE = 'stations_cloudflare';

    /**
     * The disk name of the configured storage provider.
     */
    public function name(): string
    {
        return $this->diskName(StationStorageSetting::current());
    }

    /**
     * Resolve the configured disk or the given stored disk.
     */
    public function disk(?string $name = null): Filesystem
    {
        $settings = StationStorageSetting::current();

        $this->configureDisks($settings);

        return Storage::disk($name ?: $this->diskName($settings));
    }

    private function diskName(StationStorageSetting $settings): string
    {
        return match ($settings->storage_provider) {
            StationStorageSetting::PROVIDER_AWS => self::DISK_AWS,
            StationStorageSetting::PROVIDER_AZURE => self::DISK_AZURE,
            StationStorageSetting::PROVIDER_CLOUDFLARE => self::DISK_CLOUDFLARE,
            default => self::DISK_LOCAL,
        };
    }

    /**
     * Register every configured provider so older attachments remain reachable.
     */
    private function configureDisks(StationStorageSetting $settings): void
    {
        $this->configureAws($settings);
        $this->configureAzure($settings);
        $this->configureCloudflare($settings);
    }

    private function configureAws(StationStorageSetting $settings): void
    {
        if (blank($settings->aws_key) || blank($settings->aws_secret) || blank($settings->aws_bucket)) {
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
                'use_path_style_endpoint' => $settings->aws_use_path_style,
                'visibility' => 'private',
                'throw' => true,
            ],
        ]);
    }

    private function configureAzure(StationStorageSetting $settings): void
    {
        if (blank($settings->azure_account_name) || blank($settings->azure_account_key) || blank($settings->azure_container)) {
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
                'is_public_container' => false,
            ],
        ]);
    }

    private function configureCloudflare(StationStorageSetting $settings): void
    {
        if (
            blank($settings->cloudflare_account_id)
            || blank($settings->cloudflare_access_key_id)
            || blank($settings->cloudflare_secret_access_key)
            || blank($settings->cloudflare_bucket)
        ) {
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
                'visibility' => 'private',
                'throw' => true,
            ],
        ]);
    }
}
