<?php

use App\Enums\BackupStatus;
use App\Jobs\RestoreBackupJob;
use App\Jobs\RunBackupJob;
use App\Models\Backup;
use App\Models\User;
use App\Services\Backups\BackupManager;
use App\Services\Backups\BackupRestorer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(DatabaseMigrations::class);

/**
 * Create a throwaway directory to stand in for storage/app during a test.
 */
function restoreStorageRoot(): string
{
    static $root = null;

    if ($root === null) {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-restore-'.uniqid('', true);
    }

    return $root;
}

afterEach(function () {
    File::deleteDirectory(restoreStorageRoot());
});

/**
 * Build a real backup archive from the current state.
 */
function buildRestorableBackup(?User $user = null): Backup
{
    $manager = new BackupManager(restoreStorageRoot());

    $backup = Backup::factory()->create($user !== null ? ['user_id' => $user->getKey()] : []);

    (new RunBackupJob($backup))->handle($manager);

    return $backup->refresh();
}

test('administrators can restore a backup over the application', function () {
    Storage::fake('backups');

    $root = restoreStorageRoot();
    File::ensureDirectoryExists($root.'/documents');
    File::put($root.'/documents/contract.txt', 'conteúdo original');

    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Alice Snapshot']);

    $backup = buildRestorableBackup($admin);

    expect($backup->status)->toBe(BackupStatus::Completed);

    User::factory()->create(['name' => 'Bob After']);
    File::put($root.'/documents/contract.txt', 'conteúdo alterado');
    File::put($root.'/documents/extra.txt', 'arquivo posterior');

    $backupsBefore = Backup::query()->count();

    $manager = new BackupManager($root);
    (new RestoreBackupJob($backup))->handle(new BackupRestorer($manager));

    $backup->refresh();

    expect($backup->restore_status)->toBe(BackupStatus::Completed)
        ->and($backup->restored_at)->not->toBeNull();

    expect(User::query()->where('name', 'Alice Snapshot')->exists())->toBeTrue()
        ->and(User::query()->where('name', 'Bob After')->exists())->toBeFalse();

    expect(File::get($root.'/documents/contract.txt'))->toBe('conteúdo original')
        ->and(File::exists($root.'/documents/extra.txt'))->toBeFalse();

    expect(Backup::query()->count())->toBe($backupsBefore + 1);

    expect(Schema::hasTable('backups'))->toBeTrue()
        ->and(Schema::hasTable('jobs'))->toBeTrue();
});

test('a restore creates a safety backup of the current state', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();
    $backup = buildRestorableBackup($admin);

    $manager = new BackupManager(restoreStorageRoot());
    (new RestoreBackupJob($backup))->handle(new BackupRestorer($manager));

    $safety = Backup::query()
        ->whereKeyNot($backup->getKey())
        ->firstOrFail();

    expect($safety->status)->toBe(BackupStatus::Completed)
        ->and($safety->isDownloadable())->toBeTrue();
});

test('a restore is marked as failed when the archive is missing', function () {
    Storage::fake('backups');

    $backup = Backup::factory()->completed()->create();
    Storage::disk('backups')->delete($backup->path);

    $manager = new BackupManager(restoreStorageRoot());

    try {
        (new RestoreBackupJob($backup))->handle(new BackupRestorer($manager));
    } catch (RuntimeException) {
        // The job rethrows so the queue can report the failure.
    }

    $backup->refresh();

    expect($backup->restore_status)->toBe(BackupStatus::Failed)
        ->and($backup->restore_error)->not->toBeNull();
});

test('the restore endpoint queues a job', function () {
    Storage::fake('backups');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $backup = Backup::factory()->completed()->for($admin)->create();
    Storage::disk('backups')->put($backup->path, 'conteúdo do backup');

    $this->actingAs($admin)
        ->post(route('admin.backups.restore', $backup))
        ->assertRedirect();

    Queue::assertPushed(RestoreBackupJob::class, fn (RestoreBackupJob $job) => $job->backup->is($backup));
});

test('a backup without an archive cannot be restored', function () {
    Storage::fake('backups');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $backup = Backup::factory()->for($admin)->create();

    $this->actingAs($admin)
        ->post(route('admin.backups.restore', $backup))
        ->assertNotFound();

    Queue::assertNothingPushed();
});

test('a restore is blocked while another restore is in progress', function () {
    Storage::fake('backups');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    Backup::factory()->completed()->for($admin)->create(['restore_status' => BackupStatus::Running]);

    $backup = Backup::factory()->completed()->for($admin)->create();
    Storage::disk('backups')->put($backup->path, 'conteúdo do backup');

    $this->actingAs($admin)
        ->post(route('admin.backups.restore', $backup))
        ->assertRedirect()
        ->assertSessionHasErrors('backup');

    Queue::assertNothingPushed();
});

test('a restore is blocked while a backup is in progress', function () {
    Storage::fake('backups');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    Backup::factory()->for($admin)->create();

    $backup = Backup::factory()->completed()->for($admin)->create();
    Storage::disk('backups')->put($backup->path, 'conteúdo do backup');

    $this->actingAs($admin)
        ->post(route('admin.backups.restore', $backup))
        ->assertRedirect()
        ->assertSessionHasErrors('backup');

    Queue::assertNothingPushed();
});

test('non administrators cannot restore a backup', function () {
    Storage::fake('backups');
    Queue::fake();

    $backup = Backup::factory()->completed()->create();
    Storage::disk('backups')->put($backup->path, 'conteúdo do backup');

    $this->actingAs(User::factory()->create())
        ->post(route('admin.backups.restore', $backup))
        ->assertForbidden();

    Queue::assertNothingPushed();
});
