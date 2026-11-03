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
use Plugins\Stations\Support\CoordinateParser;

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
        ->assertSee('LAS PALMAS')
        ->assertSee('Abrir menu de ações')
        ->assertSee('Ver detalhes')
        ->assertSee('role="menu"', false);
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
        ->assertSee('ID do site')
        ->assertSee('viacep.com.br', false)
        ->assertSee('name="cep"', false)
        ->assertSee('name="logradouro"', false)
        ->assertDontSee('name="tipo_de_logradouro"', false)
        ->assertSee('name="estado"', false);
});

test('the stations index renders the create modal with steps', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/stations')
        ->assertOk()
        ->assertSee('create-station-modal-title', false)
        ->assertSee('name="modal" value="create"', false)
        ->assertSee('Identificação')
        ->assertSee('Infraestrutura')
        ->assertSee('Endereço')
        ->assertSee('Localização')
        ->assertSee('Próximo')
        ->assertSee('Cadastrar estação')
        ->assertSee('<dialog', false)
        ->assertSee('stationCreateModal', false)
        ->assertSee('viacep.com.br', false)
        ->assertSee('id="modal-cep"', false)
        ->assertSee('id="modal-logradouro"', false)
        ->assertDontSee('id="modal-tipo_de_logradouro"', false)
        ->assertSee('id="modal-estado"', false)
        ->assertSee('Digite o CEP para preencher o endereço automaticamente.')
        ->assertSee('ID do site')
        ->assertSee('ID da estação')
        ->assertDontSee('SITE_ID', false)
        ->assertDontSee('STATION_ID', false)
        ->assertSeeInOrder(['id="modal-status"', 'id="modal-station_id"', 'id="modal-detentor_area"'], false);
});

test('the create modal reopens when validation fails', function () {
    $this->actingAs(User::factory()->create())
        ->from('/admin/stations')
        ->post('/admin/stations', ['modal' => 'create', 'tecnologia' => 'IP'])
        ->assertRedirect('/admin/stations')
        ->assertSessionHasErrors('site_id');
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
        'cep' => '04339-000',
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
        ->cep->toBe('04339000')
        ->latitude->toBe('-23.665278')
        ->longitude->toBe('-46.641111');
});

test('a station accepts coordinates in degrees minutes and seconds', function () {
    $this->actingAs(User::factory()->create())->post('/admin/stations', [
        'site_id' => 'SPDMS001',
        'latitude' => '23°39\'55"S',
        'longitude' => '46°38\'28"W',
    ]);

    $station = Station::query()->sole();

    expect($station)
        ->latitude->toBe('-23.665278')
        ->longitude->toBe('-46.641111');
});

test('coordinates outside the valid range are rejected', function () {
    $this->actingAs(User::factory()->create())
        ->from('/admin/stations')
        ->post('/admin/stations', [
            'modal' => 'create',
            'site_id' => 'SPLAT001',
            'latitude' => '95',
        ])
        ->assertRedirect('/admin/stations')
        ->assertSessionHasErrors('latitude');

    expect(Station::query()->count())->toBe(0);
});

test('the coordinate parser handles decimal and dms formats', function (string $input, ?float $expected) {
    $result = CoordinateParser::toDecimal($input);

    if ($expected === null) {
        expect($result)->toBeNull();

        return;
    }

    expect($result)->not->toBeNull()->toEqualWithDelta($expected, 0.000001);
})->with([
    'decimal point' => ['-23.665278', -23.665278],
    'decimal comma' => ['-23,665278', -23.665278],
    'dms with symbols' => ['23°39\'55"S', -23.665278],
    'dms with spaces' => ['23 39 55 S', -23.665278],
    'dms with prime symbols' => ['23° 39′ 55″ S', -23.665278],
    'longitude west' => ['46°38\'28"W', -46.641111],
    'hemisphere first' => ['S 23 39 55', -23.665278],
    'text is invalid' => ['abc', null],
    'minutes out of range' => ['23 75 00', null],
]);

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
