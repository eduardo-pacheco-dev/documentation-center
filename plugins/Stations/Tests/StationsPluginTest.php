<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Stations\Enums\StationLinkStatus;
use Plugins\Stations\Enums\StationStatus;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationLink;

uses(RefreshDatabase::class);

function stationFor(User $user, array $attributes = []): Station
{
    return Station::factory()->create(['user_id' => $user->getKey(), ...$attributes]);
}

it('requires authentication to access stations', function () {
    $this->get('/admin/stations')->assertRedirect('/login');
});

it('shows the stations module alongside the other modules in the sidebar', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="stations"', false)
        ->assertSeeInOrder(['data-module="erbs"', 'data-module="stations"'], false);
});

it('lists only the stations owned by the authenticated user', function () {
    $user = User::factory()->create();
    stationFor($user, ['site_id' => 'OWN-SITE']);
    stationFor(User::factory()->create(), ['site_id' => 'FOREIGN-SITE']);

    $this->actingAs($user)
        ->get('/admin/stations')
        ->assertOk()
        ->assertSee('OWN-SITE')
        ->assertDontSee('FOREIGN-SITE');
});

it('filters stations by search and status', function () {
    $user = User::factory()->create();
    stationFor($user, ['site_id' => 'ALPHA-1', 'city' => 'Campinas', 'status' => StationStatus::Active]);
    stationFor($user, ['site_id' => 'BETA-2', 'city' => 'Santos', 'status' => StationStatus::Maintenance]);

    $this->actingAs($user)
        ->get('/admin/stations?search=Campinas&status=active')
        ->assertOk()
        ->assertSee('ALPHA-1')
        ->assertDontSee('BETA-2');
});

it('creates a station and derives its code and name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/stations', [
            'site_id' => 'SITE-90001',
            'end_id' => 'END-9001',
            'operator' => 'Vivo',
            'technology' => '5G',
            'status' => 'active',
            'city' => 'Campinas',
            'state' => 'SP',
        ])
        ->assertRedirect();

    $station = Station::query()->firstOrFail();

    expect($station->user_id)->toBe($user->getKey())
        ->and($station->code)->toBe('ST-0001')
        ->and($station->name)->toBe('SITE-90001')
        ->and($station->status)->toBe(StationStatus::Active);
});

it('requires a site or end id to create a station', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/stations', ['city' => 'Campinas'])
        ->assertSessionHasErrors(['site_id', 'end_id']);
});

it('defaults new stations to the active status', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/stations', ['site_id' => 'SITE-DEFAULT'])
        ->assertRedirect();

    expect(Station::query()->firstOrFail()->status)->toBe(StationStatus::Active);
});

it('shows a station with its documents, comments and links', function () {
    $user = User::factory()->create();
    $station = stationFor($user, ['site_id' => 'SITE-SHOW', 'city' => 'Campinas']);
    $other = stationFor($user, ['site_id' => 'SITE-OTHER']);

    $station->comments()->create(['user_id' => $user->getKey(), 'body' => 'Comentário visível']);
    $station->linksAsEndA()->create([
        'user_id' => $user->getKey(),
        'station_b_id' => $other->getKey(),
        'code' => 'LNK-0001',
        'status' => 'planned',
    ]);

    $this->actingAs($user)
        ->get(route('stations.show', $station))
        ->assertOk()
        ->assertSee('SITE-SHOW')
        ->assertSee('Comentário visível')
        ->assertSee('LNK-0001');
});

it('updates a station', function () {
    $user = User::factory()->create();
    $station = stationFor($user, ['site_id' => 'SITE-BEFORE']);

    $this->actingAs($user)
        ->put(route('stations.update', $station), [
            'site_id' => 'SITE-AFTER',
            'status' => 'maintenance',
            'city' => 'Sorocaba',
        ])
        ->assertRedirect(route('stations.show', $station));

    expect($station->fresh())
        ->site_id->toBe('SITE-AFTER')
        ->status->toBe(StationStatus::Maintenance)
        ->city->toBe('Sorocaba');
});

