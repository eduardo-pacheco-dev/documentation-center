<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the clients area', function () {
    $this->get('/admin/clients')->assertRedirect('/login');
});

it('lists only the clients the user owns', function () {
    $owner = User::factory()->create();

    Client::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Cliente do Titular']);
    Client::factory()->create(['name' => 'Cliente Alheio']);

    $this->actingAs($owner)
        ->get('/admin/clients')
        ->assertOk()
        ->assertSee('Cliente do Titular')
        ->assertDontSee('Cliente Alheio');
});

it('filters the client list by search term', function () {
    $owner = User::factory()->create();

    Client::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Alpha Construções']);
    Client::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Beta Comércio']);

    $this->actingAs($owner)
        ->get('/admin/clients?search=Alpha')
        ->assertOk()
        ->assertSee('Alpha Construções')
        ->assertDontSee('Beta Comércio');
});

it('renders the client list in the alternate view modes', function () {
    $owner = User::factory()->create();

    Client::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Alpha Construções']);

    $this->actingAs($owner)->get('/admin/clients?view=cards')->assertOk()->assertSee('Alpha Construções');
    $this->actingAs($owner)->get('/admin/clients?view=compact')->assertOk()->assertSee('Alpha Construções');
});

it('creates a client owned by the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/clients/create')->assertOk();

    $response = $this->actingAs($user)->post('/admin/clients', [
        'name' => 'Construtora Alfa Ltda.',
        'document' => '12.345.678/0001-90',
        'email' => 'contato@alfa.test',
        'phone' => '(11) 99999-0000',
        'website' => 'https://alfa.test',
        'street' => 'Avenida Paulista',
        'number' => '1000',
        'complement' => 'Sala 12',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zip' => '01310-100',
        'notes' => 'Cliente estratégico.',
        'status' => 'active',
    ]);

    $client = Client::query()->firstOrFail();

    $response->assertRedirect(route('admin.clients.show', $client));

    expect($client->user_id)->toBe($user->getKey())
        ->and($client->name)->toBe('Construtora Alfa Ltda.')
        ->and($client->city)->toBe('São Paulo')
        ->and($client->status->value)->toBe('active');

    $this->actingAs($user)
        ->get(route('admin.clients.show', $client))
        ->assertOk()
        ->assertSee('Construtora Alfa Ltda.')
        ->assertSee('São Paulo');
});

it('renders the new client modal as a step wizard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/clients')
        ->assertOk()
        ->assertSee('data-wizard-label', false)
        ->assertSee('data-wizard-counter', false)
        ->assertSee('data-wizard-progress', false)
        ->assertSee('data-wizard-prev', false)
        ->assertSee('data-wizard-next', false)
        ->assertSee('data-wizard-submit', false)
        ->assertSee('Passo 1 de 3', false);
});

it('renders the standalone client form without wizard scaffolding', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/clients/create')
        ->assertOk()
        ->assertDontSee('data-wizard', false);
});

it('defaults the client status to active when it is not sent', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/clients', ['name' => 'Sem Status Ltda.'])->assertRedirect();

    expect(Client::query()->firstOrFail()->status->value)->toBe('active');
});

it('rejects a client without a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/clients', [])
        ->assertSessionHasErrors('name');

    expect(Client::query()->count())->toBe(0);
});

it('rejects invalid contact fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/clients', [
            'name' => 'Campos Inválidos Ltda.',
            'email' => 'não-é-email',
            'website' => 'não-é-url',
            'state' => 'SPO',
            'status' => 'unknown',
        ])
        ->assertSessionHasErrors(['email', 'website', 'state', 'status']);
});

it('forbids a stranger from managing the client', function () {
    $stranger = User::factory()->create();
    $client = Client::factory()->create();

    $this->actingAs($stranger)->get("/admin/clients/{$client->getKey()}")->assertForbidden();
    $this->actingAs($stranger)->get("/admin/clients/{$client->getKey()}/edit")->assertForbidden();
    $this->actingAs($stranger)->put("/admin/clients/{$client->getKey()}", ['name' => 'Tentativa'])->assertForbidden();
    $this->actingAs($stranger)->delete("/admin/clients/{$client->getKey()}")->assertForbidden();
});

it('updates the client details', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->get("/admin/clients/{$client->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/clients/{$client->getKey()}", [
            'name' => 'Nome Atualizado Ltda.',
            'status' => 'inactive',
        ])
        ->assertSessionHas('status');

    $client->refresh();

    expect($client->name)->toBe('Nome Atualizado Ltda.')
        ->and($client->status->value)->toBe('inactive');
});

it('renders the edit form inside a modal on the client page', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Cliente Modal Ltda.']);

    $this->actingAs($user)
        ->get("/admin/clients/{$client->getKey()}")
        ->assertOk()
        ->assertSee('data-modal-open="client-edit-modal"', false)
        ->assertSee('id="client-edit-modal"', false)
        ->assertSee(route('admin.clients.update', $client), false);
});

it('soft deletes the client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/clients/{$client->getKey()}")
        ->assertRedirect(route('admin.clients.index'));

    expect($client->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/clients')->assertOk()->assertDontSee($client->name);
});
