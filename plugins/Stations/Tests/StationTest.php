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
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationAttachment;
use Plugins\Stations\Models\StationComment;
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

test('the row actions menu markup is well formed', function () {
    Station::factory()->create(['site_id' => 'SPMENU001']);

    $response = $this->actingAs(User::factory()->create())->get('/admin/stations');

    $dom = new DOMDocument;

    libxml_use_internal_errors(true);
    $dom->loadHTML($response->getContent());
    libxml_clear_errors();

    $component = (new DOMXPath($dom))
        ->query('//*[@x-data and contains(@x-data, "menuId")]')
        ->item(0);

    expect($component)->not->toBeNull()
        ->and($component->getAttribute('x-data'))
        ->toContain("querySelector('[role=menuitem]')")
        ->toEndWith('}');
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
        ->assertSee('stationFormModal', false)
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

test('the stations index renders the edit modal', function () {
    $station = Station::factory()->create(['site_id' => 'SPEDIT01']);

    $this->actingAs(User::factory()->create())
        ->get('/admin/stations')
        ->assertOk()
        ->assertSee('edit-station-modal-title', false)
        ->assertSee('name="modal" value="edit"', false)
        ->assertSee('name="editing_id"', false)
        ->assertSee('open-edit-station-modal', false)
        ->assertSee(route('admin.stations.update', $station), false);
});

test('the edit modal reopens when validation fails', function () {
    $station = Station::factory()->create(['site_id' => 'SPEDIT02']);

    $this->actingAs(User::factory()->create())
        ->from('/admin/stations')
        ->followingRedirects()
        ->put(route('admin.stations.update', $station), [
            'modal' => 'edit',
            'editing_id' => $station->id,
            'site_id' => '',
        ])
        ->assertOk()
        ->assertSee('edit-station-modal-title', false)
        ->assertSee('Não foi possível salvar a estação');
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

test('the station details page renders all sections and the map', function () {
    $station = Station::factory()->create([
        'site_id' => 'SPJAB051M',
        'station_id' => 'SPJAB051M',
        'latitude' => '-23.665278',
        'longitude' => '-46.641111',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.stations.show', $station))
        ->assertOk()
        ->assertSeeInOrder(['Informações', 'Localização', 'Mapa', 'PPI/TSSR', 'Anexos', 'Comentários'])
        ->assertSee('id="station-map"', false)
        ->assertSee('unpkg.com/leaflet', false)
        ->assertSee('query=-23.665278,-46.641111', false);
});

test('attachments can be uploaded, downloaded and removed', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $station = Station::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.stations.attachments.store', $station), [
            'category' => StationAttachment::CATEGORY_PPI_TSSR,
            'files' => [
                UploadedFile::fake()->create('ppi.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->create('tssr.pdf', 140, 'application/pdf'),
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $attachments = $station->attachments()->get();

    expect($attachments)->toHaveCount(2)
        ->and($attachments->first()->category)->toBe(StationAttachment::CATEGORY_PPI_TSSR);

    $attachment = $attachments->first();

    Storage::disk('local')->assertExists($attachment->path);

    $this->actingAs($user)
        ->get(route('admin.stations.attachments.download', [$station, $attachment]))
        ->assertOk()
        ->assertDownload($attachment->original_name);

    $this->actingAs($user)
        ->delete(route('admin.stations.attachments.destroy', [$station, $attachment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    Storage::disk('local')->assertMissing($attachment->path);
    expect(StationAttachment::query()->find($attachment->getKey()))->toBeNull();
});

test('attachment uploads reject unsupported files', function () {
    Storage::fake('local');

    $station = Station::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.stations.attachments.store', $station), [
            'category' => StationAttachment::CATEGORY_ATTACHMENT,
            'files' => [UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream')],
        ])
        ->assertSessionHasErrors('files.0');

    expect(StationAttachment::query()->count())->toBe(0);
});

test('comments can be posted and are visible on the station page', function () {
    $user = User::factory()->create(['name' => 'Maria', 'surname' => 'Silva']);
    $station = Station::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.stations.comments.store', $station), [
            'body' => 'A vistoria técnica foi concluída.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $comment = StationComment::query()->sole();

    expect($comment)
        ->station_id->toBe($station->getKey())
        ->user_id->toBe($user->getKey())
        ->body->toBe('A vistoria técnica foi concluída.');

    $this->actingAs($user)
        ->get(route('admin.stations.show', $station))
        ->assertOk()
        ->assertSee('A vistoria técnica foi concluída.')
        ->assertSee('Maria Silva');
});

test('only the author or an administrator can remove a comment', function () {
    $author = User::factory()->create();
    $intruder = User::factory()->create();
    $station = Station::factory()->create();
    $comment = StationComment::factory()->for($station)->for($author)->create();

    $this->actingAs($intruder)
        ->delete(route('admin.stations.comments.destroy', [$station, $comment]))
        ->assertForbidden();

    $this->actingAs($author)
        ->delete(route('admin.stations.comments.destroy', [$station, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(StationComment::query()->find($comment->getKey()))->toBeNull();
});

test('deleting a station removes its stored attachments', function () {
    Storage::fake('local');

    $station = Station::factory()->create();
    $attachment = StationAttachment::factory()
        ->for($station)
        ->ppiTssr()
        ->create(['path' => 'stations/test/attachments/file.pdf']);

    Storage::disk('local')->put($attachment->path, 'conteúdo');

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.stations.destroy', $station))
        ->assertRedirect(route('admin.stations.index'));

    Storage::disk('local')->assertMissing($attachment->path);
    expect(StationAttachment::query()->count())->toBe(0);
});
