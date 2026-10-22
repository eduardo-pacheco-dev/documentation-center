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
        ->assertSee('Clientes')
        ->assertSee('Projetos')
        ->assertDontSee('Usuários');

    $this->actingAs($admin)
        ->get('/settings')
        ->assertOk()
        ->assertSee('Usuários')
        ->assertSee('Backups');
});

test('a user can disable modules and hide them from the navigation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/settings/modules', [
            'modules' => ['clients', 'projects'],
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Módulos atualizados com sucesso.');

    expect($user->fresh()->enabled_modules)->toBe(['clients', 'projects']);

    $this->actingAs($user->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="clientes"', false)
        ->assertSee('data-module="projetos"', false)
        ->assertDontSee('data-module="ordens-de-servico"', false)
        ->assertDontSee('data-stat="os-abertas"', false);
});

test('disabling every module keeps only the dashboard enabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/settings/modules', [])
        ->assertRedirect();

    expect($user->fresh()->enabled_modules)->toBe([]);

    $this->actingAs($user->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="inicio"', false)
        ->assertDontSee('data-module="clientes"', false)
        ->assertDontSee('data-stat="clientes"', false);
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

test('administrators can enable administrator modules', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put('/settings/modules', [
            'modules' => ['users', 'backups'],
        ])
        ->assertRedirect();

    expect($admin->fresh()->enabled_modules)->toBe(['users', 'backups']);
});
