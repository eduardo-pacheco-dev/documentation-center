<?php

use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Providers\StationsServiceProvider;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginActivator::class);
    app()->forgetInstance(PluginRepository::class);

    $plugin = app(PluginRepository::class)->findOrFail('stations');
    $plugin->enable();

    app()->register(StationsServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    app(PluginMigrator::class)->migrate($plugin);
});

afterEach(function () {
    File::delete($this->statusesPath);
});

test('guests are redirected from the stations area', function () {
    $this->get('/admin/stations')->assertRedirect('/login');
});

test('the stations plugin appears in the navigation', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.stations.index'));
});

test('stations can be listed', function () {
    $user = User::factory()->create();

    Station::factory()->create([
        'site_id' => 'SPJAB051M',
        'station_id' => 'SPJAB051M',
        'logradouro' => 'LAS PALMAS',
        'municipio' => 'SAO PAULO',
        'status' => 'Aquisitado',
        'tecnologia' => 'IP',
    ]);

    Station::factory()->create([
        'site_id' => 'SPABC123',
        'station_id' => 'SPABC123',
        'logradouro' => 'AVENIDA PAULISTA',
        'municipio' => 'CAMPINAS',
        'status' => 'Planejado',
        'tecnologia' => 'LTE',
    ]);

    $this->actingAs($user)
        ->get('/admin/stations')
        ->assertOk()
        ->assertSee('SPJAB051M')
        ->assertSee('SPABC123')
        ->assertSee('LAS PALMAS');
});

test('stations can be searched by site, station or address', function () {
    $user = User::factory()->create();

    Station::factory()->create(['site_id' => 'SPJAB051M', 'logradouro' => 'LAS PALMAS']);
    Station::factory()->create(['site_id' => 'SPXYZ999', 'logradouro' => 'AVENIDA PAULISTA']);

    $this->actingAs($user)
        ->get('/admin/stations?search=LAS+PALMAS')
        ->assertOk()
        ->assertSee('SPJAB051M')
        ->assertDontSee('SPXYZ999');
});

test('stations can be filtered by status and technology', function () {
    $user = User::factory()->create();

    Station::factory()->create(['site_id' => 'SPFILTER1', 'status' => 'Aquisitado', 'tecnologia' => 'IP']);
    Station::factory()->create(['site_id' => 'SPFILTER2', 'status' => 'Planejado', 'tecnologia' => 'LTE']);

    $this->actingAs($user)
        ->get('/admin/stations?status=Aquisitado&tecnologia=IP')
        ->assertOk()
        ->assertSee('SPFILTER1')
        ->assertDontSee('SPFILTER2');
});

test('the create page renders the station form', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/stations/create')
        ->assertOk()
        ->assertSee('Nova estação')
        ->assertSee('SITE_ID');
});

test('a station can be created', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/stations', [
        'site_id' => 'SPJAB051M',
        'endereco_id' => 'SPSPO_2360',
        'tipo_de_elemento' => 'MSAN',
        'tecnologia' => 'IP',
        'classificacao' => 'ACESSO',
        'detentor_area' => 'ELETROPAULO',
        'tipo_de_infra' => 'Greenfield',
        'tipo_de_logradouro' => 'RUA',
        'logradouro' => 'LAS PALMAS',
        'numero' => '213',
        'bairro' => 'JABAQUARA',
        'municipio' => 'SAO PAULO',
        'estado' => 'SP',
        'cep' => '04339000',
        'regional' => 'TSP',
        'latitude' => '-23.665278',
        'longitude' => '-46.641111',
        'status' => 'Aquisitado',
        'station_id' => 'SPJAB051M',
    ]);

    $station = Station::query()->sole();

    $response->assertRedirect(route('admin.stations.show', $station));
    $response->assertSessionHas('status');

    expect($station)
        ->site_id->toBe('SPJAB051M')
        ->endereco_id->toBe('SPSPO_2360')
        ->tecnologia->toBe('IP')
        ->estado->toBe('SP')
        ->latitude->toBe('-23.665278')
        ->longitude->toBe('-46.641111');
});

test('the site id is required to create a station', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/stations', ['tecnologia' => 'IP'])
        ->assertSessionHasErrors('site_id');

    expect(Station::query()->count())->toBe(0);
});

test('the state must have two characters', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/stations', [
            'site_id' => 'SPJAB051M',
            'estado' => 'SAO',
        ])
        ->assertSessionHasErrors('estado');
});

test('a station can be displayed', function () {
    $station = Station::factory()->create([
        'site_id' => 'SPJAB051M',
        'station_id' => 'SPJAB051M',
        'logradouro' => 'LAS PALMAS',
        'bairro' => 'JABAQUARA',
        'municipio' => 'SAO PAULO',
        'status' => 'Aquisitado',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.stations.show', $station))
        ->assertOk()
        ->assertSee('SPJAB051M')
        ->assertSee('LAS PALMAS')
        ->assertSee('JABAQUARA')
        ->assertSee('Aquisitado');
});

test('the edit page is filled with the station data', function () {
    $station = Station::factory()->create([
        'site_id' => 'SPJAB051M',
        'logradouro' => 'LAS PALMAS',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.stations.edit', $station))
        ->assertOk()
        ->assertSee('SPJAB051M')
        ->assertSee('LAS PALMAS');
});

test('a station can be updated', function () {
    $station = Station::factory()->create([
        'site_id' => 'SPJAB051M',
        'status' => 'Planejado',
        'tecnologia' => 'LTE',
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->put(route('admin.stations.update', $station), [
            'site_id' => 'SPUPDATED',
            'status' => 'Aquisitado',
            'tecnologia' => '5G',
        ]);

    $response->assertRedirect(route('admin.stations.show', $station));

    expect($station->fresh())
        ->site_id->toBe('SPUPDATED')
        ->status->toBe('Aquisitado')
        ->tecnologia->toBe('5G');
});

test('a station can be deleted', function () {
    $station = Station::factory()->create();

    $response = $this->actingAs(User::factory()->create())
        ->delete(route('admin.stations.destroy', $station));

    $response->assertRedirect(route('admin.stations.index'));

    expect(Station::query()->find($station->getKey()))->toBeNull();
});
