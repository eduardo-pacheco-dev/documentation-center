<?php

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage contacts', function () {
    $client = Client::factory()->create();

    $this->post("/admin/clients/{$client->getKey()}/contacts", ['name' => 'Ana'])
        ->assertRedirect('/login');
});

it('adds a contact to the client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/clients/{$client->getKey()}/contacts", [
            'name' => 'Ana Souza',
            'position' => 'Gerente',
            'phone' => '(11) 99999-0000',
            'email' => 'ana@example.test',
        ])
        ->assertSessionHas('status');

    $contact = $client->contacts()->first();

    expect($contact)->not->toBeNull()
        ->and($contact->name)->toBe('Ana Souza')
        ->and($contact->position)->toBe('Gerente')
        ->and($contact->is_primary)->toBeTrue();
});

it('keeps a single primary contact', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);
    $first = ClientContact::factory()->primary()->create(['client_id' => $client->getKey(), 'name' => 'Primeiro']);

    $this->actingAs($user)
        ->post("/admin/clients/{$client->getKey()}/contacts", ['name' => 'Segundo', 'is_primary' => '1'])
        ->assertSessionHas('status');

    expect($first->refresh()->is_primary)->toBeFalse()
        ->and($client->contacts()->where('is_primary', true)->count())->toBe(1)
        ->and($client->contacts()->where('name', 'Segundo')->first()->is_primary)->toBeTrue();
});

it('updates a contact', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);
    $contact = ClientContact::factory()->create(['client_id' => $client->getKey(), 'name' => 'Antigo']);

    $this->actingAs($user)
        ->put("/admin/clients/{$client->getKey()}/contacts/{$contact->getKey()}", [
            'name' => 'Novo Nome',
            'position' => 'Diretor',
        ])
        ->assertSessionHas('status');

    expect($contact->refresh()->name)->toBe('Novo Nome')
        ->and($contact->position)->toBe('Diretor');
});

it('removes a contact', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);
    $contact = ClientContact::factory()->create(['client_id' => $client->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/clients/{$client->getKey()}/contacts/{$contact->getKey()}")
        ->assertSessionHas('status');

    expect($client->contacts()->count())->toBe(0);
});

it('validates the contact fields', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/clients/{$client->getKey()}/contacts", [
            'name' => '',
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors(['name', 'email'], null, 'contact');
});

it('forbids a stranger from managing contacts', function () {
    $stranger = User::factory()->create();
    $client = Client::factory()->create();
    $contact = ClientContact::factory()->create(['client_id' => $client->getKey()]);

    $this->actingAs($stranger)
        ->post("/admin/clients/{$client->getKey()}/contacts", ['name' => 'X'])
        ->assertForbidden();

    $this->actingAs($stranger)
        ->put("/admin/clients/{$client->getKey()}/contacts/{$contact->getKey()}", ['name' => 'X'])
        ->assertForbidden();

    $this->actingAs($stranger)
        ->delete("/admin/clients/{$client->getKey()}/contacts/{$contact->getKey()}")
        ->assertForbidden();
});

it('renders the contacts on the client page', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);
    ClientContact::factory()->primary()->create(['client_id' => $client->getKey(), 'name' => 'Contato Principal']);

    $this->actingAs($user)
        ->get("/admin/clients/{$client->getKey()}")
        ->assertOk()
        ->assertSee('Contato Principal')
        ->assertSee('data-modal-open="contact-create-modal"', false);
});
