<?php

use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\Nokia\Models\NokiaRom;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Providers\NokiaServiceProvider;
use Plugins\Nokia\Support\NokiaDashboardMetrics;

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

test('the dashboard metrics split the sites into acceptance buckets', function () {
    $base = [
        'checklist_fam_entrega' => false,
        'checklist_fam_instalacao' => false,
        'checklist_fam_panoramica' => false,
        'checklist_fam_entrega_status' => NokiaSite::STATUS_PENDING,
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_PENDING,
        'checklist_fam_panoramica_status' => NokiaSite::STATUS_PENDING,
        'integracao_real_at' => '2026-11-02',
    ];

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPACCEPT1',
        'checklist_fam_instalacao' => true,
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_SENT,
        'fam_instalacao_approved_at' => '2026-11-01',
    ]);

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPREJECT1',
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_REJECTED,
    ]);

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPANALYS1',
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_ANALYSIS,
    ]);

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPFIRST01',
        'os_fam_instalacao' => 'OS-I-00001',
    ]);

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPNOOSM01',
        'os_fam_entrega' => null,
    ]);

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPPENDIN1',
        'os_fam_entrega' => 'OS-E-00001',
    ]);

    $metrics = app(NokiaDashboardMetrics::class)->build();

    expect($metrics['totals'])->toMatchArray([
        'sites' => 6,
        'accepted' => 1,
        'rejected' => 1,
        'analysis' => 1,
        'first_send' => 2,
        'no_os' => 1,
        'acceptance' => 16.7,
        'projected' => 66.7,
    ]);
});

test('a FAM instalação only counts as accepted after approval', function () {
    $base = [
        'checklist_fam_entrega' => false,
        'checklist_fam_instalacao' => false,
        'checklist_fam_panoramica' => false,
        'checklist_fam_entrega_status' => NokiaSite::STATUS_PENDING,
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_SENT,
        'checklist_fam_panoramica_status' => NokiaSite::STATUS_PENDING,
        'os_fam_entrega' => 'OS-E-00001',
        'integracao_real_at' => '2026-11-02',
    ];

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPAPPROV1',
        'fam_instalacao_approved_at' => '2026-11-01',
    ]);

    NokiaSite::factory()->create([
        ...$base,
        'site_id' => 'SPSENT001',
        'fam_instalacao_approved_at' => null,
    ]);

    $metrics = app(NokiaDashboardMetrics::class)->build();

    expect($metrics['totals']['accepted'])->toBe(1)
        ->and($metrics['totals']['first_send'])->toBe(1);
});

test('the dashboard metrics ignore sites without a real integration date', function () {
    NokiaSite::factory()->create([
        'site_id' => 'SPINTEG01',
        'integracao_real_at' => '2026-11-02',
    ]);

    NokiaSite::factory()->create([
        'site_id' => 'SPNOINT01',
        'integracao_real_at' => null,
    ]);

    $metrics = app(NokiaDashboardMetrics::class)->build();

    expect($metrics['totals']['sites'])->toBe(1);
});

test('the dashboard metrics group the acceptance by regional, subcon and ROM', function () {
    $rom = NokiaRom::factory()->create([
        'first_name' => 'Ana',
        'last_name' => 'Souza',
    ]);

    NokiaSite::factory()->create([
        'regional' => 'TNO',
        'subcon' => 'AFL',
        'nokia_rom_id' => $rom->getKey(),
        'checklist_fam_instalacao' => true,
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_SENT,
        'fam_instalacao_approved_at' => '2026-11-01',
        'integracao_real_at' => '2026-11-02',
    ]);

    NokiaSite::factory()->create([
        'regional' => 'TNO',
        'subcon' => 'AFL',
        'nokia_rom_id' => $rom->getKey(),
        'checklist_fam_instalacao' => false,
        'checklist_fam_instalacao_status' => NokiaSite::STATUS_PENDING,
        'integracao_real_at' => '2026-11-02',
    ]);

    NokiaSite::factory()->create([
        'regional' => 'TCO',
        'subcon' => 'TNO',
        'nokia_rom_id' => null,
        'integracao_real_at' => '2026-11-02',
    ]);

    $metrics = app(NokiaDashboardMetrics::class)->build();

    $regional = collect($metrics['regional'])->keyBy('label');

    expect($regional->get('TNO')['total'])->toBe(2)
        ->and($regional->get('TNO')['accepted'])->toBe(1)
        ->and($regional->get('TNO')['percent'])->toBe(50.0)
        ->and($regional->get('TCO')['total'])->toBe(1);

    $subcons = collect($metrics['subcons'])->keyBy('label');

    expect($subcons->get('AFL')['total'])->toBe(2)
        ->and($subcons->get('AFL')['accepted'])->toBe(1)
        ->and($subcons->get('AFL')['gap'])->toBe(1);

    $roms = collect($metrics['roms'])->keyBy('label');

    expect($roms->get('Ana Souza')['total'])->toBe(2)
        ->and($roms->get('Ana Souza')['accepted'])->toBe(1)
        ->and($roms->has('Sem ROM'))->toBeTrue();
});

test('the dashboard widget renders the nokia overview', function () {
    NokiaSite::factory()->create([
        'site_id' => 'SPWIDGET9',
        'regional' => 'TNO',
        'subcon' => 'AFL',
        'integracao_real_at' => '2026-11-02',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Projetos Nokia')
        ->assertSee('Análise TIM')
        ->assertSee('Primeiro Envio')
        ->assertSee('Sem OS MOS')
        ->assertSee('Insights Governança')
        ->assertSee('ROM vs % Aceitação')
        ->assertSee('Últimos sites atualizados')
        ->assertSee('Todos os sites')
        ->assertSee('SPWIDGET9');
});

test('the dashboard lists every registered site including non integrated ones', function () {
    NokiaSite::factory()->create([
        'site_id' => 'SPINTEG02',
        'integracao_real_at' => '2026-11-02',
    ]);

    NokiaSite::factory()->create([
        'site_id' => 'SPNOINT02',
        'integracao_real_at' => null,
    ]);

    $metrics = app(NokiaDashboardMetrics::class)->build();
    $allSites = collect($metrics['allSites'])->keyBy('site_id');

    expect($allSites)->toHaveCount(2)
        ->and($metrics['totals']['sites'])->toBe(1)
        ->and($allSites->get('SPINTEG02')['integrated'])->toBeTrue()
        ->and($allSites->get('SPNOINT02')['integrated'])->toBeFalse();
});
