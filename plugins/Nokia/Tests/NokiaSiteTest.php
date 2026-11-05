<?php

use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Providers\NokiaServiceProvider;

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

test('the site list renders the details modal', function () {
    NokiaSite::factory()->create([
        'site_id' => 'SPDETAIL1',
        'end_id' => 'END-4242',
        'checklist_fam_panoramica' => true,
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/nokia/sites')
        ->assertOk()
        ->assertSee('details-site-modal', false)
        ->assertSee('open-site-details-modal', false)
        ->assertSee('Abrir menu de ações do site SPDETAIL1')
        ->assertSee('role="menu"', false)
        ->assertSee('Detalhes do site')
        ->assertSee('SPDETAIL1')
        ->assertSee('END-4242');
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
