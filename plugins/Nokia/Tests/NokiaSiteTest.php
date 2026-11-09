<?php

use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Nokia\Database\Seeders\NokiaRomSeeder;
use Plugins\Nokia\Database\Seeders\NokiaSiteSeeder;
use Plugins\Nokia\Models\NokiaRom;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;
use Plugins\Nokia\Models\NokiaSiteRejection;
use Plugins\Nokia\Models\NokiaTask;
use Plugins\Nokia\Providers\NokiaServiceProvider;
use Plugins\Nokia\Support\NokiaSiteExport;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginActivator::class);
    app()->forgetInstance(PluginRepository::class);

    $plugin = app(PluginRepository::class)->findOrFail('nokia');
    $plugin->enable();

    app()->register(NokiaServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    app(PluginMigrator::class)->migrate($plugin);
});

afterEach(function () {
    File::delete($this->statusesPath);
});

test('guests are redirected from the nokia sites area', function () {
    $this->get('/admin/nokia/sites')->assertRedirect('/login');
});

test('the nokia plugin appears in the navigation', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.nokia.sites.index'))
        ->assertDontSee(route('admin.nokia.settings.index'));
});

test('administrators see the settings button on the sites page', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee('Configurações')
        ->assertSee(route('admin.nokia.settings.index'));
});

test('the site list groups the secondary actions in a dropdown', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee('Ações')
        ->assertSee('aria-haspopup="menu"', false)
        ->assertSee(route('admin.nokia.settings.index'))
        ->assertSee(route('admin.nokia.sites.export'))
        ->assertSee(route('admin.nokia.sites.import'));
});

test('regular users do not see the settings button on the sites page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertDontSee(route('admin.nokia.settings.index'));
});

test('sites can be listed', function () {
    NokiaSite::factory()->create([
        'site_id' => 'SPALPHA1',
        'end_id' => 'END-00001',
        'oc' => 'OC-1001',
    ]);

    NokiaSite::factory()->create([
        'site_id' => 'SPBETA2',
        'end_id' => 'END-00002',
        'oc' => 'OC-1002',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee('SPALPHA1')
        ->assertSee('SPBETA2')
        ->assertSee('END-00001')
        ->assertSee('OC-1001')
        ->assertSee('Progresso');
});

test('the site list progress includes the checklist and configured tasks', function () {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPPROGRESS1',
        'checklist_fam_entrega' => false,
        'checklist_fam_instalacao' => false,
        'checklist_fam_panoramica' => false,
    ]);

    $doneTask = NokiaTask::query()->create(['name' => 'Tarefa concluída', 'position' => 1]);
    NokiaTask::query()->create(['name' => 'Tarefa pendente', 'position' => 2]);

    $site->tasks()->attach($doneTask->getKey(), ['completed_at' => now()]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee('1/5');
});

test('the site details header progress includes configured tasks', function () {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPPROGRESS2',
        'checklist_fam_entrega' => false,
        'checklist_fam_instalacao' => false,
        'checklist_fam_panoramica' => false,
    ]);

    $doneTask = NokiaTask::query()->create(['name' => 'Tarefa concluída', 'position' => 1]);
    NokiaTask::query()->create(['name' => 'Tarefa pendente', 'position' => 2]);

    $site->tasks()->attach($doneTask->getKey(), ['completed_at' => now()]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Progresso 1/5');
});

test('the site list links to the site details page', function () {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPDETAIL1',
        'end_id' => 'END-4242',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee(route('admin.nokia.sites.show', $site), false)
        ->assertSee('Abrir menu de ações do site SPDETAIL1')
        ->assertSee('role="menu"', false)
        ->assertSee('Detalhes')
        ->assertSee('SPDETAIL1')
        ->assertSee('END-4242');
});

