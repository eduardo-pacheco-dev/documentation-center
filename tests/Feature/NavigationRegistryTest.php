<?php

use App\Models\User;
use App\Navigation\NavigationItem;
use App\Navigation\NavigationRegistry;
use App\Plugins\PluginActivator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\Example\Providers\ExampleServiceProvider;

uses(RefreshDatabase::class);

test('the registry orders items and replaces duplicated identifiers', function () {
    $registry = (new NavigationRegistry)
        ->register(NavigationItem::make('acme.second', 'Second', 'admin.dashboard')->order(20))
        ->register(NavigationItem::make('acme.first', 'First', 'admin.dashboard')->order(10))
        ->register(NavigationItem::make('acme.second', 'Second updated', 'admin.dashboard')->order(5));

    expect($registry->all()->map(fn (NavigationItem $item): string => $item->label())->all())
        ->toBe(['Second updated', 'First']);
});

test('a plugin can add a button to the sidebar', function () {
    app(NavigationRegistry::class)->register(
        NavigationItem::make('acme.team', 'Equipe Acme', 'admin.users.index')->inSidebar(),
    );

    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Equipe Acme')
        ->assertSee(route('admin.users.index'));
});

test('a plugin can add a button to the modules menu', function () {
    app(NavigationRegistry::class)->register(
        NavigationItem::make('acme.reports', 'Relatórios Acme', 'admin.dashboard')->inModules(),
    );

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Relatórios Acme');
});

test('items pointing to unknown routes are ignored', function () {
    app(NavigationRegistry::class)->register(
        NavigationItem::make('acme.missing', 'Rota inexistente', 'acme.missing')->inSidebar(),
    );

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('Rota inexistente');
});

test('items can be restricted to an admin module', function () {
    app(NavigationRegistry::class)->register(
        NavigationItem::make('acme.users', 'Usuários Acme', 'admin.users.index')
            ->inSidebar()
            ->module('users'),
    );

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('Usuários Acme');

    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Usuários Acme');
});

test('items can be hidden with a visibility callback', function () {
    app(NavigationRegistry::class)->register(
        NavigationItem::make('acme.secret', 'Área secreta', 'admin.dashboard')
            ->inSidebar()
            ->visible(fn (): bool => false),
    );

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('Área secreta');
});

test('an enabled plugin registers its sidebar button through the hook', function () {
    $statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $statusesPath]);

    try {
        app()->forgetInstance(PluginActivator::class);
        app()->forgetInstance(PluginRepository::class);

        app(PluginRepository::class)->findOrFail('example')->enable();
        app()->register(ExampleServiceProvider::class);
        Route::getRoutes()->refreshNameLookups();

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Exemplo de navegação')
            ->assertSee(route('plugins.example.index'));
    } finally {
        File::delete($statusesPath);
    }
});

test('a disabled plugin does not register a sidebar button', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('Exemplo de navegação');
});
