<?php

use App\Models\StorageSetting;
use App\Models\User;
use App\Settings\StorageSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('redirects guests away from the storage settings', function () {
    $this->put('/settings/storage', ['storage_provider' => 'local'])
        ->assertRedirect('/login');
});

it('forbids regular users from updating the storage settings', function () {
    $this->actingAs(User::factory()->create())
        ->put('/settings/storage', ['storage_provider' => 'local'])
        ->assertForbidden();

    expect(StorageSetting::query()->count())->toBe(0);
});

it('shows the storage section only to administrators', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/settings')
        ->assertOk()
        ->assertSee('Provedor de armazenamento')
        ->assertSee('AWS S3')
        ->assertSee('Azure Blob')
        ->assertSee('Cloudflare R2')
        ->assertSee('Salvar armazenamento');

    $this->actingAs(User::factory()->create())
        ->get('/settings')
        ->assertOk()
        ->assertDontSee('Provedor de armazenamento');
});

it('saves the local storage provider', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/settings/storage', ['storage_provider' => 'local'])
        ->assertRedirect(route('settings').'#armazenamento')
        ->assertSessionHas('status', 'Configurações de armazenamento salvas com sucesso.');

    expect(StorageSettings::provider())->toBe(StorageSetting::PROVIDER_LOCAL)
        ->and(StorageSettings::diskName())->toBe(StorageSettings::DISK_LOCAL);
});

it('saves the aws configuration and encrypts the secret', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put('/settings/storage', [
        'storage_provider' => 'aws',
        'aws_key' => 'AKIAEXAMPLE',
        'aws_secret' => 'super-secret',
        'aws_region' => 'sa-east-1',
        'aws_bucket' => 'documentation-center',
    ])->assertRedirect(route('settings').'#armazenamento');

    $settings = StorageSetting::query()->firstOrFail();
    $raw = DB::table('storage_settings')->first();

    expect($settings->storage_provider)->toBe(StorageSetting::PROVIDER_AWS)
        ->and($settings->aws_key)->toBe('AKIAEXAMPLE')
        ->and($settings->aws_secret)->toBe('super-secret')
        ->and($raw->aws_secret)->not->toBe('super-secret')
        ->and(StorageSettings::diskName())->toBe(StorageSettings::DISK_AWS);

    StorageSettings::configure();

    expect(config('filesystems.disks.'.StorageSettings::DISK_AWS))
        ->toMatchArray([
            'driver' => 's3',
            'bucket' => 'documentation-center',
            'region' => 'sa-east-1',
            'root' => 'plugins',
        ]);
});

it('configures the azure blob disk', function () {
    StorageSetting::query()->create([
        'storage_provider' => 'azure',
        'azure_account_name' => 'documentation',
        'azure_account_key' => 'azure-key',
        'azure_container' => 'plugins',
        'azure_endpoint_suffix' => 'core.windows.net',
    ]);

    expect(StorageSettings::diskName())->toBe(StorageSettings::DISK_AZURE);

    StorageSettings::configure();

    expect(config('filesystems.disks.'.StorageSettings::DISK_AZURE))
        ->toMatchArray([
            'driver' => 'azure-storage-blob',
            'account_name' => 'documentation',
            'container' => 'plugins',
            'root' => 'plugins',
        ]);
});

it('configures the cloudflare r2 disk', function () {
    StorageSetting::query()->create([
        'storage_provider' => 'cloudflare',
        'cloudflare_account_id' => 'account-id',
        'cloudflare_access_key_id' => 'access-key',
        'cloudflare_secret_access_key' => 'secret-key',
        'cloudflare_bucket' => 'plugins',
    ]);

    expect(StorageSettings::diskName())->toBe(StorageSettings::DISK_CLOUDFLARE);

    StorageSettings::configure();

    expect(config('filesystems.disks.'.StorageSettings::DISK_CLOUDFLARE))
        ->toMatchArray([
            'driver' => 's3',
            'bucket' => 'plugins',
            'endpoint' => 'https://account-id.r2.cloudflarestorage.com',
            'use_path_style_endpoint' => true,
            'root' => 'plugins',
        ]);
});

it('keeps the stored secret when the field is left blank', function () {
    $admin = User::factory()->admin()->create();

    StorageSetting::query()->create([
        'storage_provider' => 'aws',
        'aws_key' => 'AKIAEXAMPLE',
        'aws_secret' => 'super-secret',
        'aws_region' => 'us-east-1',
        'aws_bucket' => 'bucket',
    ]);

    $this->actingAs($admin)->put('/settings/storage', [
        'storage_provider' => 'aws',
        'aws_key' => 'AKIAEXAMPLE',
        'aws_secret' => '',
        'aws_region' => 'us-east-1',
        'aws_bucket' => 'bucket',
    ])->assertRedirect(route('settings').'#armazenamento');

    expect(StorageSetting::query()->firstOrFail()->aws_secret)->toBe('super-secret');
});

it('validates the provider and the required credentials', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put('/settings/storage', ['storage_provider' => 'invalid'])
        ->assertSessionHasErrors('storage_provider');

    $this->actingAs($admin)
        ->put('/settings/storage', ['storage_provider' => 'aws'])
        ->assertSessionHasErrors(['aws_key', 'aws_secret', 'aws_region', 'aws_bucket']);

    $this->actingAs($admin)
        ->put('/settings/storage', ['storage_provider' => 'azure'])
        ->assertSessionHasErrors(['azure_account_name', 'azure_account_key', 'azure_container']);

    $this->actingAs($admin)
        ->put('/settings/storage', ['storage_provider' => 'cloudflare'])
        ->assertSessionHasErrors([
            'cloudflare_account_id',
            'cloudflare_access_key_id',
            'cloudflare_secret_access_key',
            'cloudflare_bucket',
        ]);
});

it('falls back to local storage when the selected provider is incomplete', function () {
    StorageSetting::query()->create([
        'storage_provider' => 'aws',
        'aws_key' => 'AKIAEXAMPLE',
    ]);

    expect(StorageSettings::diskName())->toBe(StorageSettings::DISK_LOCAL)
        ->and(StorageSettings::configured(StorageSetting::PROVIDER_AWS))->toBeFalse();
});

it('uses the configured cloud disk for plugin storage', function () {
    Storage::fake(StorageSettings::DISK_AWS);

    StorageSetting::query()->create([
        'storage_provider' => 'aws',
        'aws_key' => 'AKIAEXAMPLE',
        'aws_secret' => 'super-secret',
        'aws_region' => 'us-east-1',
        'aws_bucket' => 'bucket',
    ]);

    $storage = plugin_storage('example');

    expect($storage->diskName())->toBe(StorageSettings::DISK_AWS);

    $path = $storage->put('documents/readme.txt', 'conteúdo');

    Storage::disk(StorageSettings::DISK_AWS)->assertExists('example/'.$path);
});