test('a site details page shows the information, checklist and attachments', function () {
    $site = NokiaSite::factory()->completed()->create([
        'site_id' => 'SPDETAIL2',
        'end_id' => 'END-9999',
        'oc' => 'OC-777',
        'os_fam_entrega' => 'OS-E-111',
        'os_fam_instalacao' => 'OS-I-222',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('SPDETAIL2')
        ->assertSee('END-9999')
        ->assertSee('OC-777')
        ->assertSee('OS-E-111')
        ->assertSee('OS-I-222')
        ->assertSee('Checklist de atividades')
        ->assertSee('Aprovado')
        ->assertSee('TSSR')
        ->assertSee('DOC-D')
        ->assertSee('DWG')
        ->assertSee('name="files[]"', false)
        ->assertSee('attachmentDropzone', false)
        ->assertSee('x-on:drop.prevent', false)
        ->assertSee('open-edit-site-modal', false)
        ->assertDontSee('@js(', false);
});

test('the site list renders the create and edit modals', function () {
    NokiaSite::factory()->create(['site_id' => 'SPMODAL1']);

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee('create-site-modal', false)
        ->assertSee('edit-site-modal', false)
        ->assertSee('open-site-modal', false)
        ->assertSee('open-edit-site-modal', false)
        ->assertSee('name="modal" value="create"', false)
        ->assertSee('name="modal" value="edit"', false)
        ->assertSee('id="create-site-site_id"', false)
        ->assertSee('id="edit-site-site_id"', false)
        ->assertDontSee('@js(', false);
});

test('the create modal reopens when validation fails', function () {
    $this->actingAs(User::factory()->create())
        ->from('/admin/nokia/sites')
        ->post('/admin/nokia/sites', ['modal' => 'create', 'site_id' => ''])
        ->assertRedirect('/admin/nokia/sites')
        ->assertSessionHasErrors('site_id');
});

test('the nokia charts widget appears on the dashboard', function () {
    NokiaSite::factory()->completed()->create([
        'site_id' => 'SPWIDGET1',
        'integracao_real_at' => '2026-11-02',
    ]);
    NokiaSite::factory()->create([
        'site_id' => 'SPWIDGET2',
        'integracao_real_at' => '2026-11-02',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Projetos Nokia')
        ->assertSee('Status FAM')
        ->assertSee('% Aceitação Atual')
        ->assertSee('Aging Instalação')
        ->assertSee('Subcon vs % Aceitação')
        ->assertSee('Ver sites');
});

test('the seeder creates the sample sites once', function () {
    $this->seed(NokiaSiteSeeder::class);
    $this->seed(NokiaSiteSeeder::class);

    expect(NokiaSite::query()->count())->toBe(40)
        ->and(NokiaSite::query()->completed()->count())->toBe(8)
        ->and(NokiaTask::query()->count())->toBe(4)
        ->and(NokiaSite::query()->whereNotNull('interruption_reason')->count())->toBe(5)
        ->and(NokiaSite::query()->whereHas('rejections')->count())->toBe(6)
        ->and(NokiaSiteRejection::query()->count())->toBe(9)
        ->and(NokiaSiteAttachment::query()->count())->toBe(27)
        ->and(NokiaSite::query()->whereHas('tasks')->count())->toBe(32)
        ->and(NokiaSite::query()->where('site_id', 'SPJAB051M')->exists())->toBeTrue()
        ->and(NokiaSite::query()->where('site_id', 'SPFORA01J')->exists())->toBeTrue();
});

test('the rom seeder creates the sample roms once', function () {
    $this->seed(NokiaRomSeeder::class);
    $this->seed(NokiaRomSeeder::class);

    expect(NokiaRom::query()->count())->toBe(5)
        ->and(NokiaRom::query()->where('email', 'rafael.almeida@example.com')->exists())->toBeTrue()
        ->and(NokiaRom::query()->where('email', 'diego.martins@example.com')->exists())->toBeTrue();
});

test('sites can be searched by site or orders', function () {
    NokiaSite::factory()->create([
        'site_id' => 'SPALPHA1',
        'os_fam_entrega' => 'OS-E-11111',
    ]);

    NokiaSite::factory()->create([
        'site_id' => 'SPBETA2',
        'os_fam_entrega' => 'OS-E-22222',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites?search=OS-E-22222')
        ->assertOk()
        ->assertSee('SPBETA2')
        ->assertDontSee('SPALPHA1');
});

test('the create page renders the site form', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites/create')
        ->assertOk()
        ->assertSee('Novo site')
        ->assertSee('Site ID')
        ->assertSee('End ID')
        ->assertSee('OS FAM Entrega')
        ->assertSee('OS FAM Instalação')
        ->assertSee('name="checklist_fam_entrega"', false)
        ->assertSee('FAM Panorâmica');
});

test('a site can be created with the checklist', function () {
    $response = $this->actingAs(User::factory()->create())
        ->post('/admin/nokia/sites', [
            'site_id' => 'SPNEW001',
            'end_id' => 'END-12345',
            'oc' => 'OC-9876',
            'os_fam_entrega' => 'OS-E-12345',
            'os_fam_instalacao' => 'OS-I-54321',
            'checklist_fam_entrega' => '1',
            'checklist_fam_instalacao' => '0',
            'checklist_fam_panoramica' => '0',
        ]);

    $response->assertRedirect(route('admin.nokia.sites.index'));
    $response->assertSessionHas('status');

    $site = NokiaSite::query()->sole();

    expect($site)
        ->site_id->toBe('SPNEW001')
        ->end_id->toBe('END-12345')
        ->oc->toBe('OC-9876')
        ->os_fam_entrega->toBe('OS-E-12345')
        ->os_fam_instalacao->toBe('OS-I-54321')
        ->checklist_fam_entrega->toBeTrue()
        ->checklist_fam_instalacao->toBeFalse()
        ->checklist_fam_panoramica->toBeFalse();
});

test('checklist items can be moved through their statuses', function (string $status, bool $approved) {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPSTATUS1',
        'checklist_fam_entrega' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.checklist.update', [$site, 'checklist_fam_entrega']), [
            'status' => $status,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $site->refresh();

    expect($site->checklist_fam_entrega)->toBe($approved)
        ->and($site->checklistStatus('checklist_fam_entrega'))->toBe($status);
})->with([
    ['pending', false],
    ['analysis', false],
    ['rejected', false],
    ['sent', true],
]);

test('the approved checklist status is labelled as approved', function () {
    expect(NokiaSite::CHECKLIST_STATUSES[NokiaSite::STATUS_SENT])->toBe('Aprovado')
        ->and(NokiaSite::factory()->create([
            'checklist_fam_entrega_status' => NokiaSite::STATUS_SENT,
        ])->checklistStatusLabel('checklist_fam_entrega'))->toBe('Aprovado');
});

test('checklist status rejects unknown values', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.checklist.update', [$site, 'checklist_fam_entrega']), [
            'status' => 'done',
        ])
        ->assertSessionHasErrors('status');
});

test('checklist status rejects unknown checklist items', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.checklist.update', [$site, 'checklist_unknown']), [
            'status' => 'sent',
        ])
        ->assertNotFound();
});

