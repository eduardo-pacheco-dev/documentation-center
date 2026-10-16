<?php

use App\Enums\BackupStatus;
use App\Jobs\RestoreBackupJob;
use App\Jobs\RunBackupJob;
use App\Models\Backup;
use App\Models\User;
use App\Services\Backups\BackupManager;
use App\Services\Backups\BackupRestorer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| MariaDB Integration
|--------------------------------------------------------------------------
|
| These tests exercise the real MySQL/MariaDB backup and restore path. They
| are skipped unless a MariaDB server is configured through the environment,
| so the default SQLite test suite stays self contained.
|
*/

beforeEach(function () {
    $host = getenv('MARIADB_TEST_HOST');

    if ($host === false || $host === '') {
        $this->markTestSkipped(
            'Defina MARIADB_TEST_HOST (e demais variáveis MARIADB_TEST_*) para executar os testes de integração com MariaDB.'
        );
    }

    config()->set('database.connections.mariadb_test', [
        'driver' => 'mariadb',
        'host' => $host,
        'port' => getenv('MARIADB_TEST_PORT') ?: '3306',
        'database' => getenv('MARIADB_TEST_DATABASE') ?: 'backup_test',
        'username' => getenv('MARIADB_TEST_USERNAME') ?: 'root',
        'password' => getenv('MARIADB_TEST_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ]);

    config()->set('database.default', 'mariadb_test');

    DB::purge('mariadb_test');

    Artisan::call('migrate:fresh', ['--database' => 'mariadb_test', '--force' => true]);

    Storage::fake('backups');
});

afterEach(function () {
    DB::purge('mariadb_test');

    config()->set('database.default', env('DB_CONNECTION', 'sqlite'));

    File::deleteDirectory(mariadbStorageRoot());
});

/**
 * Create a throwaway directory to stand in for storage/app during a test.
 */
function mariadbStorageRoot(): string
{
    static $root = null;

    if ($root === null) {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-mariadb-'.uniqid('', true);
    }

    return $root;
}

test('a backup can be created and restored against mariadb', function () {
    $root = mariadbStorageRoot();
    File::ensureDirectoryExists($root.'/documents');
    File::put($root.'/documents/contract.txt', 'conteúdo original');

    $admin = User::factory()->admin()->create();

    $tricky = "Alice; Snapshot\nlinha 2 \\ teste";
    User::factory()->create(['name' => $tricky]);

    $backup = Backup::factory()->create(['user_id' => $admin->getKey()]);

    (new RunBackupJob($backup))->handle(new BackupManager($root));
    $backup->refresh();

    expect($backup->status)->toBe(BackupStatus::Completed);

    $zip = new ZipArchive;
    $zip->open(Storage::disk('backups')->path($backup->path));
    $sql = $zip->getFromName('database/database.sql');
    $zip->close();

    expect($sql)->not->toBeFalse()
        ->and($sql)->toContain('CREATE TABLE `users`')
        ->and($sql)->toContain('INSERT INTO `users`');

    User::factory()->create(['name' => 'Bob After']);
    File::put($root.'/documents/contract.txt', 'conteúdo alterado');
    File::put($root.'/documents/extra.txt', 'arquivo posterior');

    $backupsBefore = Backup::query()->count();

    (new RestoreBackupJob($backup))->handle(new BackupRestorer(new BackupManager($root)));

    $backup->refresh();

    expect($backup->restore_status)->toBe(BackupStatus::Completed)
        ->and($backup->restored_at)->not->toBeNull()
        ->and(User::query()->where('name', $tricky)->exists())->toBeTrue()
        ->and(User::query()->where('name', 'Bob After')->exists())->toBeFalse()
        ->and(File::get($root.'/documents/contract.txt'))->toBe('conteúdo original')
        ->and(File::exists($root.'/documents/extra.txt'))->toBeFalse()
        ->and(Backup::query()->count())->toBe($backupsBefore + 1)
        ->and(Schema::hasTable('backups'))->toBeTrue()
        ->and(Schema::hasTable('jobs'))->toBeTrue();
});
