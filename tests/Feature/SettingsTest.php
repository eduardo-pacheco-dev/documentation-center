<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guests are redirected from the settings page', function () {
    $this->get('/settings')->assertRedirect('/login');
});

test('guests cannot update the theme', function () {
    $user = User::factory()->create();

    $this->put('/settings/theme', ['theme' => 'dark'])->assertRedirect('/login');

    $this->assertGuest();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'theme' => 'system',
    ]);
});

test('users can view the settings page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings')
        ->assertOk();
});

test('users can save the theme preference', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from('/settings')->put('/settings/theme', [
        'theme' => 'dark',
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHas('status', 'Preferência de tema salva com sucesso.');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'theme' => 'dark',
    ]);
});

test('theme preference rejects invalid values', function (array $payload) {
    $response = $this->actingAs(User::factory()->create())
        ->from('/settings')
        ->put('/settings/theme', $payload);

    $response->assertRedirect('/settings');
    $response->assertSessionHasErrors('theme');
})->with([
    'empty value' => [['theme' => '']],
    'unknown value' => [['theme' => 'blue']],
    'missing value' => [[]],
]);

test('users can change their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->from('/settings')->put('/settings/password', [
        'current_password' => 'current-password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHas('status', 'Senha atualizada com sucesso.');
    $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
    $this->assertFalse(Hash::check('current-password', $user->fresh()->password));
});

test('password update requires the current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->from('/settings')->put('/settings/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHasErrors('current_password');
    $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
});

test('password update must differ from the current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->from('/settings')->put('/settings/password', [
        'current_password' => 'current-password',
        'password' => 'current-password',
        'password_confirmation' => 'current-password',
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHasErrors([
        'password' => 'A nova senha deve ser diferente da senha atual.',
    ]);
});

test('password update requires a matching confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->from('/settings')->put('/settings/password', [
        'current_password' => 'current-password',
        'password' => 'new-secret-password',
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHasErrors('password');
    $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
});

test('account deletion requires the correct password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->from('/settings')->delete('/settings/account', [
        'delete_password' => 'wrong-password',
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHasErrors('delete_password');
    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('users can delete their account', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->delete('/settings/account', [
        'delete_password' => 'current-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('status', 'Sua conta foi excluída com sucesso.');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
