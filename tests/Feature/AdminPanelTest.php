<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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

test('administrators can search for users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Maria da Silva', 'email' => 'maria@example.com']);
    User::factory()->create(['name' => 'João Souza', 'email' => 'joao@example.com']);

    $this->actingAs($admin)
        ->get('/admin/users?search=maria')
        ->assertSee('Maria da Silva')
        ->assertDontSee('João Souza');
});

test('administrators can sort users by name', function () {
    $admin = User::factory()->admin()->create(['name' => 'Alice Admin', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Bravo', 'email' => 'bob@example.com']);
    User::factory()->create(['name' => 'Carol Costa', 'email' => 'carol@example.com']);

    $this->actingAs($admin)
        ->get('/admin/users?sort=name&direction=asc')
        ->assertSeeInOrder(['Alice Admin', 'Bob Bravo', 'Carol Costa']);
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

test('administrators can update a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => Hash::make('current-password')]);

    $response = $this->actingAs($admin)->put('/admin/users/'.$user->getKey(), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'is_admin' => 1,
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('status', 'Usuário atualizado com sucesso.');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'is_admin' => true,
    ]);
    $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
});

test('updating a user can optionally change the password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => Hash::make('current-password')]);

    $this->actingAs($admin)->put('/admin/users/'.$user->getKey(), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertRedirect('/admin/users');

    $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
});

test('updating a user requires a unique e-mail', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->actingAs($admin)->from('/admin/users')->put('/admin/users/'.$user->getKey(), [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHasErrors('email');
});

test('administrators can deactivate and activate users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->from('/admin/users')
        ->patch('/admin/users/'.$user->getKey().'/toggle')
        ->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);

    $this->actingAs($admin)
        ->from('/admin/users')
        ->patch('/admin/users/'.$user->getKey().'/toggle')
        ->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => true]);
});

test('administrators can not deactivate themselves', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch('/admin/users/'.$admin->getKey().'/toggle');

    $response->assertSessionHasErrors('user');
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
});

test('deactivated users can not log in', function () {
    User::factory()->create([
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
        'is_active' => false,
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'maria@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors(['email' => 'Sua conta está desativada.']);
    $this->assertGuest();
});

test('deactivated sessions are terminated by the active middleware', function () {
    $user = User::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
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
