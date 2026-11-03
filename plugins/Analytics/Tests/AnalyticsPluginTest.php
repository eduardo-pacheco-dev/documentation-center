<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Analytics\Enums\AnalyticsImportStatus;
use Plugins\Analytics\Exceptions\AnalyticsImportCanceledException;
use Plugins\Analytics\Jobs\ProcessAnalyticsImportJob;
use Plugins\Analytics\Models\AnalyticsAflRecord;
use Plugins\Analytics\Models\AnalyticsImport;
use Plugins\Analytics\Models\AnalyticsRecord;
use Plugins\Analytics\Models\AnalyticsSnapshot;
use Plugins\Analytics\Services\AnalyticsAflImporter;
use Plugins\Analytics\Services\AnalyticsRecordImporter;
use Plugins\Analytics\Support\AnalyticsImportProgress;
use Plugins\Files\Models\Document;
use Plugins\Projects\Models\Project;

uses(RefreshDatabase::class);

it('requires authentication to access analytics', function () {
    $this->get('/admin/analytics')->assertRedirect('/login');
});

it('shows analytics alongside the other modules in the sidebar', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="analytics"', false);
});

it('renders the dashboard with charts and indicators', function () {
    $user = User::factory()->create();
    Project::factory()->count(2)->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('Criações no período')
        ->assertSee('Ordens por status')
        ->assertSee('Tarefas por lista')
        ->assertSee('Formulários por status')
        ->assertSee('analytics-series', false)
        ->assertSee('apexcharts', false)
        ->assertSee('chart.js', false)
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['total'] === 2);
});

it('scopes the indicators to the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Project::factory()->count(2)->create(['user_id' => $user->getKey()]);
    Project::factory()->count(5)->create(['user_id' => $other->getKey()]);

    Document::factory()->count(3)->create(['user_id' => $user->getKey()]);
    Document::factory()->count(7)->create(['user_id' => $other->getKey()]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['total'] === 2
            && $summary['documents']['total'] === 3);
});

it('lets administrators switch to the global scope', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    Project::factory()->create(['user_id' => $admin->getKey()]);
    Project::factory()->count(3)->create(['user_id' => $other->getKey()]);

    $this->actingAs($admin)
        ->get('/admin/analytics?scope=global')
        ->assertOk()
        ->assertViewHas('isGlobal', true)
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['total'] === 4);
});

it('forbids regular users from using the global scope', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/analytics?scope=global')
        ->assertForbidden();
});

it('applies the selected period to the indicators', function () {
    $user = User::factory()->create();

    Project::factory()->create([
        'user_id' => $user->getKey(),
        'created_at' => now()->subDays(40),
    ]);

    Project::factory()->create([
        'user_id' => $user->getKey(),
        'created_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get('/admin/analytics?period=7')
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['created'] === 1);
});

it('validates custom period dates', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/analytics?from=2026-02-30&to=2026-03-01')
        ->assertSessionHasErrors('from');
});

it('exports the indicators as csv', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)
        ->get('/admin/analytics/export?period=30')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)->toContain('Indicador,Valor')
        ->toContain('projects.total,1');
});

it('stores a snapshot for the authenticated user', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post('/admin/analytics/snapshot', ['period' => 30])
        ->assertRedirect();

    $snapshot = AnalyticsSnapshot::query()->where('user_id', $user->getKey())->firstOrFail();

    expect($snapshot->metrics['projects']['total'])->toBe(1)
        ->and($snapshot->captured_on)->toBe(now()->toDateString());
});

it('stores global and per-user snapshots through the artisan command', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $this->artisan('analytics:snapshot')->assertSuccessful();

    expect(AnalyticsSnapshot::query()->whereNull('user_id')->exists())->toBeTrue()
        ->and(AnalyticsSnapshot::query()->where('user_id', $user->getKey())->exists())->toBeTrue();
});

it('refreshes the snapshot of the day instead of duplicating it', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $this->artisan('analytics:snapshot', ['--user' => $user->getKey()])->assertSuccessful();
    $this->artisan('analytics:snapshot', ['--user' => $user->getKey()])->assertSuccessful();

    expect(AnalyticsSnapshot::query()->where('user_id', $user->getKey())->count())->toBe(1);
});

it('requires a valid user for the snapshot command', function () {
    $this->artisan('analytics:snapshot', ['--user' => 999999])
        ->expectsOutputToContain('was not found')
        ->assertFailed();
});

