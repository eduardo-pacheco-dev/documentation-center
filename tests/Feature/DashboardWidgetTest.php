<?php

use App\Dashboard\Widget;
use App\Dashboard\WidgetRegistry;
use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Plugins\Example\Providers\ExampleServiceProvider;
use Tests\Support\ProbeWidget;

uses(RefreshDatabase::class);

test('the dashboard renders the core widgets', function () {
    $admin = User::factory()->admin()->create(['name' => 'Maria Silva']);
    User::factory()->create(['name' => 'Regular Member']);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Usuários')
        ->assertSee('Plugins instalados')
        ->assertSee('Plugins ativos')
        ->assertSee('Usuários recentes')
        ->assertSee('Regular Member');
});

test('the widget registry orders widgets by position and identifier', function () {
    $registry = (new WidgetRegistry)
        ->register(new ProbeWidget('core.last', 30))
        ->register(new ProbeWidget('core.second', 20))
        ->register(new ProbeWidget('core.first', 10))
        ->register(new ProbeWidget('core.third', 30));

    expect($registry->all()->map(fn (Widget $widget): string => $widget->id())->all())
        ->toBe(['core.first', 'core.second', 'core.last', 'core.third']);
});

test('the widget registry replaces a widget with the same identifier', function () {
    $registry = (new WidgetRegistry)
        ->register(new ProbeWidget('core.users', 10))
        ->register(new ProbeWidget('core.users', 99));

    expect($registry->all())->toHaveCount(1)
        ->and($registry->get('core.users')?->order())->toBe(99);
});

test('the widget registry filters widgets that are not visible for the request', function () {
    $request = Request::create('/admin');

    $registry = (new WidgetRegistry)
        ->register(new ProbeWidget('core.visible', 10))
        ->register(new ProbeWidget('core.hidden', 20, isVisible: false));

    expect($registry->forRequest($request)->map(fn (Widget $widget): string => $widget->id())->all())
        ->toBe(['core.visible']);
});

test('an enabled plugin can contribute a dashboard widget', function () {
    $statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $statusesPath]);

    try {
        app()->forgetInstance(PluginActivator::class);
        app()->forgetInstance(PluginRepository::class);

        app(PluginRepository::class)->findOrFail('example')->enable();
        app()->register(ExampleServiceProvider::class);

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Widget de exemplo')
            ->assertSee('Este bloco foi registrado pelo provider do plugin Example.');
    } finally {
        File::delete($statusesPath);
    }
});

test('a disabled plugin does not contribute dashboard widgets', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('Widget de exemplo');
});

test('the dashboard offers an edit mode', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Editar widgets')
        ->assertDontSee('Modo edição');
});

test('edit mode lists the widgets hidden by the user', function () {
    $admin = User::factory()->admin()->create([
        'dashboard_hidden_widgets' => ['core.plugins.installed'],
    ]);

    $this->actingAs($admin)
        ->get('/admin?edit=1')
        ->assertOk()
        ->assertSee('Modo edição')
        ->assertSee('Widgets ocultos')
        ->assertSee('Plugins instalados')
        ->assertSee('Adicionar')
        ->assertDontSee('data-widget="core.plugins.installed"', false)
        ->assertSee('data-widget="core.plugins.active"', false);
});

test('users can hide a dashboard widget', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.widgets.update', ['widget' => 'core.plugins.installed']), ['hidden' => 1])
        ->assertRedirect(route('admin.dashboard', ['edit' => 1]))
        ->assertSessionHas('status', 'Widget removido do painel.');

    expect($admin->fresh()->dashboard_hidden_widgets)->toBe(['core.plugins.installed']);

    $this->actingAs($admin->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('data-widget="core.plugins.installed"', false)
        ->assertSee('data-widget="core.plugins.active"', false);
});

test('users can add a hidden dashboard widget back', function () {
    $admin = User::factory()->admin()->create([
        'dashboard_hidden_widgets' => ['core.plugins.installed'],
    ]);

    $this->actingAs($admin)
        ->put(route('admin.widgets.update', ['widget' => 'core.plugins.installed']), ['hidden' => 0])
        ->assertRedirect(route('admin.dashboard', ['edit' => 1]))
        ->assertSessionHas('status', 'Widget adicionado ao painel.');

    expect($admin->fresh()->dashboard_hidden_widgets)->toBe([]);

    $this->actingAs($admin->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-widget="core.plugins.installed"', false);
});

test('regular users can not hide administrator widgets', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('admin.widgets.update', ['widget' => 'core.users']), ['hidden' => 1])
        ->assertNotFound();

    expect($user->fresh()->dashboard_hidden_widgets)->toBeNull();
});

