<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected from the profile page', function () {
    $this->get('/profile')->assertRedirect('/login');
});

test('guests cannot update the profile', function () {
    $user = User::factory()->create(['name' => 'Maria Silva']);

    $this->put('/profile', [
        'name' => 'Intruso',
        'email' => 'intruso@example.com',
    ])->assertRedirect('/login');

    $this->assertGuest();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Maria Silva',
    ]);
});

test('users can view the profile page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/profile')
        ->assertOk();
});

test('users can update their name and e-mail', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from('/profile')->put('/profile', [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
    ]);

    $response->assertRedirect('/profile');
    $response->assertSessionHas('status', 'Perfil atualizado com sucesso.');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
    ]);
});

test('users can keep their current e-mail', function () {
    $user = User::factory()->create(['email' => 'maria@example.com']);

    $response = $this->actingAs($user)->from('/profile')->put('/profile', [
        'name' => $user->name,
        'email' => 'maria@example.com',
    ]);

    $response->assertRedirect('/profile');
    $response->assertSessionHasNoErrors();
});

test('profile update validates required fields', function () {
    $response = $this->actingAs(User::factory()->create())
        ->from('/profile')
        ->put('/profile', []);

    $response->assertRedirect('/profile');
    $response->assertSessionHasErrors(['name', 'email']);
});

test('profile update rejects an invalid e-mail', function () {
    $response = $this->actingAs(User::factory()->create())
        ->from('/profile')
        ->put('/profile', [
            'name' => 'Maria Silva',
            'email' => 'not-an-email',
        ]);

    $response->assertRedirect('/profile');
    $response->assertSessionHasErrors('email');
});

test('profile update requires a unique e-mail', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->actingAs(User::factory()->create())
        ->from('/profile')
        ->put('/profile', [
            'name' => 'Maria Silva',
            'email' => 'taken@example.com',
        ]);

    $response->assertRedirect('/profile');
    $response->assertSessionHasErrors('email');
});