it('imports oc and os records from a spreadsheet', function () {
    $user = User::factory()->create();

    $path = analyticsRecordSheet([
        ['364007', '417', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Obra Civil, Infra Cabeamento, Quadro de Energia e Energização', 'ENODEB', 'R. João Pessoa, 116-190, Aimorés - MG, 35200-000, Brazil', '', 'MG', '7NLAMGV28', 'MGAOR_0001', 'Max da Silva Costa', 'Aceito com Ressalvas'],
        ['362293', '8171', '26/10/2017', 'NOKIA', '', 'Acesso FAM', 'Instalação', 'ENODEB', 'R. Pres. Costa e Silva, 2-50, Nova Viçosa - BA, 45920-000, Brazil', 'Nova Vicosa', 'BA', '7NLNVIT01', 'BANVV_0001', '', 'Criado pelo NetFlow'],
    ]);

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', [
            'file' => new UploadedFile($path, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $record = AnalyticsRecord::query()
        ->where('oc', '364007')
        ->firstOrFail();

    expect($record->user_id)->toBe($user->getKey())
        ->and($record->os)->toBe('417')
        ->and($record->created_on->toDateString())->toBe('2017-08-16')
        ->and($record->supplier)->toBe('NOKIA')
        ->and($record->process_stage)->toBe('Obra Civil, Infra Cabeamento, Quadro de Energia e Energização')
        ->and($record->element_type)->toBe('ENODEB')
        ->and($record->state)->toBe('MG')
        ->and($record->node_name)->toBe('7NLAMGV28')
        ->and($record->location)->toBe('MGAOR_0001')
        ->and($record->operator)->toBe('Max da Silva Costa')
        ->and($record->status)->toBe('Aceito com Ressalvas')
        ->and(AnalyticsRecord::query()->count())->toBe(2);

    @unlink($path);
});

it('queues spreadsheet imports and exposes their progress', function () {
    Queue::fake();
    Storage::fake('local');

    $user = User::factory()->create();

    $path = analyticsRecordSheet([['364007', '417', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Obra Civil', 'ENODEB', 'Rua A', 'Aimorés', 'MG', '7NLAMGV28', 'MGAOR_0001', '', 'Criado pelo NetFlow']]);

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', [
            'file' => new UploadedFile($path, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Importação FAMs enfileirada. O andamento aparece em “Importações recentes”.')
        ->assertSessionHas('import_started', true);

    $import = AnalyticsImport::query()->sole();

    expect($import->status->value)->toBe('pending')
        ->and($import->original_name)->toBe('oc-os.xlsx')
        ->and($import->created_count)->toBe(0)
        ->and(AnalyticsRecord::query()->count())->toBe(0);

    Storage::disk('local')->assertExists($import->path);

    Queue::assertPushed(ProcessAnalyticsImportJob::class, fn (ProcessAnalyticsImportJob $job): bool => $job->import->is($import));

    Storage::disk('local')->delete($import->path);

    $import->markProcessing(5);

    AnalyticsImportProgress::put($import->getKey(), [
        'processed' => 2,
        'total' => 5,
        'created' => 1,
        'updated' => 1,
        'unchanged' => 0,
        'skipped' => 0,
    ]);

    $this->actingAs($user)
        ->get('/admin/analytics/imports/status')
        ->assertOk()
        ->assertJson(['pending' => true])
        ->assertJsonStructure(['html', 'toast_html', 'pending', 'imports'])
        ->assertJsonFragment(['original_name' => 'oc-os.xlsx'])
        ->assertJsonPath('imports.0.processed_rows', 2)
        ->assertJsonPath('imports.0.percentage', 40)
        ->assertSee('2 de 5 linhas processadas');

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('Importando FAMs')
        ->assertSee('2 de 5 linhas processadas')
        ->assertSee('data-import-toast', false)
        ->assertSee('data-import-cancel', false)
        ->assertSee('data-import-file-preview', false)
        ->assertSee('data-import-file-clear', false);

    AnalyticsImportProgress::forget($import->getKey());

    @unlink($path);
});

it('cancels a queued import', function () {
    $user = User::factory()->create();

    $import = AnalyticsImport::query()->create([
        'user_id' => $user->getKey(),
        'type' => AnalyticsImport::TYPE_FAMS,
        'status' => AnalyticsImportStatus::Pending,
        'original_name' => 'oc-os.xlsx',
        'path' => 'analytics-imports/queued.xlsx',
    ]);

    AnalyticsImportProgress::put($import->getKey(), [
        'processed' => 0,
        'total' => 10,
        'created' => 0,
        'updated' => 0,
        'unchanged' => 0,
        'skipped' => 0,
    ]);

    $this->actingAs($user)
        ->post("/admin/analytics/imports/{$import->getKey()}/cancel")
        ->assertRedirect()
        ->assertSessionHas('status', 'Importação cancelada.');

    expect($import->fresh()->status)->toBe(AnalyticsImportStatus::Canceled)
        ->and($import->fresh()->isFinished())->toBeTrue()
        ->and(AnalyticsImportProgress::get($import->getKey()))->toBeNull();
});

it('does not cancel an import that already finished', function () {
    $user = User::factory()->create();

    $import = AnalyticsImport::query()->create([
        'user_id' => $user->getKey(),
        'type' => AnalyticsImport::TYPE_FAMS,
        'status' => AnalyticsImportStatus::Completed,
        'original_name' => 'oc-os.xlsx',
        'path' => 'analytics-imports/done.xlsx',
    ]);

    $this->actingAs($user)
        ->post("/admin/analytics/imports/{$import->getKey()}/cancel")
        ->assertRedirect()
        ->assertSessionHasErrors('import');

    expect($import->fresh()->status)->toBe(AnalyticsImportStatus::Completed);
});

it('forbids canceling an import owned by another user', function () {
    $import = AnalyticsImport::query()->create([
        'user_id' => User::factory()->create()->getKey(),
        'type' => AnalyticsImport::TYPE_FAMS,
        'status' => AnalyticsImportStatus::Pending,
        'original_name' => 'oc-os.xlsx',
        'path' => 'analytics-imports/other.xlsx',
    ]);

    $this->actingAs(User::factory()->create())
        ->post("/admin/analytics/imports/{$import->getKey()}/cancel")
        ->assertNotFound();

    expect($import->fresh()->status)->toBe(AnalyticsImportStatus::Pending);
});

it('skips an import that was canceled before the job runs', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    $import = AnalyticsImport::query()->create([
        'user_id' => $user->getKey(),
        'type' => AnalyticsImport::TYPE_FAMS,
        'status' => AnalyticsImportStatus::Canceled,
        'original_name' => 'oc-os.xlsx',
        'path' => 'analytics-imports/canceled.xlsx',
    ]);

    Storage::disk('local')->put($import->path, 'data');

    (new ProcessAnalyticsImportJob($import))->handle(
        app(AnalyticsRecordImporter::class),
        app(AnalyticsAflImporter::class),
    );

    expect(AnalyticsRecord::query()->count())->toBe(0)
        ->and($import->fresh()->status)->toBe(AnalyticsImportStatus::Canceled)
        ->and(Storage::disk('local')->exists($import->path))->toBeFalse();
});

it('raises the memory limit before processing an import', function () {
    Storage::fake('local');

    $original = ini_get('memory_limit');

    try {
        ini_set('memory_limit', '128M');
        config(['analytics.import_memory_limit' => '1G']);

        $user = User::factory()->create();

        $import = AnalyticsImport::query()->create([
            'user_id' => $user->getKey(),
            'type' => AnalyticsImport::TYPE_FAMS,
            'status' => AnalyticsImportStatus::Canceled,
            'original_name' => 'oc-os.xlsx',
            'path' => 'analytics-imports/memory.xlsx',
        ]);

        Storage::disk('local')->put($import->path, 'data');

        (new ProcessAnalyticsImportJob($import))->handle(
            app(AnalyticsRecordImporter::class),
            app(AnalyticsAflImporter::class),
        );

        expect(ini_get('memory_limit'))->toBe('1G');
    } finally {
        ini_set('memory_limit', (string) $original);
    }
});

it('rolls back the import when it is canceled while running', function () {
    $user = User::factory()->create();
    $checks = 0;

    expect(function () use ($user, &$checks): void {
        app(AnalyticsRecordImporter::class)->import($user, [
            ['oc' => '100', 'os' => '1', 'status' => 'Aberto'],
        ], null, function () use (&$checks): bool {
            $checks++;

            return $checks >= 2;
        });
    })->toThrow(AnalyticsImportCanceledException::class);

    expect(AnalyticsRecord::query()->count())->toBe(0);
});

it('creates a new version when an imported row changes', function () {
    $user = User::factory()->create();

    $first = analyticsRecordSheet([['364007', '417', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Obra Civil', 'ENODEB', 'Rua A', 'Aimorés', 'MG', '7NLAMGV28', 'MGAOR_0001', '', 'Criado pelo NetFlow']]);
    $second = analyticsRecordSheet([['364007', '417', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Instalação', 'ENODEB', 'Rua A', 'Aimorés', 'MG', '7NLAMGV28', 'MGAOR_0001', '', 'Aceito com Ressalvas']]);

    $this->actingAs($user)->post('/admin/analytics/records/import', [
        'file' => new UploadedFile($first, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', [
            'file' => new UploadedFile($second, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHas('status', 'Importação FAMs concluída: 0 novo(s), 1 atualizado(s) e 0 sem alteração.');

    $records = AnalyticsRecord::query()->orderBy('id')->get();

    expect($records)->toHaveCount(2)
        ->and($records->first()->status)->toBe('Criado pelo NetFlow')
        ->and($records->last()->process_stage)->toBe('Instalação')
        ->and($records->last()->status)->toBe('Aceito com Ressalvas');

    @unlink($first);
    @unlink($second);
});

it('ignores an unchanged row when importing again', function () {
    $user = User::factory()->create();

    $sheet = analyticsRecordSheet([['364007', '417', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Instalação', 'ENODEB', 'Rua A', 'Aimorés', 'MG', '7NLAMGV28', 'MGAOR_0001', '', 'Aceito com Ressalvas']]);

    $this->actingAs($user)->post('/admin/analytics/records/import', [
        'file' => new UploadedFile($sheet, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', [
            'file' => new UploadedFile($sheet, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHas('status', 'Importação FAMs concluída: 0 novo(s), 0 atualizado(s) e 1 sem alteração.');

    expect(AnalyticsRecord::query()->count())->toBe(1);

    @unlink($sheet);
});

it('skips spreadsheet footer rows when importing oc and os records', function () {
    $user = User::factory()->create();

    $path = analyticsRecordSheet([
        ['364007', '417', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Instalação', 'ENODEB', 'Rua A', 'Aimorés', 'MG', '7NLAMGV28', 'MGAOR_0001', '', 'Criado pelo NetFlow'],
        ['02/11/2026', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Total :1'],
        ['', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Página 1 de 1'],
    ]);

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', [
            'file' => new UploadedFile($path, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Importação FAMs concluída: 1 novo(s), 0 atualizado(s) e 0 sem alteração. 2 linha(s) ignorada(s).');

    expect(AnalyticsRecord::query()->count())->toBe(1);

    @unlink($path);
});

it('rejects an oc and os row with an invalid creation date', function () {
    $user = User::factory()->create();

    $path = analyticsRecordSheet([['364007', '417', '31/02/2017', 'NOKIA', '', 'Acesso FAM', 'Instalação', 'ENODEB', '', '', 'MG', '', '', '', 'Criado pelo NetFlow']]);

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', [
            'file' => new UploadedFile($path, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHasErrors('import');

    expect(AnalyticsRecord::query()->count())->toBe(0)
        ->and(AnalyticsImport::query()->sole()->status->value)->toBe('failed');

    @unlink($path);
});

it('requires oc and os to import records', function () {
    $user = User::factory()->create();

    $path = analyticsRecordSheet([['', '', '16/08/2017', 'NOKIA', '', 'Acesso FAM', 'Instalação', 'ENODEB', '', '', 'MG', '', '', '', 'Criado pelo NetFlow']]);

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', [
            'file' => new UploadedFile($path, 'oc-os.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHasErrors('import');

    expect(AnalyticsRecord::query()->count())->toBe(0);

    @unlink($path);
});

it('reopens the import modal when an import fails', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/analytics/records/import', ['modal' => 'import-fams'])
        ->assertSessionHasErrors('file');

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('data-modal-open="analytics-import-modal"', false)
        ->assertSee('analytics-import-modal', false)
        ->assertSee('data-open', false);
});

it('downloads the oc and os import template', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/analytics/records/template')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('shows the imported records in the dashboard and scopes them by user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    AnalyticsRecord::query()->create([
        'user_id' => $user->getKey(),
        'oc' => '364007',
        'os' => '417',
        'created_on' => now()->toDateString(),
        'process_stage' => 'Instalação',
        'status' => 'Aceito com Ressalvas',
    ]);

    AnalyticsRecord::query()->create([
        'user_id' => $other->getKey(),
        'oc' => '999999',
        'os' => '999',
        'created_on' => now()->toDateString(),
        'process_stage' => 'Obra Civil',
        'status' => 'Criado pelo NetFlow',
    ]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('Importar dados FAMs')
        ->assertSee('364007')
        ->assertDontSee('999999')
        ->assertViewHas('recordsSummary', fn (array $summary): bool => $summary['total'] === 1)
        ->assertViewHas('recentRecords', fn (array $records): bool => count($records) === 1);
});

it('uses the latest version of each oc and os in the dashboard while keeping the history', function () {
    $user = User::factory()->create();

    AnalyticsRecord::query()->create([
        'user_id' => $user->getKey(),
        'oc' => '364007',
        'os' => '417',
        'created_on' => now()->toDateString(),
        'process_stage' => 'Obra Civil',
        'status' => 'Criado pelo NetFlow',
    ]);

    AnalyticsRecord::query()->create([
        'user_id' => $user->getKey(),
        'oc' => '364007',
        'os' => '417',
        'created_on' => now()->toDateString(),
        'process_stage' => 'Instalação',
        'status' => 'Aceito com Ressalvas',
    ]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('Histórico de alterações FAMs')
        ->assertViewHas('recordsSummary', fn (array $summary): bool => $summary['total'] === 1 && $summary['versions'] === 2)
        ->assertViewHas('recordsByStatus', fn (array $breakdown): bool => $breakdown['labels'] === ['Aceito com Ressalvas'])
        ->assertViewHas('recentRecords', fn (array $records): bool => count($records) === 1 && $records[0]['versions'] === 2)
        ->assertViewHas('recordChanges', fn (array $changes): bool => count($changes) === 1);
});

it('exports the oc and os breakdowns as csv', function () {
    $user = User::factory()->create();

    AnalyticsRecord::query()->create([
        'user_id' => $user->getKey(),
        'oc' => '364007',
        'os' => '417',
        'created_on' => now()->toDateString(),
        'process_stage' => 'Instalação',
        'status' => 'Aceito com Ressalvas',
    ]);

    $response = $this->actingAs($user)
        ->get('/admin/analytics/export?period=30')
        ->assertOk();

    expect($response->streamedContent())
        ->toContain('Etapa do processo')
        ->toContain('Instalação,1')
        ->toContain('Status FAMs');
});

/**
 * Build an XLSX spreadsheet with the OC/OS import headings.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function analyticsRecordSheet(array $rows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->fromArray([
        ['OC', 'OS', 'Data Criação', 'Fornecedor', 'Fornecedor Fiscalização', 'Tipo Projeto', 'Etapa Processo', 'Tipo Elemento', 'Endereço de Atendimento', 'Cidade', 'Estado', 'Node Name', 'Localização', 'Operador', 'Status'],
        ...$rows,
    ], null, 'A1');

    $path = tempnam(sys_get_temp_dir(), 'oc_os_').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('imports afl records from a spreadsheet', function () {
    $user = User::factory()->create();

    $path = analyticsAflSheet([[
        'code' => 'AFL20260620',
        'general_status' => 'Pendente OS/PO',
        'site_id_a' => '4G-JQIT19',
        'project' => 'CT_REUSO',
        'description' => 'VISTORIA',
        'supervisor' => 'Fabricio Paes',
        'coordinator' => 'Djalma Teixeira',
        'oc_tim' => '1311997',
        'boq_status' => 'Aprovado',
        'created_on' => '2026-05-05 00:00:00',
        'billing_status' => 'Não aplicável',
        'request_date' => '2026-03-06 00:00:00',
        'activity_planned_start_a' => '2026-03-14 00:00:00',
        'activity_actual_end_a' => '2026-03-16 00:00:00',
        'documentation_sent_b' => 'ttttt',
        'billed_value' => '1.234,56',
        'is_billed' => 'false',
        'last_changed_at' => '2026-05-05 12:48:50',
        'last_user' => 'robert@aflengenharia.com',
    ]]);

    $this->actingAs($user)
        ->post('/admin/analytics/afl/import', [
            'file' => new UploadedFile($path, 'afl.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $record = AnalyticsAflRecord::query()->firstOrFail();

    expect($record->user_id)->toBe($user->getKey())
        ->and($record->code)->toBe('AFL20260620')
        ->and($record->general_status)->toBe('Pendente OS/PO')
        ->and($record->site_id_a)->toBe('4G-JQIT19')
        ->and($record->project)->toBe('CT_REUSO')
        ->and($record->oc_tim)->toBe('1311997')
        ->and($record->created_on->toDateString())->toBe('2026-05-05')
        ->and($record->billing_status)->toBe('Não aplicável')
        ->and($record->request_date->toDateString())->toBe('2026-03-06')
        ->and($record->activity_planned_start_a->toDateString())->toBe('2026-03-14')
        ->and($record->activity_actual_end_a->toDateString())->toBe('2026-03-16')
        ->and($record->documentation_sent_b)->toBeNull()
        ->and((float) $record->billed_value)->toBe(1234.56)
        ->and($record->is_billed)->toBeFalse()
        ->and($record->last_changed_at->format('Y-m-d H:i:s'))->toBe('2026-05-05 12:48:50')
        ->and($record->last_user)->toBe('robert@aflengenharia.com');

    @unlink($path);
});

it('creates a new afl version when the row changes and ignores unchanged rows', function () {
    $user = User::factory()->create();

    $base = [
        'code' => 'AFL20260630',
        'general_status' => 'Pendente OS/PO',
        'project' => 'CT_MW',
        'supervisor' => 'Fabricio Paes',
        'billing_status' => 'Não aplicável',
    ];

    $first = analyticsAflSheet([$base]);
    $changed = analyticsAflSheet([[...$base, 'general_status' => 'Concluída']]);

    $this->actingAs($user)->post('/admin/analytics/afl/import', [
        'file' => new UploadedFile($first, 'afl.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    $this->actingAs($user)
        ->post('/admin/analytics/afl/import', [
            'file' => new UploadedFile($first, 'afl.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHas('status', 'Importação AFL concluída: 0 novo(s), 0 atualizado(s) e 1 sem alteração.');

    expect(AnalyticsAflRecord::query()->count())->toBe(1);

    $this->actingAs($user)
        ->post('/admin/analytics/afl/import', [
            'file' => new UploadedFile($changed, 'afl.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHas('status', 'Importação AFL concluída: 0 novo(s), 1 atualizado(s) e 0 sem alteração.');

    $records = AnalyticsAflRecord::query()->orderBy('id')->get();

    expect($records)->toHaveCount(2)
        ->and($records->first()->general_status)->toBe('Pendente OS/PO')
        ->and($records->last()->general_status)->toBe('Concluída');

    @unlink($first);
    @unlink($changed);
});

it('requires the afl code to import a row', function () {
    $user = User::factory()->create();

    $path = analyticsAflSheet([['project' => 'CT_MW']]);

    $this->actingAs($user)
        ->post('/admin/analytics/afl/import', [
            'file' => new UploadedFile($path, 'afl.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHasErrors('import');

    expect(AnalyticsAflRecord::query()->count())->toBe(0);

    @unlink($path);
});

it('downloads the afl import template', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/analytics/afl/template')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('shows the afl indicators and history in the dashboard', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    AnalyticsAflRecord::query()->create([
        'user_id' => $user->getKey(),
        'code' => 'AFL20260620',
        'general_status' => 'Pendente OS/PO',
        'project' => 'CT_REUSO',
        'created_on' => now()->toDateString(),
        'billing_status' => 'Não aplicável',
    ]);

    AnalyticsAflRecord::query()->create([
        'user_id' => $user->getKey(),
        'code' => 'AFL20260620',
        'general_status' => 'Concluída',
        'project' => 'CT_REUSO',
        'created_on' => now()->toDateString(),
        'billing_status' => 'Faturado',
    ]);

    AnalyticsAflRecord::query()->create([
        'user_id' => $other->getKey(),
        'code' => 'AFL-OTHER',
        'general_status' => 'Pendente OS/PO',
    ]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('Importar dados AFL')
        ->assertSee('Histórico de alterações AFL')
        ->assertDontSee('AFL-OTHER')
        ->assertViewHas('aflSummary', fn (array $summary): bool => $summary['total'] === 1 && $summary['versions'] === 2)
        ->assertViewHas('aflByStatus', fn (array $breakdown): bool => $breakdown['labels'] === ['Concluída'])
        ->assertViewHas('recentAflRecords', fn (array $records): bool => count($records) === 1 && $records[0]['versions'] === 2)
        ->assertViewHas('aflChanges', fn (array $changes): bool => count($changes) === 1);
});

it('exports the afl breakdowns as csv', function () {
    $user = User::factory()->create();

    AnalyticsAflRecord::query()->create([
        'user_id' => $user->getKey(),
        'code' => 'AFL20260620',
        'general_status' => 'Pendente OS/PO',
        'billing_status' => 'Não aplicável',
    ]);

    $response = $this->actingAs($user)
        ->get('/admin/analytics/export?period=30')
        ->assertOk();

    expect($response->streamedContent())
        ->toContain('Status geral AFL')
        ->toContain('Status de faturamento AFL');
});

it('reports progress while importing fams records', function () {
    $user = User::factory()->create();
    $reports = [];

    $result = app(AnalyticsRecordImporter::class)->import($user, [
        ['oc' => '100', 'os' => '1', 'status' => 'Aberto'],
        ['oc' => '101', 'os' => '2', 'status' => 'Fechado'],
    ], function (array $progress) use (&$reports): void {
        $reports[] = $progress;
    });

    $last = end($reports);

    expect($result['created'])->toBe(2)
        ->and($reports)->not->toBeEmpty()
        ->and($last['processed'])->toBe(2)
        ->and($last['total'])->toBe(2);
});

it('reports progress while importing afl records', function () {
    $user = User::factory()->create();
    $reports = [];

    $row = array_fill(0, count(AnalyticsAflImporter::FIELDS), null);
    $row[0] = 'PROGRESS-001';
    $row[1] = 'Pendente OS/PO';

    $result = app(AnalyticsAflImporter::class)->import($user, [$row], function (array $progress) use (&$reports): void {
        $reports[] = $progress;
    });

    $last = end($reports);

    expect($result['created'])->toBe(1)
        ->and($reports)->not->toBeEmpty()
        ->and($last['processed'])->toBe(1)
        ->and($last['total'])->toBe(1);
});

/**
 * Build an XLSX spreadsheet with the AFL layout.
 *
 * @param  array<int, array<string, string>>  $rows
 */
function analyticsAflSheet(array $rows): string
{
    $fields = AnalyticsAflImporter::FIELDS;
    $headers = $fields;
    $headers[0] = 'Cód_AFL';

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->fromArray([$headers], null, 'A1');

    $line = 2;

    foreach ($rows as $row) {
        $cells = [];

        foreach ($fields as $field) {
            $cells[] = $row[$field] ?? null;
        }

        $sheet->fromArray([$cells], null, 'A'.$line);

        $line++;
    }

    $path = tempnam(sys_get_temp_dir(), 'afl_').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('requires a dedicated connection to prepare the analytics database', function () {
    $this->artisan('analytics:database')
        ->expectsOutputToContain('ANALYTICS_DB_CONNECTION')
        ->assertFailed();
});

it('routes analytics models to the dedicated connection while core models stay on the default one', function () {
    config([
        'database.connections.analytics' => config('database.connections.sqlite'),
        'analytics.database.connection' => 'analytics',
    ]);

    expect((new AnalyticsRecord)->getConnectionName())->toBe('analytics')
        ->and((new AnalyticsSnapshot)->getConnectionName())->toBe('analytics')
        ->and((new AnalyticsAflRecord)->getConnectionName())->toBe('analytics')
        ->and((new AnalyticsRecord)->getConnection()->getName())->toBe('analytics')
        ->and((new Project)->getConnection()->getName())->toBe('sqlite');
});

it('migrates and copies the analytics rows to the dedicated connection', function () {
    $path = storage_path('framework/testing/analytics_'.Str::random(8).'.sqlite');
    touch($path);

    AnalyticsRecord::query()->create([
        'user_id' => User::factory()->create()->getKey(),
        'oc' => '364007',
        'os' => '417',
        'status' => 'Aceito com Ressalvas',
    ]);

    config([
        'database.connections.analytics' => [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        'analytics.database.connection' => 'analytics',
    ]);

    DB::purge('analytics');

    $this->artisan('analytics:database')->assertSuccessful();

    $this->artisan('analytics:database', ['--copy' => true])->assertSuccessful();

    $copied = AnalyticsRecord::on('analytics')->firstOrFail();

    expect($copied->oc)->toBe('364007')
        ->and($copied->os)->toBe('417')
        ->and($copied->status)->toBe('Aceito com Ressalvas')
        ->and(AnalyticsRecord::on('analytics')->count())->toBe(1);

    DB::purge('analytics');
    @unlink($path);
});
