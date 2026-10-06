<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get('/admin/users')->assertRedirect('/login');
});

test('non administrators can not manage users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/users')
        ->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->post('/admin/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertForbidden();
});

test('administrators can list users', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Usuários');
});

test('administrators can create users', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'is_admin' => 1,
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('users', [
        'email' => 'new@example.com',
        'name' => 'New User',
        'is_admin' => true,
    ]);
});

test('administrators can delete other users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->delete('/admin/users/'.$user->getKey());

    $response->assertRedirect('/admin/users');
    $this->assertDatabaseMissing('users', ['id' => $user->getKey()]);
});

test('administrators can not delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->delete('/admin/users/'.$admin->getKey());

    $response->assertRedirect();
    $response->assertSessionHasErrors('user');
    $this->assertDatabaseHas('users', ['id' => $admin->getKey()]);
});

test('the dashboard shows platform statistics', function () {
    User::factory()->count(2)->create();
    User::factory()->admin()->create();

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Total de usuários')
        ->assertSee('Administradores');
});
