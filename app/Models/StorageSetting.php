<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'storage_provider',
    'aws_key',
    'aws_secret',
    'aws_region',
    'aws_bucket',
    'aws_endpoint',
    'aws_url',
    'aws_use_path_style',
    'azure_account_name',
    'azure_account_key',
    'azure_container',
    'azure_endpoint_suffix',
    'azure_url',
    'cloudflare_account_id',
    'cloudflare_access_key_id',
    'cloudflare_secret_access_key',
    'cloudflare_bucket',
    'cloudflare_public_url',
])]
#[Hidden([
    'aws_key',
    'aws_secret',
    'aws_bucket',
    'aws_endpoint',
    'aws_url',
    'azure_account_name',
    'azure_account_key',
    'azure_container',
    'azure_url',
    'cloudflare_account_id',
    'cloudflare_access_key_id',
    'cloudflare_secret_access_key',
    'cloudflare_bucket',
    'cloudflare_public_url',
])]
class StorageSetting extends Model
{
    public const PROVIDER_LOCAL = 'local';

    public const PROVIDER_AWS = 'aws';

    public const PROVIDER_AZURE = 'azure';

    public const PROVIDER_CLOUDFLARE = 'cloudflare';

    /**
     * The storage providers supported by the system.
     *
     * @return list<string>
     */
    public static function providers(): array
    {
        return [
            self::PROVIDER_LOCAL,
            self::PROVIDER_AWS,
            self::PROVIDER_AZURE,
            self::PROVIDER_CLOUDFLARE,
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'storage_provider' => 'encrypted',
            'aws_key' => 'encrypted',
            'aws_secret' => 'encrypted',
            'aws_region' => 'encrypted',
            'aws_bucket' => 'encrypted',
            'aws_endpoint' => 'encrypted',
            'aws_url' => 'encrypted',
            'aws_use_path_style' => 'boolean',
            'azure_account_name' => 'encrypted',
            'azure_account_key' => 'encrypted',
            'azure_container' => 'encrypted',
            'azure_endpoint_suffix' => 'encrypted',
            'azure_url' => 'encrypted',
            'cloudflare_account_id' => 'encrypted',
            'cloudflare_access_key_id' => 'encrypted',
            'cloudflare_secret_access_key' => 'encrypted',
            'cloudflare_bucket' => 'encrypted',
            'cloudflare_public_url' => 'encrypted',
        ];
    }
}