test('the site id is required to create a site', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/nokia/sites', ['oc' => 'OC-1'])
        ->assertSessionHasErrors('site_id');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('the oc number must be unique when creating a site', function () {
    NokiaSite::factory()->create(['oc' => 'OC-DUPLICADO']);

    $this->actingAs(User::factory()->create())
        ->post('/admin/nokia/sites', [
            'site_id' => 'SPDUPLICADO',
            'oc' => 'OC-DUPLICADO',
        ])
        ->assertSessionHasErrors('oc');

    expect(NokiaSite::query()->where('site_id', 'SPDUPLICADO')->exists())->toBeFalse();
});

test('a site keeps its own oc and cannot take the oc of another site', function () {
    $site = NokiaSite::factory()->create(['oc' => 'OC-PROPRIO']);
    NokiaSite::factory()->create(['oc' => 'OC-DE-OUTRO']);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('admin.nokia.sites.update', $site), [
            'site_id' => $site->site_id,
            'oc' => 'OC-PROPRIO',
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($user)
        ->put(route('admin.nokia.sites.update', $site), [
            'site_id' => $site->site_id,
            'oc' => 'OC-DE-OUTRO',
        ])
        ->assertSessionHasErrors('oc');
});

test('the import rejects an oc repeated inside the file', function () {
    $csv = implode("\n", [
        'Site ID;OC',
        'SPDUP001;OC-REPETIDO',
        'SPDUP002;OC-REPETIDO',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertSessionHasErrors('file');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('the import updates a site whose oc already exists', function () {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPOLD003',
        'end_id' => 'END-ANTIGO',
        'oc' => 'OC-EXISTENTE',
    ]);

    $csv = implode("\n", [
        'Site ID;End ID;OC;OS FAM Entrega',
        'SPDUP003;END-NOVO;OC-EXISTENTE;OS-E-NOVO',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'))
        ->assertSessionHas('status');

    expect(NokiaSite::query()->count())->toBe(1);

    expect($site->fresh())
        ->site_id->toBe('SPDUP003')
        ->end_id->toBe('END-NOVO')
        ->oc->toBe('OC-EXISTENTE')
        ->os_fam_entrega->toBe('OS-E-NOVO');
});

test('the import creates new sites and updates existing ones in the same file', function () {
    NokiaSite::factory()->create(['oc' => 'OC-EXISTENTE']);

    $csv = implode("\n", [
        'Site ID;OC',
        'SPATUALIZADO;OC-EXISTENTE',
        'SPNOVO;OC-NOVO',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'))
        ->assertSessionHas('status');

    expect(NokiaSite::query()->count())->toBe(2)
        ->and(NokiaSite::query()->where('oc', 'OC-EXISTENTE')->sole()->site_id)->toBe('SPATUALIZADO')
        ->and(NokiaSite::query()->where('oc', 'OC-NOVO')->sole()->site_id)->toBe('SPNOVO');
});

test('a site can be updated including the checklist', function () {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPOLD001',
        'checklist_fam_entrega' => true,
        'checklist_fam_instalacao' => false,
        'checklist_fam_panoramica' => false,
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.update', $site), [
            'site_id' => 'SPUPDATED',
            'os_fam_instalacao' => 'OS-I-99999',
            'checklist_fam_entrega' => '1',
            'checklist_fam_instalacao' => '1',
            'checklist_fam_panoramica' => '1',
        ]);

    $response->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh())
        ->site_id->toBe('SPUPDATED')
        ->os_fam_instalacao->toBe('OS-I-99999')
        ->checklist_fam_entrega->toBeTrue()
        ->checklist_fam_instalacao->toBeTrue()
        ->checklist_fam_panoramica->toBeTrue();
});

test('the edit page is filled with the site data', function () {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPEDIT01',
        'end_id' => 'END-77777',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.edit', $site))
        ->assertOk()
        ->assertSee('SPEDIT01')
        ->assertSee('END-77777');
});

test('a site can be deleted', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.nokia.sites.destroy', $site))
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect(NokiaSite::query()->find($site->getKey()))->toBeNull();
});

test('administrators can manage tasks from the settings page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.nokia.settings.index'))
        ->assertOk()
        ->assertSee('Tarefas')
        ->assertSee('Nova tarefa')
        ->assertSee('create-task-modal', false)
        ->assertSee('Nenhuma tarefa cadastrada');

    $this->actingAs($admin)
        ->post(route('admin.nokia.settings.tasks.store'), ['name' => 'Validar documentação'])
        ->assertRedirect(route('admin.nokia.settings.index'))
        ->assertSessionHas('status');

    $task = NokiaTask::query()->sole();

    expect($task->name)->toBe('Validar documentação')
        ->and($task->position)->toBe(1);

    $this->actingAs($admin)
        ->put(route('admin.nokia.settings.tasks.update', $task), ['name' => 'Validar documentação final'])
        ->assertRedirect(route('admin.nokia.settings.index'));

    expect($task->refresh()->name)->toBe('Validar documentação final');

    $this->actingAs($admin)
        ->delete(route('admin.nokia.settings.tasks.destroy', $task))
        ->assertRedirect(route('admin.nokia.settings.index'));

    expect(NokiaTask::query()->count())->toBe(0);
});

test('administrators can reorder the tasks', function () {
    $admin = User::factory()->admin()->create();

    $first = NokiaTask::query()->create(['name' => 'Primeira', 'position' => 1]);
    $second = NokiaTask::query()->create(['name' => 'Segunda', 'position' => 2]);
    $third = NokiaTask::query()->create(['name' => 'Terceira', 'position' => 3]);

    $this->actingAs($admin)
        ->patch(route('admin.nokia.settings.tasks.move', $third), ['direction' => 'up'])
        ->assertRedirect(route('admin.nokia.settings.index'))
        ->assertSessionHas('status');

    expect($first->fresh()->position)->toBe(1)
        ->and($third->fresh()->position)->toBe(2)
        ->and($second->fresh()->position)->toBe(3);

    $this->actingAs($admin)
        ->patch(route('admin.nokia.settings.tasks.move', $first), ['direction' => 'down'])
        ->assertRedirect(route('admin.nokia.settings.index'));

    expect($third->fresh()->position)->toBe(1)
        ->and($first->fresh()->position)->toBe(2)
        ->and($second->fresh()->position)->toBe(3);

    $this->actingAs($admin)
        ->patch(route('admin.nokia.settings.tasks.move', $third), ['direction' => 'up'])
        ->assertRedirect(route('admin.nokia.settings.index'));

    expect($third->fresh()->position)->toBe(1);
});

test('the task reorder validates the direction', function () {
    $admin = User::factory()->admin()->create();
    $task = NokiaTask::query()->create(['name' => 'Primeira', 'position' => 1]);

    $this->actingAs($admin)
        ->patch(route('admin.nokia.settings.tasks.move', $task), ['direction' => 'sideways'])
        ->assertSessionHasErrors('direction');
});

test('regular users cannot manage tasks', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.settings.tasks.store'), ['name' => 'Nova tarefa'])
        ->assertForbidden();
});

test('the task modal reopens when validation fails', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.nokia.settings.index'))
        ->post(route('admin.nokia.settings.tasks.store'), ['name' => ''])
        ->assertRedirect(route('admin.nokia.settings.index'))
        ->assertSessionHasErrors('name');

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.nokia.settings.index'))
        ->assertOk()
        ->assertSee('create-task-modal', false);
});

