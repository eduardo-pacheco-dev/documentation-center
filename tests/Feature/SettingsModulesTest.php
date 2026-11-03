<?php

use App\Enums\AdminModule;
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

test('administrators can save the module order', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put('/settings/modules', [
            'modules' => ['users'],
            'order' => ['users', 'dashboard'],
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Módulos atualizados com sucesso.');

    expect($admin->fresh()->module_order)->toBe(['users', 'dashboard']);
});

test('the module order only accepts available modules', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put('/settings/modules', [
            'modules' => ['users'],
            'order' => ['missing-module'],
        ])
        ->assertSessionHasErrors('order.0');

    expect($admin->fresh()->module_order)->toBeNull();
});

test('the enabled modules follow the saved order', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'module_order' => ['users', 'dashboard'],
    ]);

    $values = array_map(
        fn (AdminModule $module): string => $module->value,
        AdminModule::enabledFor($admin),
    );

    expect($values)->toBe(['users', 'dashboard', 'dev-docs']);
});

test('administrators can move modules between the sidebar and the modules menu', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put('/settings/modules', [
            'modules' => ['users'],
            'order' => ['dashboard', 'users'],
            'sections' => [
                'dashboard' => AdminModule::SECTION_MODULES,
                'users' => AdminModule::SECTION_SIDEBAR,
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Módulos atualizados com sucesso.');

    $admin = $admin->fresh();

    expect($admin->module_sections)->toBe([
        'dashboard' => AdminModule::SECTION_MODULES,
        'users' => AdminModule::SECTION_SIDEBAR,
    ])->and(AdminModule::sectionFor($admin, AdminModule::Dashboard))->toBe(AdminModule::SECTION_MODULES)
        ->and(AdminModule::sectionFor($admin, AdminModule::Users))->toBe(AdminModule::SECTION_SIDEBAR);
});

test('the module section only accepts the available groups', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put('/settings/modules', [
            'modules' => ['users'],
            'sections' => [
                'users' => 'floating',
            ],
        ])
        ->assertSessionHasErrors('sections.users');

    expect($admin->fresh()->module_sections)->toBeNull();
});
