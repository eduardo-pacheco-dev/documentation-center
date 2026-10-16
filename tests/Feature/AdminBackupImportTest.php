<?php

use App\Enums\BackupStatus;
use App\Jobs\RestoreBackupJob;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Build a real backup archive holding the given database dump.
 */
function makeImportableBackup(string $sql = 'CREATE TABLE demo (id INTEGER);', array $extra = []): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'backup-import-').'.zip';

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('database/database.sql', $sql);
    $zip->addFromString('manifest.json', json_encode(['application' => 'Documentation Center']));

    foreach ($extra as $name => $contents) {
        $zip->addFromString($name, $contents);
    }

    $zip->close();

    return new UploadedFile($path, 'backup.zip', 'application/zip', null, true);
}

test('guests are redirected to the login page', function () {
    $this->post('/admin/backups/import')->assertRedirect('/login');
});

test('non administrators cannot import a backup', function () {
    Storage::fake('backups');

    $this->actingAs(User::factory()->create())
        ->post('/admin/backups/import', ['file' => makeImportableBackup()])
        ->assertForbidden();

    $this->assertDatabaseCount('backups', 0);
});

test('administrators can import a backup archive', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => makeImportableBackup()])
        ->assertRedirect()
        ->assertSessionHas('status');

    $backup = Backup::query()->firstOrFail();

    expect($backup->status)->toBe(BackupStatus::Completed)
        ->and($backup->user_id)->toBe($admin->getKey())
        ->and($backup->path)->not->toBeNull()
        ->and($backup->size)->toBeGreaterThan(0)
        ->and($backup->isDownloadable())->toBeTrue()
        ->and($backup->isRestorable())->toBeTrue();

    Storage::disk('backups')->assertExists($backup->path);
});

test('an imported backup archive is stored with the standard naming', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => makeImportableBackup()])
        ->assertRedirect();

    $backup = Backup::query()->firstOrFail();

    expect($backup->filename())->toMatch('/^backup-'.$backup->getKey().'-\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.zip$/');
});

test('the imported backup appears in the backups listing', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => makeImportableBackup()])
        ->assertRedirect();

    $this->actingAs($admin)
        ->get('/admin/backups')
        ->assertOk()
        ->assertSee('Concluído')
        ->assertSee('Baixar');
});

test('an imported backup can be restored over the application', function () {
    Storage::fake('backups');
    Queue::fake();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => makeImportableBackup()])
        ->assertRedirect();

    $backup = Backup::query()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.backups.restore', $backup))
        ->assertRedirect();

    Queue::assertPushed(RestoreBackupJob::class, fn (RestoreBackupJob $job) => $job->backup->is($backup));
});

test('an uploaded file that is not a backup archive is rejected', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    $file = UploadedFile::fake()->create('planilha.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => $file])
        ->assertRedirect()
        ->assertSessionHasErrors('file');

    $this->assertDatabaseCount('backups', 0);
});

test('a zip archive without the database dump is rejected', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    $path = tempnam(sys_get_temp_dir(), 'backup-invalid-').'.zip';

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('manifest.json', '{}');
    $zip->close();

    $file = new UploadedFile($path, 'backup.zip', 'application/zip', null, true);

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => $file])
        ->assertRedirect()
        ->assertSessionHasErrors('file');

    $this->assertDatabaseCount('backups', 0);
    Storage::disk('backups')->assertDirectoryEmpty('');
});

test('a missing file is rejected', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/backups/import', [])
        ->assertRedirect()
        ->assertSessionHasErrors('file');

    $this->assertDatabaseCount('backups', 0);
});

test('importing is blocked while a backup is in progress', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    Backup::factory()->for($admin)->create();

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => makeImportableBackup()])
        ->assertRedirect()
        ->assertSessionHasErrors('import');

    $this->assertDatabaseCount('backups', 1);
});

test('importing is blocked while a restore is in progress', function () {
    Storage::fake('backups');

    $admin = User::factory()->admin()->create();

    Backup::factory()->completed()->for($admin)->create(['restore_status' => BackupStatus::Running]);

    $this->actingAs($admin)
        ->post('/admin/backups/import', ['file' => makeImportableBackup()])
        ->assertRedirect()
        ->assertSessionHasErrors('import');

    $this->assertDatabaseCount('backups', 1);
});
