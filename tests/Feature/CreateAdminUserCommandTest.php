<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('creates an active administrator with the given password', function () {
    $this->artisan('user:create-admin', [
        'email' => 'admin@example.com',
        '--name' => 'Admin',
        '--password' => 'super-secret-password',
    ])->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'email' => 'admin@example.com',
        'name' => 'Admin',
        'is_admin' => true,
        'is_active' => true,
    ]);

    $user = User::where('email', 'admin@example.com')->firstOrFail();
    expect(Hash::check('super-secret-password', $user->password))->toBeTrue();
});

test('promotes an existing user to administrator and activates the account', function () {
    $user = User::factory()->create(['is_active' => false]);

    $this->artisan('user:create-admin', ['email' => $user->email])->assertSuccessful();

    $user->refresh();

    expect($user->is_admin)->toBeTrue();
    expect($user->is_active)->toBeTrue();
});

test('leaves an already active administrator untouched', function () {
    $user = User::factory()->admin()->create(['password' => Hash::make('current-password'), 'is_active' => true]);

    $this->artisan('user:create-admin', [
        'email' => $user->email,
        '--password' => 'new-secret-password',
    ])->assertSuccessful();

    $user->refresh();

    expect($user->is_admin)->toBeTrue();
    expect(Hash::check('current-password', $user->password))->toBeTrue();
});

test('rejects an invalid e-mail', function () {
    $this->artisan('user:create-admin', [
        'email' => 'not-an-email',
        '--name' => 'Admin',
        '--password' => 'super-secret-password',
    ])->assertFailed();

    $this->assertDatabaseCount('users', 0);
});

test('rejects a weak password', function () {
    $this->artisan('user:create-admin', [
        'email' => 'admin@example.com',
        '--name' => 'Admin',
        '--password' => 'short',
    ])->assertFailed();

    $this->assertDatabaseCount('users', 0);
});
