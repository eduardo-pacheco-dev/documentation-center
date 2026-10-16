<?php

use App\Enums\BackupStatus;
use App\Jobs\RunBackupJob;
use App\Models\Backup;
use App\Models\User;
use App\Services\Backups\BackupManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get('/admin/backups')->assertRedirect('/login');
});

test('non administrators cannot open the backups area', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/backups')
        ->assertForbidden();
});

test('administrators can open the backups area', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/backups')
        ->assertOk()
        ->assertSee('Backups');
});

test('the backups area renders finished backups with their status and download action', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();
    $backup = Backup::factory()->completed()->for($admin)->create();

    Storage::disk('backups')->put($backup->path, 'conteúdo do backup');

    $this->actingAs($admin)
        ->get('/admin/backups')
        ->assertOk()
        ->assertSee('Concluído')
        ->assertSee('Baixar');
});

test('requesting a backup queues a job and records it as pending', function () {
    Queue::fake();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/backups')
        ->assertRedirect();

    $backup = Backup::query()->firstOrFail();

    expect($backup->status)->toBe(BackupStatus::Pending)
        ->and($backup->user_id)->toBe($admin->getKey());

    Queue::assertPushed(RunBackupJob::class, fn (RunBackupJob $job) => $job->backup->is($backup));
});

test('the backup job builds an archive with the database and marks it completed', function () {
    Storage::fake('backups');

    $backup = Backup::factory()->create();

    (new RunBackupJob($backup))->handle(app(BackupManager::class));

    $backup->refresh();

    expect($backup->status)->toBe(BackupStatus::Completed)
        ->and($backup->path)->not->toBeNull();

    Storage::disk('backups')->assertExists($backup->path);
    expect(Storage::disk('backups')->size($backup->path))->toBeGreaterThan(0);
});

test('a failing backup is marked as failed and stores the reason', function () {
    Storage::fake('backups');

    $backup = Backup::factory()->create();

    $manager = Mockery::mock(BackupManager::class);
    $manager->shouldReceive('build')->once()->andThrow(new RuntimeException('Falha simulada.'));

    try {
        (new RunBackupJob($backup))->handle($manager);
    } catch (RuntimeException) {
        // The job rethrows so the queue can report the failure.
    }

    $backup->refresh();

    expect($backup->status)->toBe(BackupStatus::Failed)
        ->and($backup->error)->toContain('Falha simulada.');
});

test('administrators can download a completed backup', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();
    $backup = Backup::factory()->completed()->for($admin)->create();

    Storage::disk('backups')->put($backup->path, 'conteúdo do backup');

    $this->actingAs($admin)
        ->get(route('admin.backups.download', $backup))
        ->assertOk()
        ->assertDownload($backup->filename());
});

test('administrators can delete a backup and its archive', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();
    $backup = Backup::factory()->completed()->for($admin)->create();

    Storage::disk('backups')->put($backup->path, 'conteúdo do backup');

    $this->actingAs($admin)
        ->delete(route('admin.backups.destroy', $backup))
        ->assertRedirect();

    $this->assertDatabaseMissing('backups', ['id' => $backup->getKey()]);
    Storage::disk('backups')->assertMissing($backup->path);
});

test('the status endpoint reports the backup states as json', function () {
    $admin = User::factory()->admin()->create();
    Backup::factory()->completed()->for($admin)->create();

    $this->actingAs($admin)
        ->getJson(route('admin.backups.status'))
        ->assertOk()
        ->assertJsonFragment(['status' => 'completed']);
});

test('the generated database dump can be restored into a fresh database', function () {
    Storage::fake('backups');

    User::factory()->admin()->create(['name' => 'Alice Backupped']);

    $backup = Backup::factory()->create();

    (new RunBackupJob($backup))->handle(app(BackupManager::class));
    $backup->refresh();

    $zip = new ZipArchive;
    $zip->open(Storage::disk('backups')->path($backup->path));

    $sql = $zip->getFromName('database/database.sql');

    $zip->close();

    expect($sql)->not->toBeFalse();

    $target = tempnam(sys_get_temp_dir(), 'restore-').'.sqlite';

    $database = new PDO('sqlite:'.$target);

    try {
        $database->exec($sql);

        $count = $database->query("SELECT COUNT(*) FROM users WHERE name = 'Alice Backupped'")->fetchColumn();

        expect((int) $count)->toBe(1);
    } finally {
        $database = null;
        @unlink($target);
    }
});