test('unknown dashboard widgets are rejected', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.widgets.update', ['widget' => 'core.missing']), ['hidden' => 1])
        ->assertNotFound();
});

test('updating a dashboard widget requires the hidden flag', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.widgets.update', ['widget' => 'core.plugins.installed']), [])
        ->assertSessionHasErrors('hidden');
});

test('guests can not edit dashboard widgets', function () {
    $this->put(route('admin.widgets.update', ['widget' => 'core.plugins.installed']), ['hidden' => 1])
        ->assertRedirect('/login');
});

test('the dashboard follows the widget order chosen by the user', function () {
    $admin = User::factory()->admin()->create([
        'dashboard_widget_order' => [
            'core.plugins.active',
            'core.users.recent',
            'core.users',
            'core.plugins.installed',
        ],
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSeeInOrder([
            'data-widget="core.plugins.active"',
            'data-widget="core.users.recent"',
            'data-widget="core.users"',
            'data-widget="core.plugins.installed"',
        ], false);
});

test('new widgets are appended after the widgets ordered by the user', function () {
    $admin = User::factory()->admin()->create([
        'dashboard_widget_order' => ['core.users.recent'],
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSeeInOrder([
            'data-widget="core.users.recent"',
            'data-widget="core.users"',
            'data-widget="core.plugins.installed"',
            'data-widget="core.plugins.active"',
        ], false);
});

test('users can reorder widgets through the drag and drop endpoint', function () {
    $admin = User::factory()->admin()->create();

    $order = [
        'core.users.recent',
        'core.plugins.active',
        'core.users',
        'core.plugins.installed',
    ];

    $this->actingAs($admin)
        ->putJson(route('admin.widgets.order'), ['order' => $order])
        ->assertOk()
        ->assertJson(['order' => $order]);

    expect($admin->fresh()->dashboardWidgetOrder())->toBe($order);

    $this->actingAs($admin->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertSeeInOrder([
            'data-widget="core.users.recent"',
            'data-widget="core.plugins.active"',
            'data-widget="core.users"',
            'data-widget="core.plugins.installed"',
        ], false);
});

test('unknown widget identifiers are discarded from the order', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->putJson(route('admin.widgets.order'), [
            'order' => ['core.missing', 'core.users'],
        ])
        ->assertOk();

    expect($admin->fresh()->dashboardWidgetOrder())->toBe([
        'core.users',
        'core.plugins.installed',
        'core.plugins.active',
        'core.users.recent',
    ]);
});

test('users can resize a widget', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.widgets.layout', ['widget' => 'core.users']), ['span' => 2])
        ->assertRedirect(route('admin.dashboard', ['edit' => 1]))
        ->assertSessionHas('status', 'Largura do widget atualizada.');

    expect($admin->fresh()->dashboardWidgetSpan('core.users'))->toBe(2);

    $this->actingAs($admin->fresh())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-widget="core.users"', false);
});

test('choosing the default width removes the custom span', function () {
    $admin = User::factory()->admin()->create([
        'dashboard_widget_spans' => ['core.users' => 2],
    ]);

    $this->actingAs($admin)
        ->put(route('admin.widgets.layout', ['widget' => 'core.users']), ['span' => 1])
        ->assertRedirect(route('admin.dashboard', ['edit' => 1]));

    expect($admin->fresh()->dashboardWidgetSpans())->toBe([]);
});

test('the widget layout validates the order and span', function (array $payload, string $error) {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.widgets.layout', ['widget' => 'core.users']), $payload)
        ->assertSessionHasErrors($error);
})->with([
    'span too small' => [['span' => 0], 'span'],
    'span too large' => [['span' => 4], 'span'],
]);

test('the widget order validation rejects invalid payloads', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->putJson(route('admin.widgets.order'), ['order' => 'not-an-array'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('order');
});

test('guests can not reorder dashboard widgets', function () {
    $this->putJson(route('admin.widgets.order'), ['order' => []])
        ->assertUnauthorized();
});

test('unknown widgets can not be reordered or resized', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.widgets.layout', ['widget' => 'core.missing']), ['span' => 2])
        ->assertNotFound();
});

test('the edit mode exposes the widget metadata', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin?edit=1')
        ->assertOk()
        ->assertSee('Total de contas cadastradas no sistema.')
        ->assertSee('Arraste para reordenar')
        ->assertSee('Largura');
});