test('a work interruption can be registered and cleared on the site page', function () {
    $site = NokiaSite::factory()->create(['site_id' => 'SPSTOP1']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Interrupção de obra')
        ->assertSee('Interromper obra')
        ->assertSee('open-interruption-modal', false)
        ->assertSee('Registrar interrupção');

    $this->actingAs($user)
        ->put(route('admin.nokia.sites.interruption.update', $site), [
            'reason' => 'Aguardando liberação da prefeitura',
            'return_days' => 14,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $site->refresh();

    expect($site->interruption_reason)->toBe('Aguardando liberação da prefeitura')
        ->and($site->interruption_return_days)->toBe(14)
        ->and($site->isInterrupted())->toBeTrue()
        ->and($site->interruptionReturnAt()->toDateString())->toBe(now()->addDays(14)->toDateString());

    $this->actingAs($user)
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Obra interrompida')
        ->assertSee('Editar interrupção')
        ->assertSee('Aguardando liberação da prefeitura')
        ->assertSee('14 dias')
        ->assertSee('Encerrar interrupção');

    $this->actingAs($user)
        ->delete(route('admin.nokia.sites.interruption.destroy', $site))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($site->refresh()->isInterrupted())->toBeFalse();
});

test('work interruptions accept the available return periods', function (int $days) {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.interruption.update', $site), [
            'reason' => 'Parada programada',
            'return_days' => $days,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($site->refresh()->interruption_return_days)->toBe($days);
})->with([7, 14, 21, 28]);

test('the work interruption requires a reason and a valid return period', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.interruption.update', $site), [
            'reason' => '',
            'return_days' => 10,
        ])
        ->assertSessionHasErrors(['reason', 'return_days']);
});

test('the interruption modal reopens when validation fails', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->from(route('admin.nokia.sites.show', $site))
        ->put(route('admin.nokia.sites.interruption.update', $site), [
            'reason' => '',
            'return_days' => 10,
        ])
        ->assertRedirect(route('admin.nokia.sites.show', $site))
        ->assertSessionHasErrors(['reason', 'return_days']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('interruption-modal', false);
});

test('the milestone dates can be updated and are shown on the site page', function () {
    $site = NokiaSite::factory()->create(['site_id' => 'SPDATES1']);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.dates.update', $site), [
            'mos_plan_at' => '2026-01-10',
            'mos_real_at' => '2026-01-12',
            'fam_entrega_approved_at' => '2026-02-01',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $site->refresh();

    expect($site->mos_plan_at->toDateString())->toBe('2026-01-10')
        ->and($site->mos_real_at->toDateString())->toBe('2026-01-12')
        ->and($site->fam_entrega_approved_at->toDateString())->toBe('2026-02-01');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Datas')
        ->assertSee('10/01/2026')
        ->assertSee('12/01/2026')
        ->assertSee('01/02/2026');
});

test('the site page shows an empty dates state when nothing is filled', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Nenhuma data informada');
});

test('milestone dates accept typed brazilian dates', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.dates.update', $site), [
            'mos_plan_at' => '10/01/2026',
            'mos_real_at' => '12-01-2026',
            'fam_instalacao_approved_at' => '01/02/2026',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $site->refresh();

    expect($site->mos_plan_at->toDateString())->toBe('2026-01-10')
        ->and($site->mos_real_at->toDateString())->toBe('2026-01-12')
        ->and($site->fam_instalacao_approved_at->toDateString())->toBe('2026-02-01');
});

test('the milestone dates reject invalid values', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.dates.update', $site), [
            'mos_plan_at' => 'not-a-date',
        ])
        ->assertSessionHasErrors('mos_plan_at');
});