it('soft deletes a station', function () {
    $user = User::factory()->create();
    $station = stationFor($user);

    $this->actingAs($user)
        ->delete(route('stations.destroy', $station))
        ->assertRedirect(route('stations.index'));

    expect(Station::query()->find($station->getKey()))->toBeNull()
        ->and($station->fresh()?->trashed())->toBeTrue();
});

it('forbids access to stations owned by another user', function () {
    $station = stationFor(User::factory()->create());
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->get(route('stations.show', $station))->assertForbidden();
    $this->actingAs($stranger)->get(route('stations.edit', $station))->assertForbidden();
    $this->actingAs($stranger)->put(route('stations.update', $station), ['site_id' => 'HACK'])->assertForbidden();
    $this->actingAs($stranger)->delete(route('stations.destroy', $station))->assertForbidden();
});

it('adds and removes comments on a station', function () {
    $user = User::factory()->create();
    $station = stationFor($user);

    $this->actingAs($user)
        ->post(route('stations.comments.store', $station), ['body' => 'Primeiro comentário'])
        ->assertRedirect();

    $comment = $station->comments()->firstOrFail();

    expect($comment->body)->toBe('Primeiro comentário');

    $this->actingAs($user)
        ->delete(route('stations.comments.destroy', [$station, $comment]))
        ->assertRedirect();

    expect($station->comments()->count())->toBe(0);
});

it('validates comment bodies', function () {
    $user = User::factory()->create();
    $station = stationFor($user);

    $this->actingAs($user)
        ->post(route('stations.comments.store', $station), ['body' => ''])
        ->assertSessionHasErrors('body');
});

it('returns not found when removing a comment of another station', function () {
    $user = User::factory()->create();
    $station = stationFor($user);
    $other = stationFor($user);
    $comment = $other->comments()->create(['user_id' => $user->getKey(), 'body' => 'Outra station']);

    $this->actingAs($user)
        ->delete(route('stations.comments.destroy', [$station, $comment]))
        ->assertNotFound();
});

it('uploads, downloads and removes station documents', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $station = stationFor($user);

    $this->actingAs($user)
        ->post(route('stations.documents.store', $station), [
            'documents' => [UploadedFile::fake()->create('relatorio.pdf', 12, 'application/pdf')],
        ])
        ->assertRedirect();

    $document = $station->documents()->firstOrFail();

    expect($document->original_name)->toBe('relatorio.pdf')
        ->and($document->extension())->toBe('pdf');

    Storage::disk('local')->assertExists($document->path);

    $this->actingAs($user)
        ->get(route('stations.documents.download', [$station, $document]))
        ->assertOk();

    $this->actingAs($user)
        ->delete(route('stations.documents.destroy', [$station, $document]))
        ->assertRedirect();

    Storage::disk('local')->assertMissing($document->path);
    expect($station->documents()->count())->toBe(0);
});

it('rejects unsupported document types', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $station = stationFor($user);

    $this->actingAs($user)
        ->post(route('stations.documents.store', $station), [
            'documents' => [UploadedFile::fake()->create('script.php', 2, 'text/x-php')],
        ])
        ->assertSessionHasErrors('documents.0');
});

it('creates, updates and removes links between stations', function () {
    $user = User::factory()->create();
    $station = stationFor($user);
    $other = stationFor($user);

    $this->actingAs($user)
        ->post(route('stations.links.store', $station), [
            'station_b_id' => $other->getKey(),
            'status' => 'active',
            'frequency' => 18.5,
        ])
        ->assertRedirect();

    $link = StationLink::query()->firstOrFail();

    expect($link->station_a_id)->toBe($station->getKey())
        ->and($link->station_b_id)->toBe($other->getKey())
        ->and($link->code)->toBe('LNK-0001');

    $this->actingAs($user)
        ->put(route('stations.links.update', [$station, $link]), [
            'frequency' => 23,
            'status' => 'maintenance',
        ])
        ->assertRedirect();

    expect($link->fresh())
        ->status->toBe(StationLinkStatus::Maintenance)
        ->frequency->toBe('23.000');

    $this->actingAs($user)
        ->delete(route('stations.links.destroy', [$station, $link]))
        ->assertRedirect();

    expect(StationLink::query()->count())->toBe(0);
});

