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
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;
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
        ->assertSee(route('admin.nokia.sites.index'));
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
        ->assertSee('Tarefas enviadas')
        ->assertSee('FAM Panorâmica');
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
        ->assertSee('Concluído')
        ->assertSee('TSSR')
        ->assertSee('DOC-D')
        ->assertSee('DWG')
        ->assertSee('name="files[]"', false)
        ->assertSee('open-edit-site-modal', false);
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
        ->assertSee('id="edit-site-site_id"', false);
});

test('the create modal reopens when validation fails', function () {
    $this->actingAs(User::factory()->create())
        ->from('/admin/nokia/sites')
        ->post('/admin/nokia/sites', ['modal' => 'create', 'site_id' => ''])
        ->assertRedirect('/admin/nokia/sites')
        ->assertSessionHasErrors('site_id');
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
        ->assertSee('name="checklist_tarefas_enviadas"', false)
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
            'checklist_tarefas_enviadas' => '1',
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
        ->checklist_tarefas_enviadas->toBeTrue()
        ->checklist_fam_entrega->toBeTrue()
        ->checklist_fam_instalacao->toBeFalse()
        ->checklist_fam_panoramica->toBeFalse();
});

test('the site id is required to create a site', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/nokia/sites', ['oc' => 'OC-1'])
        ->assertSessionHasErrors('site_id');

    expect(NokiaSite::query()->count())->toBe(0);
});

test('a site can be updated including the checklist', function () {
    $site = NokiaSite::factory()->create([
        'site_id' => 'SPOLD001',
        'checklist_tarefas_enviadas' => true,
        'checklist_fam_entrega' => true,
        'checklist_fam_instalacao' => false,
        'checklist_fam_panoramica' => false,
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.update', $site), [
            'site_id' => 'SPUPDATED',
            'os_fam_instalacao' => 'OS-I-99999',
            'checklist_tarefas_enviadas' => '0',
            'checklist_fam_entrega' => '1',
            'checklist_fam_instalacao' => '1',
            'checklist_fam_panoramica' => '1',
        ]);

    $response->assertRedirect(route('admin.nokia.sites.index'));

    expect($site->fresh())
        ->site_id->toBe('SPUPDATED')
        ->os_fam_instalacao->toBe('OS-I-99999')
        ->checklist_tarefas_enviadas->toBeFalse()
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
        ->and($sheet->getCell('F1')->getValue())->toBe('Tarefas enviadas')
        ->and($text)
        ->toContain('SPEXPORT1')
        ->toContain('END-11111')
        ->toContain('OC-2222')
        ->toContain('OS-E-33333')
        ->toContain('OS-I-44444');

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
        ->assertSee('500 sites')
        ->assertSee('name="file"', false);
});

test('sites can be imported from a csv file', function () {
    $csv = implode("\n", [
        'Site ID;End ID;OC;OS FAM Entrega;OS FAM Instalação;Tarefas enviadas;FAM Entrega;FAM Instalação;FAM Panorâmica',
        'SPIMP001;END-1;OC-1;OS-E-1;OS-I-1;Sim;Sim;Não;Não',
        'SPIMP002;END-2;OC-2;OS-E-2;OS-I-2;Não;Não;Não;Não',
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
        ->checklist_tarefas_enviadas->toBeTrue()
        ->checklist_fam_entrega->toBeTrue()
        ->checklist_fam_instalacao->toBeFalse()
        ->checklist_fam_panoramica->toBeFalse();
});

test('an invalid row prevents the whole import', function () {
    $csv = implode("\n", [
        'Site ID;End ID',
        'SPVALID1;END-1',
        ';END-2',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('sites.csv', $csv),
        ])
        ->assertSessionHasErrors('file');

    expect(NokiaSite::query()->count())->toBe(0);
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
        ->toBe(['Sites', 'Instruções'])
        ->and($spreadsheet->getSheet(0)->getCell('A1')->getValue())->toBe('Site ID *')
        ->and($spreadsheet->getSheet(0)->getCell('F1')->getValue())->toBe('Tarefas enviadas')
        ->and($spreadsheet->getSheet(0)->getCell('I1')->getValue())->toBe('FAM Panorâmica');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});
