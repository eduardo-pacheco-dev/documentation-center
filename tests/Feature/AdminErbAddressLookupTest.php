<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('requires an authenticated user to look up an address', function () {
    Http::preventStrayRequests();

    $this->getJson(route('admin.erbs.lookup.zip', ['zip' => '01310100']))
        ->assertUnauthorized();
});

it('resolves an address from a Brazilian ZIP code', function () {
    Http::preventStrayRequests();
    Http::fake([
        'viacep.com.br/ws/01310100/json/' => Http::response([
            'cep' => '01310-100',
            'logradouro' => 'Avenida Paulista',
            'complemento' => '',
            'bairro' => 'Bela Vista',
            'localidade' => 'São Paulo',
            'uf' => 'SP',
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.erbs.lookup.zip', ['zip' => '01310-100']))
        ->assertOk()
        ->assertJson([
            'zip' => '01310-100',
            'street' => 'Avenida Paulista',
            'number' => null,
            'complement' => null,
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);
});

it('returns not found when the ZIP code is unknown', function () {
    Http::preventStrayRequests();
    Http::fake([
        'viacep.com.br/*' => Http::response(['erro' => true]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.erbs.lookup.zip', ['zip' => '99999-999']))
        ->assertNotFound();
});

it('validates the ZIP code lookup input', function () {
    Http::preventStrayRequests();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.erbs.lookup.zip'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['zip']);
});

it('resolves an address from coordinates', function () {
    Http::preventStrayRequests();
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'road' => 'Avenida Paulista',
                'house_number' => '1578',
                'suburb' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'São Paulo',
                'postcode' => '01310-200',
                'ISO3166-2-lvl4' => 'BR-SP',
            ],
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.erbs.lookup.coordinates', [
            'latitude' => '-23.561414',
            'longitude' => '-46.655881',
        ]))
        ->assertOk()
        ->assertJson([
            'zip' => '01310-200',
            'street' => 'Avenida Paulista',
            'number' => '1578',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);
});

it('resolves the state code from the state name when the ISO code is missing', function () {
    Http::preventStrayRequests();
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'city' => 'Curitiba',
                'state' => 'Paraná',
            ],
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.erbs.lookup.coordinates', [
            'latitude' => '-25.4284',
            'longitude' => '-49.2733',
        ]))
        ->assertOk()
        ->assertJson(['state' => 'PR']);
});

it('returns not found when the coordinates cannot be resolved', function () {
    Http::preventStrayRequests();
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response(['error' => 'Unable to geocode']),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.erbs.lookup.coordinates', [
            'latitude' => '0',
            'longitude' => '0',
        ]))
        ->assertNotFound();
});

it('validates the coordinate lookup input', function () {
    Http::preventStrayRequests();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.erbs.lookup.coordinates', ['latitude' => '200', 'longitude' => '10']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['latitude']);
});