it('validates link endpoints', function () {
    $user = User::factory()->create();
    $station = stationFor($user);
    $other = stationFor($user);
    $foreign = stationFor(User::factory()->create());

    $this->actingAs($user)
        ->post(route('stations.links.store', $station), ['station_b_id' => $station->getKey()])
        ->assertSessionHasErrors('station_b_id');

    $this->actingAs($user)
        ->post(route('stations.links.store', $station), ['station_b_id' => $foreign->getKey()])
        ->assertSessionHasErrors('station_b_id');

    $this->actingAs($user)->post(route('stations.links.store', $station), ['station_b_id' => $other->getKey()]);

    $this->actingAs($user)
        ->post(route('stations.links.store', $station), ['station_b_id' => $other->getKey()])
        ->assertSessionHasErrors('station_b_id');
});

it('looks up an address by zip code', function () {
    Http::fake([
        'viacep.com.br/*' => Http::response([
            'cep' => '01310-100',
            'logradouro' => 'Av. Paulista',
            'complemento' => '',
            'bairro' => 'Bela Vista',
            'localidade' => 'São Paulo',
            'uf' => 'SP',
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('stations.lookup.zip', ['zip' => '01310100']))
        ->assertOk()
        ->assertJson([
            'zip' => '01310-100',
            'street' => 'Av. Paulista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);
});

it('returns not found for an unknown zip code', function () {
    Http::fake(['viacep.com.br/*' => Http::response(['erro' => true])]);

    $this->actingAs(User::factory()->create())
        ->get(route('stations.lookup.zip', ['zip' => '99999999']))
        ->assertNotFound();
});

it('looks up an address by coordinates', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'road' => 'Avenida Paulista',
                'house_number' => '1000',
                'suburb' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'São Paulo',
                'postcode' => '01310-100',
            ],
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('stations.lookup.coordinates', ['latitude' => -23.56, 'longitude' => -46.65]))
        ->assertOk()
        ->assertJson([
            'street' => 'Avenida Paulista',
            'state' => 'SP',
            'latitude' => -23.56,
        ]);
});

it('imports stations from a spreadsheet', function () {
    $user = User::factory()->create();

    $path = stationSpreadsheet([
        ['SITE-80001', 'END-8001', 'Vivo', '5G', 'SMP-1', 'ativa', '10/01/2026', '31/12/2026', 'Av. Paulista', '1000', '', 'Bela Vista', 'São Paulo', 'SP', '01310-100', '-23.56143', '-46.65590', 'Importada'],
    ]);

    $this->actingAs($user)
        ->post(route('stations.import'), [
            'file' => new UploadedFile($path, 'stations.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertRedirect(route('stations.index'));

    $station = Station::query()->firstOrFail();

    expect($station->site_id)->toBe('SITE-80001')
        ->and($station->operator)->toBe('Vivo')
        ->and($station->status)->toBe(StationStatus::Active)
        ->and($station->city)->toBe('São Paulo');

    @unlink($path);
});

it('rejects an invalid import row without creating stations', function () {
    $user = User::factory()->create();

    $path = stationSpreadsheet([
        ['', '', 'Vivo', '5G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->post(route('stations.import'), [
            'file' => new UploadedFile($path, 'stations.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ])
        ->assertSessionHasErrors('import');

    expect(Station::query()->count())->toBe(0);

    @unlink($path);
});

it('downloads the import template', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('stations.import.template'))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('requires a valid spreadsheet to import', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('stations.import'), [
            'file' => UploadedFile::fake()->create('stations.txt', 2, 'text/plain'),
        ])
        ->assertSessionHasErrors('file');
});

/**
 * Build an XLSX spreadsheet with the station import headings.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function stationSpreadsheet(array $rows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->fromArray([
        ['Site ID', 'End ID', 'Operadora', 'Tecnologia', 'Nokia SMP ID', 'Status', 'Início', 'Término', 'Logradouro', 'Número', 'Complemento', 'Bairro', 'Cidade', 'Estado', 'CEP', 'Latitude', 'Longitude', 'Observações'],
        ...$rows,
    ], null, 'A1');

    $path = tempnam(sys_get_temp_dir(), 'stations_').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return $path;
}
