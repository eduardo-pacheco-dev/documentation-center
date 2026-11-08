<?php

use App\Enums\AdminModule;
use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\Example\Providers\ExampleServiceProvider;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginActivator::class);
    app()->forgetInstance(PluginRepository::class);

    app(PluginRepository::class)->findOrFail('example')->enable();
    app()->register(ExampleServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();
});

afterEach(function () {
    File::delete($this->statusesPath);
});

test('the settings page lists plugin navigation buttons with the core modules', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings')
        ->assertOk()
        ->assertSee('Exemplo de navegação')
        ->assertSee('Módulos');
});

test('users can disable a plugin navigation button', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/settings/modules', ['modules' => []])
        ->assertRedirect()
        ->assertSessionHas('status', 'Módulos atualizados com sucesso.');

    expect($user->fresh()->disabled_modules)->toContain('example.index');

    $this->actingAs($user->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('Exemplo de navegação');
});

test('a plugin button is enabled by default when the user has no preference', function () {
    expect(User::factory()->create()->disabled_modules)->toBeNull();

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Exemplo de navegação');
});

test('users can move a plugin button to the sidebar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/settings/modules', [
            'modules' => ['example.index'],
            'order' => ['dashboard', 'example.index'],
            'sections' => [
                'example.index' => AdminModule::SECTION_SIDEBAR,
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Módulos atualizados com sucesso.');

    $user = $user->fresh();

    expect($user->module_sections)->toBe(['example.index' => AdminModule::SECTION_SIDEBAR])
        ->and($user->module_order)->toBe(['dashboard', 'example.index']);

    $content = $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->getContent();

    expect(strpos($content, 'Exemplo de navegação'))
        ->toBeLessThan(strpos($content, '</nav>'));
});

test('a plugin button stays in the modules menu by default', function () {
    $content = $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->getContent();

    expect(strpos($content, 'Exemplo de navegação'))
        ->toBeGreaterThan(strpos($content, '</nav>'));
});

test('plugin buttons follow the saved order in the sidebar', function () {
    $user = User::factory()->create([
        'module_order' => ['example.index', 'dashboard'],
        'module_sections' => ['example.index' => AdminModule::SECTION_SIDEBAR],
    ]);

    $content = $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->getContent();

    expect(strpos($content, 'Exemplo de navegação'))
        ->toBeLessThan(strpos($content, 'Início'));
});
