<?php

use App\Enums\WorkOrderStatus;
use App\Models\Erb;
use App\Models\User;
use App\Models\WorkOrder;
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

test('the users list shows account statistics', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['is_active' => false]);
    User::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertViewHas('stats', fn (array $stats): bool => $stats['total'] === 4
            && $stats['admins'] === 1
            && $stats['active'] === 3
            && $stats['inactive'] === 1);
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

test('administrators can filter users by profile', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->admin()->create(['name' => 'Alice Admin', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bruno Comum', 'email' => 'bruno@example.com']);

    $this->actingAs($admin)
        ->get('/admin/users?profile=admin')
        ->assertOk()
        ->assertSee('Alice Admin')
        ->assertDontSee('Bruno Comum');

    $this->actingAs($admin)
        ->get('/admin/users?profile=user')
        ->assertOk()
        ->assertSee('Bruno Comum')
        ->assertDontSee('Alice Admin');
});

test('administrators can filter users by status', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Bruno Comum', 'email' => 'bruno@example.com', 'is_active' => false]);
    User::factory()->create(['name' => 'Carla Ativa', 'email' => 'carla@example.com']);

    $this->actingAs($admin)
        ->get('/admin/users?status=inactive')
        ->assertOk()
        ->assertSee('Bruno Comum')
        ->assertDontSee('Carla Ativa');

    $this->actingAs($admin)
        ->get('/admin/users?status=active')
        ->assertOk()
        ->assertSee('Carla Ativa')
        ->assertDontSee('Bruno Comum');
});

test('administrators can paginate the user list with a custom page size', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(12)->create();

    $this->actingAs($admin)
        ->get('/admin/users?per_page=25')
        ->assertOk()
        ->assertSee('25 por página');
});

test('administrators can sort users by name', function () {
    $admin = User::factory()->admin()->create(['name' => 'Alice Admin', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Bravo', 'email' => 'bob@example.com']);
    User::factory()->create(['name' => 'Carol Costa', 'email' => 'carol@example.com']);

    $this->actingAs($admin)
        ->get('/admin/users?sort=name&direction=asc')
        ->assertSeeInOrder(['Alice Admin', 'Bob Bravo', 'Carol Costa']);
});

test('administrators can switch the user list view mode', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/users?view=table')
        ->assertSee('sort=is_admin');

    $this->actingAs($admin)
        ->get('/admin/users?view=cards')
        ->assertDontSee('sort=is_admin')
        ->assertSee('grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3');

    $this->actingAs($admin)
        ->get('/admin/users?view=compact')
        ->assertDontSee('sort=is_admin')
        ->assertSee('divide-y divide-gray-100 rounded-xl');
});

test('the users list renders the delete confirmation modal', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Maria Souza']);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('id="confirm-modal"', false)
        ->assertSee('data-confirm="Excluir o usuário Maria Souza?"', false);
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

test('administrators can create a user with a surname', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Maria',
        'surname' => 'Souza',
        'email' => 'maria.souza@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', [
        'email' => 'maria.souza@example.com',
        'name' => 'Maria',
        'surname' => 'Souza',
    ]);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Maria Souza');
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

test('the dashboard shows the signed in user summaries', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Clientes')
        ->assertSee('Ordens de serviço')
        ->assertSee('Módulos')
        ->assertSee('Arquivos recentes');
});

test('the dashboard surfaces every system module', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="erbs"', false)
        ->assertSee('data-module="radio-links"', false)
        ->assertSee('data-module="catalogo"', false)
        ->assertSee('data-module="formularios"', false)
        ->assertSee('data-module="colaboradores"', false);
});

test('the dashboard only counts the signed in user records', function () {
    $user = User::factory()->create();

    Erb::factory()->count(3)->for($user)->create();
    Erb::factory()->count(2)->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertViewHas('counts', fn (array $counts): bool => $counts['erbs'] === 3);
});

test('the dashboard only surfaces the signed in user work orders', function () {
    $user = User::factory()->create();

    WorkOrder::factory()->for($user)->create([
        'number' => 'OS-8001',
        'status' => WorkOrderStatus::Open,
    ]);
    WorkOrder::factory()->for($user)->completed()->create(['number' => 'OS-8002']);

    WorkOrder::factory()->create([
        'number' => 'OS-8003',
        'status' => WorkOrderStatus::Open,
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('OS-8001')
        ->assertSee('OS-8002')
        ->assertDontSee('OS-8003');
});

test('the dashboard monthly series only counts the user work orders from the last six months', function () {
    $user = User::factory()->create();

    WorkOrder::factory()->for($user)->create([
        'created_at' => now()->startOfMonth()->addDay(),
    ]);
    WorkOrder::factory()->for($user)->create([
        'created_at' => now()->startOfMonth()->subMonths(2)->addDay(),
    ]);
    WorkOrder::factory()->for($user)->create([
        'created_at' => now()->startOfMonth()->subMonths(6)->addDay(),
    ]);
    WorkOrder::factory()->create([
        'created_at' => now()->startOfMonth()->addDay(),
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertViewHas('monthlyWorkOrders', function (array $series): bool {
            return count($series) === 6
                && collect($series)->sum('count') === 2;
        });
});
