<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a guest can sign up and receives an access token', function () {
    $response = $this->postJson('/api/v1/auth/signup', [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'Galaxy S24',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user.email', 'maria@example.com')
        ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email', 'is_admin'], 'token']]);

    $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);

    $this->withToken($response->json('data.token'))
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.email', 'maria@example.com');
});

test('signup validates the payload', function () {
    $this->postJson('/api/v1/auth/signup', [
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('signup rejects a duplicated email', function () {
    User::factory()->create(['email' => 'maria@example.com']);

    $this->postJson('/api/v1/auth/signup', [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

test('a user can log in from a mobile device', function () {
    $user = User::factory()->create([
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'maria@example.com',
        'password' => 'password',
        'device_name' => 'iPhone 17',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.email', 'maria@example.com')
        ->assertJsonStructure(['data' => ['user', 'token']]);

    expect($user->tokens()->first()->name)->toBe('iPhone 17');
});

test('login rejects invalid credentials', function () {
    User::factory()->create([
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'maria@example.com',
        'password' => 'wrong-password',
    ])->assertUnauthorized()->assertJsonPath('message', 'As credenciais informadas não correspondem a um usuário.');
});

test('protected endpoints require a valid token', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();

    $this->withToken('invalid-token')
        ->getJson('/api/v1/auth/me')
        ->assertUnauthorized();
});

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

    Auth::forgetGuards();

    $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
    expect($user->tokens()->count())->toBe(0);
});

test('authentication endpoints are rate limited', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'maria@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => 'maria@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});