test('a rejection can be registered for multiple FAMs and cleared', function () {
    $site = NokiaSite::factory()->create(['site_id' => 'SPREJECT1']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Rejeitar')
        ->assertSee('rejection-modal', false)
        ->assertSee('Registrar rejeição');

    $this->actingAs($user)
        ->post(route('admin.nokia.sites.rejections.store', $site), [
            'fam' => ['checklist_fam_entrega', 'checklist_fam_instalacao'],
            'rejection_reason' => 'Documentação incompleta',
            'rejection_return_days' => 21,
            'rejection_requires_field_return' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($site->rejections()->count())->toBe(2);

    $rejection = $site->rejections()->where('checklist_field', 'checklist_fam_entrega')->sole();

    expect($rejection->reason)->toBe('Documentação incompleta')
        ->and($rejection->return_days)->toBe(21)
        ->and($rejection->requires_field_return)->toBeTrue()
        ->and($rejection->returnAt()->toDateString())->toBe(now()->addDays(21)->toDateString());

    $this->actingAs($user)
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Rejeições')
        ->assertSee('Nova rejeição')
        ->assertSee('Documentação incompleta')
        ->assertSee('FAM Entrega')
        ->assertSee('FAM Instalação')
        ->assertSee('21 dias')
        ->assertSee('Retorno a campo')
        ->assertSee('Sim')
        ->assertSee('Encerrar rejeição');

    $this->actingAs($user)
        ->patch(route('admin.nokia.sites.rejections.resolve', [$site, $rejection]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($rejection->refresh()->resolved_at)->not->toBeNull()
        ->and($site->rejections()->count())->toBe(2);

    $this->actingAs($user)
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Encerrada');
});

test('rejections accept the available return periods', function (int $days) {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.rejections.store', $site), [
            'fam' => ['checklist_fam_entrega'],
            'rejection_reason' => 'Pendência de documentos',
            'rejection_return_days' => $days,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($site->rejections()->sole()->return_days)->toBe($days);
})->with([7, 14, 21, 28]);

test('the rejection requires a reason, a FAM and a valid return period', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.rejections.store', $site), [
            'fam' => [],
            'rejection_reason' => '',
            'rejection_return_days' => 10,
        ])
        ->assertSessionHasErrors(['fam', 'rejection_reason', 'rejection_return_days']);
});

test('the rejection modal reopens when validation fails', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->from(route('admin.nokia.sites.show', $site))
        ->post(route('admin.nokia.sites.rejections.store', $site), [
            'fam' => [],
            'rejection_reason' => '',
            'rejection_return_days' => 10,
        ])
        ->assertRedirect(route('admin.nokia.sites.show', $site))
        ->assertSessionHasErrors(['fam', 'rejection_reason', 'rejection_return_days']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('rejection-modal', false);
});

test('tasks can be marked as completed on the site page', function () {
    $site = NokiaSite::factory()->create(['site_id' => 'SPTASK1']);
    $task = NokiaTask::query()->create(['name' => 'Validar documentação', 'position' => 1]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Tarefas cadastradas nas configurações')
        ->assertSee('Validar documentação')
        ->assertSee('Pendente');

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.tasks.toggle', [$site, $task]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($site->tasks()->whereKey($task->getKey())->wherePivotNotNull('completed_at')->exists())->toBeTrue();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Concluída');

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.tasks.toggle', [$site, $task]))
        ->assertRedirect();

    expect($site->tasks()->whereKey($task->getKey())->wherePivotNotNull('completed_at')->exists())->toBeFalse();
});

test('the site page prompts to configure tasks when none exist', function () {
    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Nenhuma tarefa cadastrada');
});

test('attachments can be uploaded, downloaded and removed', function () {
    Storage::fake(plugin_storage('nokia')->diskName());

    $user = User::factory()->create();
    $site = NokiaSite::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.nokia.sites.attachments.store', $site), [
            'category' => NokiaSiteAttachment::CATEGORY_TSSR,
            'files' => [
                UploadedFile::fake()->create('tssr.pdf', 120, 'application/pdf'),
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $attachment = $site->attachments()->sole();

    expect($attachment)
        ->category->toBe(NokiaSiteAttachment::CATEGORY_TSSR)
        ->original_name->toBe('tssr.pdf');

    Storage::disk(plugin_storage('nokia')->diskName())->assertExists(plugin_storage('nokia')->path($attachment->path));

    $this->actingAs($user)
        ->get(route('admin.nokia.sites.attachments.download', [$site, $attachment]))
        ->assertOk()
        ->assertDownload('tssr.pdf');

    $this->actingAs($user)
        ->delete(route('admin.nokia.sites.attachments.destroy', [$site, $attachment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    Storage::disk(plugin_storage('nokia')->diskName())->assertMissing(plugin_storage('nokia')->path($attachment->path));
    expect(NokiaSiteAttachment::query()->find($attachment->getKey()))->toBeNull();
});

test('attachment uploads reject unsupported files', function () {
    Storage::fake(plugin_storage('nokia')->diskName());

    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.attachments.store', $site), [
            'category' => NokiaSiteAttachment::CATEGORY_DWG,
            'files' => [
                UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream'),
            ],
        ])
        ->assertSessionHasErrors('files.0');

    expect(NokiaSiteAttachment::query()->count())->toBe(0);
});

test('attachment uploads accept files up to 20 MB', function () {
    Storage::fake(plugin_storage('nokia')->diskName());

    $site = NokiaSite::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.nokia.sites.attachments.store', $site), [
            'category' => NokiaSiteAttachment::CATEGORY_TSSR,
            'files' => [
                UploadedFile::fake()->create('tssr.pdf', 20480, 'application/pdf'),
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(NokiaSiteAttachment::query()->count())->toBe(1);

    $this->actingAs($user)
        ->post(route('admin.nokia.sites.attachments.store', $site), [
            'category' => NokiaSiteAttachment::CATEGORY_TSSR,
            'files' => [
                UploadedFile::fake()->create('grande.pdf', 20481, 'application/pdf'),
            ],
        ])
        ->assertSessionHasErrors('files.0');

    expect(NokiaSiteAttachment::query()->count())->toBe(1);
});

test('deleting a site removes its stored attachments', function () {
    Storage::fake(plugin_storage('nokia')->diskName());

    $site = NokiaSite::factory()->create();
    $attachment = NokiaSiteAttachment::factory()
        ->category(NokiaSiteAttachment::CATEGORY_DOC_D)
        ->create([
            'nokia_site_id' => $site->getKey(),
            'path' => 'sites/'.$site->getKey().'/doc-d/planta.pdf',
        ]);

    Storage::disk(plugin_storage('nokia')->diskName())->put(plugin_storage('nokia')->path($attachment->path), 'conteúdo');

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.nokia.sites.destroy', $site))
        ->assertRedirect(route('admin.nokia.sites.index'));

    Storage::disk(plugin_storage('nokia')->diskName())->assertMissing(plugin_storage('nokia')->path($attachment->path));
    expect(NokiaSiteAttachment::query()->count())->toBe(0);
});

test('all sites can be exported to excel', function () {
    NokiaSite::factory()->completed()->create([
        'site_id' => 'SPEXPORT1',
        'end_id' => 'END-11111',
        'oc' => 'OC-2222',
        'os_fam_entrega' => 'OS-E-33333',
        'os_fam_instalacao' => 'OS-I-44444',
    ]);

    NokiaTask::query()->create(['name' => 'Validar documentação', 'position' => 1]);

    $fileName = app(NokiaSiteExport::class)->fileName();

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.export'))
        ->assertOk()
        ->assertDownload($fileName);

    $path = sys_get_temp_dir().'/nokia-sites-export-'.Str::random(8).'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getSheet(0);
    $text = collect($sheet->toArray())->flatten()->filter()->implode(' | ');

    expect($sheet->getTitle())->toBe('Sites')
        ->and($sheet->getCell('A1')->getValue())->toBe('Site ID')
        ->and($sheet->getCell('F1')->getValue())->toBe('OS FAM Instalação')
        ->and($sheet->getCell('G1')->getValue())->toBe('Regional')
        ->and($sheet->getCell('H1')->getValue())->toBe('Subcon')
        ->and($sheet->getCell('I1')->getValue())->toBe('Checklist - FAM Entrega')
        ->and($text)
        ->toContain('SPEXPORT1')
        ->toContain('END-11111')
        ->toContain('OC-2222')
        ->toContain('OS-E-33333')
        ->toContain('OS-I-44444')
        ->toContain('Tarefa - Validar documentação')
        ->toContain('Interrupção - Motivo')
        ->toContain('Rejeição - FAM Entrega')
        ->toContain('MOS Plan')
        ->toContain('FAM Panorâmica Aprovado');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});

test('the excel export respects the search filter', function () {
    NokiaSite::factory()->create(['site_id' => 'SPALPHA1']);
    NokiaSite::factory()->create(['site_id' => 'SPBETA2']);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.export', ['search' => 'SPALPHA']))
        ->assertOk();

    $path = sys_get_temp_dir().'/nokia-sites-export-'.Str::random(8).'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = IOFactory::load($path);
    $text = collect($spreadsheet->getSheet(0)->toArray())->flatten()->filter()->implode(' | ');

    expect($text)
        ->toContain('SPALPHA1')
        ->not->toContain('SPBETA2');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});

test('the import page renders the instructions and file field', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.import'))
        ->assertOk()
        ->assertSee('Importar sites')
        ->assertSee('Baixar modelo Excel')
        ->assertSee('10 MB')
        ->assertSee('500 sites')
        ->assertSee('name="file"', false);
});

test('sites can be imported from a csv file', function () {
    $csv = implode("\n", [
        'Site ID;End ID;OC;OS FAM Entrega;OS FAM Instalação;FAM Entrega;FAM Instalação;FAM Panorâmica',
        'SPIMP001;END-1;OC-1;OS-E-1;OS-I-1;Sim;Não;Não',
        'SPIMP002;END-2;OC-2;OS-E-2;OS-I-2;Não;Não;Não',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'))
        ->assertSessionHas('status');

    expect(NokiaSite::query()->count())->toBe(2);

    expect(NokiaSite::query()->where('site_id', 'SPIMP001')->sole())
        ->end_id->toBe('END-1')
        ->oc->toBe('OC-1')
        ->os_fam_entrega->toBe('OS-E-1')
        ->os_fam_instalacao->toBe('OS-I-1')
        ->checklist_fam_entrega->toBeTrue()
        ->checklist_fam_instalacao->toBeFalse()
        ->checklist_fam_panoramica->toBeFalse();
});

test('sites can be imported with dates in different formats', function () {
    $csv = implode("\n", [
        'Site ID;OC;MOS Plan;MOS Real;Instalação Plan;RFA Real;FAM Entrega Aprovado',
        'SPDATE1;OC-DATE1;02/03/2026;2026-03-05;07-03-2026;10/03/2026;15/03/2026',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'))
        ->assertSessionHas('status');

    $site = NokiaSite::query()->where('oc', 'OC-DATE1')->sole();

    expect($site->mos_plan_at?->toDateString())->toBe('2026-03-02')
        ->and($site->mos_real_at?->toDateString())->toBe('2026-03-05')
        ->and($site->instalacao_plan_at?->toDateString())->toBe('2026-03-07')
        ->and($site->rfa_real_at?->toDateString())->toBe('2026-03-10')
        ->and($site->fam_entrega_approved_at?->toDateString())->toBe('2026-03-15');
});

test('the import rejects an invalid date', function () {
    $csv = implode("\n", [
        'Site ID;OC;MOS Plan',
        'SPDATE2;OC-DATE2;31/02/2026',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertSessionHasErrors('file');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('the import does not clear dates when their columns are absent', function () {
    $site = NokiaSite::factory()->create([
        'oc' => 'OC-KEEPDATE',
        'mos_plan_at' => '2026-01-10',
        'rfa_real_at' => '2026-01-20',
    ]);

    $csv = implode("\n", [
        'Site ID;OC',
        'SPKEEPDATE;OC-KEEPDATE',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh())
        ->site_id->toBe('SPKEEPDATE')
        ->mos_plan_at->toDateString()->toBe('2026-01-10')
        ->rfa_real_at->toDateString()->toBe('2026-01-20');
});

test('sites can be imported with the configured activities', function () {
    $validar = NokiaTask::query()->create(['name' => 'Validar documentação', 'position' => 1]);
    $fotos = NokiaTask::query()->create(['name' => 'Enviar fotos', 'position' => 2]);

    $csv = implode("\n", [
        'Site ID;OC;Tarefa - Validar documentação;Atividade - Enviar fotos',
        'SPTASK1;OC-TASK1;Sim;Não',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'))
        ->assertSessionHas('status');

    $site = NokiaSite::query()->where('oc', 'OC-TASK1')->sole();

    expect($site->tasks()->whereKey($validar->getKey())->wherePivotNotNull('completed_at')->exists())->toBeTrue()
        ->and($site->tasks()->whereKey($fotos->getKey())->wherePivotNull('completed_at')->exists())->toBeTrue();
});

test('the import reopens and preserves configured activities', function () {
    $task = NokiaTask::query()->create(['name' => 'Validar documentação', 'position' => 1]);
    $site = NokiaSite::factory()->create(['oc' => 'OC-TASK2']);

    $site->tasks()->attach($task->getKey(), ['completed_at' => now()]);

    $csv = implode("\n", [
        'Site ID;OC',
        'SPTASK2;OC-TASK2',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh()->tasks()->whereKey($task->getKey())->wherePivotNotNull('completed_at')->exists())->toBeTrue();

    $csv = implode("\n", [
        'Site ID;OC;Tarefa - Validar documentação',
        'SPTASK2;OC-TASK2;Não',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh()->tasks()->whereKey($task->getKey())->wherePivotNull('completed_at')->exists())->toBeTrue();
});

test('an invalid row prevents the whole import', function () {
    $csv = implode("\n", [
        'Site ID;End ID;OC',
        'SPVALID1;END-1;OC-VALID1',
        ';END-2;OC-VALID2',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertSessionHasErrors('file');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('the import requires the oc column', function () {
    $csv = implode("\n", [
        'Site ID;End ID',
        'SPNOOC1;END-1',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertSessionHasErrors('file');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('the import requires an oc value on every row', function () {
    $csv = implode("\n", [
        'Site ID;OC',
        'SPNOOC2;',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertSessionHasErrors('file');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('sites can be imported with a ROM name', function () {
    $rom = NokiaRom::factory()->create(['first_name' => 'Ana', 'last_name' => 'Souza']);

    $csv = implode("\n", [
        'Site ID;OC;ROM',
        'SPROM001;OC-ROM1;Ana Souza',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'))
        ->assertSessionHas('status');

    expect(NokiaSite::query()->where('oc', 'OC-ROM1')->sole()->nokia_rom_id)->toBe($rom->getKey());
});

test('the import rejects an unknown ROM', function () {
    $csv = implode("\n", [
        'Site ID;OC;ROM',
        'SPROM002;OC-ROM2;ROM inexistente',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertSessionHasErrors('file');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('the import preserves the ROM when the column is absent or empty', function () {
    $rom = NokiaRom::factory()->create(['first_name' => 'Ana', 'last_name' => 'Souza']);
    $site = NokiaSite::factory()->create(['oc' => 'OC-ROM3', 'nokia_rom_id' => $rom->getKey()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', "Site ID;OC\nSPROM003;OC-ROM3"),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh()->nokia_rom_id)->toBe($rom->getKey());

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', "Site ID;OC;ROM\nSPROM003;OC-ROM3;"),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh()->nokia_rom_id)->toBe($rom->getKey());
});

test('empty import cells do not overwrite the stored site', function () {
    $site = NokiaSite::factory()->create([
        'oc' => 'OC-KEEPALL',
        'end_id' => 'END-KEEP',
        'os_fam_entrega' => 'OS-E-KEEP',
        'os_fam_instalacao' => 'OS-I-KEEP',
        'regional' => 'TNO',
        'subcon' => 'AFL',
        'mos_real_at' => '2026-01-10',
        'checklist_fam_entrega' => true,
        'checklist_fam_entrega_status' => NokiaSite::STATUS_SENT,
    ]);

    $csv = implode("\n", [
        'Site ID;OC;End ID;OS FAM Entrega;OS FAM Instalação;Regional;Subcon;FAM Entrega;MOS Real',
        'SPKEEPALL;OC-KEEPALL;;;;;;;',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh())
        ->end_id->toBe('END-KEEP')
        ->os_fam_entrega->toBe('OS-E-KEEP')
        ->os_fam_instalacao->toBe('OS-I-KEEP')
        ->regional->toBe('TNO')
        ->subcon->toBe('AFL')
        ->mos_real_at->toDateString()->toBe('2026-01-10')
        ->checklist_fam_entrega->toBeTrue();
});

test('sites can be imported from an excel file', function () {
    $rom = NokiaRom::factory()->create(['first_name' => 'Ana', 'last_name' => 'Souza']);

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([
        ['Site ID', 'OC', 'OS FAM Instalação', 'ROM', 'MOS Real'],
        ['SPXLSX01', 123456, 654321, 'Ana Souza'],
    ]);
    $sheet->setCellValue('E2', ExcelDate::PHPToExcel(new DateTimeImmutable('2026-03-05')));
    $sheet->getStyle('E2')->getNumberFormat()->setFormatCode('dd/mm/yyyy');

    $path = sys_get_temp_dir().'/nokia-import-'.Str::random(8).'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    try {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.nokia.sites.import.store'), [
                'file' => new UploadedFile($path, 'sites.xlsx', null, null, true),
            ])
            ->assertRedirect(route('admin.nokia.sites.index'))
            ->assertSessionHas('status');
    } finally {
        @unlink($path);
    }

    $site = NokiaSite::query()->where('oc', '123456')->sole();

    expect($site->oc)->toBe('123456')
        ->and($site->os_fam_instalacao)->toBe('654321')
        ->and($site->nokia_rom_id)->toBe($rom->getKey())
        ->and($site->mos_real_at?->toDateString())->toBe('2026-03-05');
});

test('the import template can be downloaded as an excel file', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.import.template'))
        ->assertOk()
        ->assertDownload('modelo-importacao-sites-nokia.xlsx');

    $path = sys_get_temp_dir().'/nokia-sites-template-'.Str::random(8).'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = IOFactory::load($path);

    expect($spreadsheet->getSheetNames())
        ->toBe(['Sites', 'Exemplo', 'Instruções'])
        ->and($spreadsheet->getSheet(0)->getCell('A1')->getValue())->toBe('Site ID *')
        ->and($spreadsheet->getSheet(0)->getCell('C1')->getValue())->toBe('OC *')
        ->and($spreadsheet->getSheet(0)->getCell('G1')->getValue())->toBe('Regional')
        ->and($spreadsheet->getSheet(0)->getCell('H1')->getValue())->toBe('Subcon')
        ->and($spreadsheet->getSheet(0)->getCell('I1')->getValue())->toBe('FAM Entrega')
        ->and($spreadsheet->getSheet(0)->getCell('K1')->getValue())->toBe('FAM Panorâmica')
        ->and($spreadsheet->getSheet(0)->getCell('L1')->getValue())->toBe('MOS Plan')
        ->and($spreadsheet->getSheet(0)->getCell('V1')->getValue())->toBe('FAM Panorâmica Aprovado')
        ->and($spreadsheet->getSheet(1)->getCell('A2')->getValue())->toBe('SPEXEMPLO01')
        ->and($spreadsheet->getSheet(1)->getCell('C2')->getValue())->toBe('OC-0001')
        ->and($spreadsheet->getSheet(1)->getCell('L2')->getValue())->toBe('10/01/2026');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});

test('the import template includes the configured activity columns', function () {
    NokiaTask::query()->create(['name' => 'Validar documentação', 'position' => 1]);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.import.template'))
        ->assertOk();

    $path = sys_get_temp_dir().'/nokia-sites-template-'.Str::random(8).'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = IOFactory::load($path);

    expect($spreadsheet->getSheet(0)->getCell('W1')->getValue())->toBe('Tarefa - Validar documentação')
        ->and($spreadsheet->getSheet(1)->getCell('W2')->getValue())->toBe('Sim');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});

test('the nokia settings page manages roms', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/nokia/settings')
        ->assertOk()
        ->assertSee('Configurações')
        ->assertSee('Novo ROM')
        ->assertSee('create-rom-modal', false)
        ->assertSee('Nenhum ROM cadastrado');

    $this->actingAs($admin)
        ->post('/admin/nokia/settings/roms', [
            'first_name' => 'Maria',
            'last_name' => 'Souza',
            'email' => 'maria.souza@example.com',
            'phone' => '(11) 99999-1234',
        ])
        ->assertRedirect(route('admin.nokia.settings.index'))
        ->assertSessionHas('status');

    $rom = NokiaRom::query()->sole();

    expect($rom)
        ->first_name->toBe('Maria')
        ->last_name->toBe('Souza')
        ->email->toBe('maria.souza@example.com')
        ->phone->toBe('(11) 99999-1234')
        ->and($rom->fullName())->toBe('Maria Souza');

    $this->actingAs($admin)
        ->get('/admin/nokia/settings')
        ->assertOk()
        ->assertSee('Maria Souza')
        ->assertSee('maria.souza@example.com')
        ->assertSee('(11) 99999-1234');
});

test('non administrators cannot access the nokia settings page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/settings')
        ->assertForbidden();
});

test('the create rom form is rendered as a modal and validates the fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/nokia/settings')
        ->assertOk()
        ->assertSee('create-rom-modal', false)
        ->assertSee('Cadastrar ROM')
        ->assertSee('name="first_name"', false)
        ->assertSee('name="last_name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="phone"', false);

    $this->actingAs($admin)
        ->from('/admin/nokia/settings')
        ->post('/admin/nokia/settings/roms', [
            'first_name' => '',
            'last_name' => 'Souza',
            'email' => 'invalido',
            'phone' => '',
        ])
        ->assertRedirect('/admin/nokia/settings')
        ->assertSessionHasErrors(['first_name', 'email', 'phone']);
});

test('a rom cannot be registered with a duplicated email', function () {
    $admin = User::factory()->admin()->create();

    NokiaRom::factory()->create(['email' => 'rom@example.com']);

    $this->actingAs($admin)
        ->post('/admin/nokia/settings/roms', [
            'first_name' => 'Outro',
            'last_name' => 'ROM',
            'email' => 'rom@example.com',
            'phone' => '(11) 98888-0000',
        ])
        ->assertSessionHasErrors('email');

    expect(NokiaRom::query()->count())->toBe(1);
});

test('a rom can be updated and deleted', function () {
    $admin = User::factory()->admin()->create();
    $rom = NokiaRom::factory()->create([
        'first_name' => 'Carlos',
        'last_name' => 'Lima',
        'email' => 'carlos@example.com',
        'phone' => '(11) 97777-0000',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.nokia.settings.roms.update', $rom), [
            'first_name' => 'Carlos',
            'last_name' => 'Lima',
            'email' => 'carlos.lima@example.com',
            'phone' => '(11) 96666-0000',
        ])
        ->assertRedirect(route('admin.nokia.settings.index'));

    expect($rom->fresh())
        ->email->toBe('carlos.lima@example.com')
        ->phone->toBe('(11) 96666-0000');

    $this->actingAs($admin)
        ->delete(route('admin.nokia.settings.roms.destroy', $rom))
        ->assertRedirect(route('admin.nokia.settings.index'));

    expect(NokiaRom::query()->find($rom->getKey()))->toBeNull();
});

test('a site can be linked to a rom on creation and listing', function () {
    $rom = NokiaRom::factory()->create([
        'first_name' => 'Ana',
        'last_name' => 'Souza',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites/create')
        ->assertOk()
        ->assertSee('ROM')
        ->assertSee('Ana Souza');

    $this->actingAs(User::factory()->create())
        ->post('/admin/nokia/sites', [
            'site_id' => 'SPROM001',
            'nokia_rom_id' => $rom->getKey(),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    $site = NokiaSite::query()->where('site_id', 'SPROM001')->sole();

    expect($site->nokia_rom_id)->toBe($rom->getKey());

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee('Ana Souza');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.nokia.sites.show', $site))
        ->assertOk()
        ->assertSee('Ana Souza');
});

test('a site can be updated to change its rom', function () {
    $rom = NokiaRom::factory()->create(['first_name' => 'Bruno', 'last_name' => 'Alves']);
    $site = NokiaSite::factory()->create(['site_id' => 'SPROM002']);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.update', $site), [
            'site_id' => 'SPROM002',
            'nokia_rom_id' => $rom->getKey(),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh()->nokia_rom_id)->toBe($rom->getKey());

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.update', $site), [
            'site_id' => 'SPROM002',
            'nokia_rom_id' => '',
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh()->nokia_rom_id)->toBeNull();
});

test('deleting a rom keeps the sites without a rom', function () {
    $rom = NokiaRom::factory()->create();
    $site = NokiaSite::factory()->create(['nokia_rom_id' => $rom->getKey()]);

    $rom->delete();

    expect($site->fresh()->nokia_rom_id)->toBeNull();
});

test('an approval date marks the matching checklist as approved', function () {
    $site = NokiaSite::factory()->create([
        'checklist_fam_entrega' => false,
        'checklist_fam_entrega_status' => NokiaSite::STATUS_PENDING,
        'fam_entrega_approved_at' => '2026-02-01',
    ]);

    expect($site->checklist_fam_entrega)->toBeTrue()
        ->and($site->checklist_fam_entrega_status)->toBe(NokiaSite::STATUS_SENT);
});

test('a later approval date also marks the checklist as approved', function () {
    $site = NokiaSite::factory()->create([
        'checklist_fam_instalacao' => false,
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_PENDING,
    ]);

    $site->update(['fam_instalacao_approved_at' => '2026-03-10']);

    expect($site->fresh()->checklist_fam_instalacao)->toBeTrue()
        ->and($site->fresh()->checklist_fam_instalacao_status)->toBe(NokiaSite::STATUS_SENT);
});

test('the approval date does not override a later checklist status change', function () {
    $site = NokiaSite::factory()->create([
        'fam_instalacao_approved_at' => '2026-03-10',
    ]);

    $site->update([
        'checklist_fam_instalacao' => false,
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_REJECTED,
    ]);

    expect($site->fresh()->checklist_fam_instalacao_status)->toBe(NokiaSite::STATUS_REJECTED);
});

test('importing approval dates marks the checklists as approved', function () {
    $csv = implode("\n", [
        'Site ID;OC;FAM Entrega Aprovado;FAM Instalação Aprovado;FAM Panorâmica Aprovado',
        'SPAPPROV1;OC-APPROV1;01/02/2026;02/02/2026;03/02/2026',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertRedirect(route('admin.nokia.sites.index'));

    $site = NokiaSite::query()->where('oc', 'OC-APPROV1')->sole();

    expect($site->checklist_fam_entrega_status)->toBe(NokiaSite::STATUS_SENT)
        ->and($site->checklist_fam_instalacao_status)->toBe(NokiaSite::STATUS_SENT)
        ->and($site->checklist_fam_panoramica_status)->toBe(NokiaSite::STATUS_SENT);
});
