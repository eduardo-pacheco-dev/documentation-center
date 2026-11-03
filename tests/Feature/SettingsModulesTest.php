<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the settings page lists the modules the user can toggle', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($user)
        ->get('/settings')
        ->assertOk()
        ->assertSee('Módulos')
        ->assertDontSee('Usuários');

    $this->actingAs($admin)
        ->get('/settings')
        ->assertOk()
        ->assertSee('Usuários');
});

test('administrators can enable and disable the users module', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put('/settings/modules', ['modules' => ['users']])
        ->assertRedirect()
        ->assertSessionHas('status', 'Módulos atualizados com sucesso.');

    expect($admin->fresh()->enabled_modules)->toBe(['users']);

    $this->actingAs($admin->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="usuarios"', false);

    $this->actingAs($admin->fresh())
        ->put('/settings/modules', [])
        ->assertRedirect();

    expect($admin->fresh()->enabled_modules)->toBe([]);

    $this->actingAs($admin->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="inicio"', false)
        ->assertDontSee('data-module="usuarios"', false);
});

test('regular users cannot enable administrator modules', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/settings/modules', [
            'modules' => ['users'],
        ])
        ->assertSessionHasErrors('modules.0');

    expect($user->fresh()->enabled_modules)->toBeNull();
});
