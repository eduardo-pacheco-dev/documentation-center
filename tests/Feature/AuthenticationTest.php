<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guests can view the login page', function () {
    $this->get('/login')->assertOk();
});

test('users can log in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->post('/login', [
        'email' => 'maria@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
    $this->assertAuthenticatedAs($user);
});

test('login rejects invalid credentials', function () {
    User::factory()->create([
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'maria@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('guests can view the registration page', function () {
    $this->get('/register')->assertOk();
});

test('guests can register', function () {
    $response = $this->post('/register', [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/admin');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'maria@example.com',
        'name' => 'Maria Silva',
    ]);
});

test('registration requires a unique email and a confirmed password', function () {
    User::factory()->create(['email' => 'maria@example.com']);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'password' => 'password',
        'password_confirmation' => 'another-password',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors(['email', 'password']);
    $this->assertGuest();
});

test('users can log out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('authenticated users reach the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk();
});
