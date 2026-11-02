<?php

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Analytics\Models\AnalyticsSnapshot;
use Plugins\Stations\Models\Station;

uses(RefreshDatabase::class);

it('requires authentication to access analytics', function () {
    $this->get('/admin/analytics')->assertRedirect('/login');
});

it('shows analytics alongside the other modules in the sidebar', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="analytics"', false);
});

it('renders the dashboard with charts and indicators', function () {
    $user = User::factory()->create();
    Project::factory()->count(2)->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('Criações no período')
        ->assertSee('Ordens por status')
        ->assertSee('Tarefas por lista')
        ->assertSee('Formulários por status')
        ->assertSee('analytics-series', false)
        ->assertSee('apexcharts', false)
        ->assertSee('chart.js', false)
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['total'] === 2);
});

it('scopes the indicators to the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Project::factory()->count(2)->create(['user_id' => $user->getKey()]);
    Project::factory()->count(5)->create(['user_id' => $other->getKey()]);

    Document::factory()->count(3)->create(['user_id' => $user->getKey()]);
    Document::factory()->count(7)->create(['user_id' => $other->getKey()]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['total'] === 2
            && $summary['documents']['total'] === 3);
});

it('lets administrators switch to the global scope', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    Project::factory()->create(['user_id' => $admin->getKey()]);
    Project::factory()->count(3)->create(['user_id' => $other->getKey()]);

    $this->actingAs($admin)
        ->get('/admin/analytics?scope=global')
        ->assertOk()
        ->assertViewHas('isGlobal', true)
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['total'] === 4);
});

it('forbids regular users from using the global scope', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/analytics?scope=global')
        ->assertForbidden();
});

it('includes the stations indicators when the stations plugin is enabled', function () {
    $user = User::factory()->create();

    $station = Station::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => is_array($summary['stations'])
            && $summary['stations']['total'] === 1
            && $summary['stations']['active'] === 1);

    $station->forceDelete();
});

it('applies the selected period to the indicators', function () {
    $user = User::factory()->create();

    Project::factory()->create([
        'user_id' => $user->getKey(),
        'created_at' => now()->subDays(40),
    ]);

    Project::factory()->create([
        'user_id' => $user->getKey(),
        'created_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get('/admin/analytics?period=7')
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['projects']['created'] === 1);
});

it('validates custom period dates', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/analytics?from=2026-02-30&to=2026-03-01')
        ->assertSessionHasErrors('from');
});

it('exports the indicators as csv', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)
        ->get('/admin/analytics/export?period=30')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)->toContain('Indicador,Valor')
        ->toContain('projects.total,1');
});

it('stores a snapshot for the authenticated user', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post('/admin/analytics/snapshot', ['period' => 30])
        ->assertRedirect();

    $snapshot = AnalyticsSnapshot::query()->where('user_id', $user->getKey())->firstOrFail();

    expect($snapshot->metrics['projects']['total'])->toBe(1)
        ->and($snapshot->captured_on)->toBe(now()->toDateString());
});

it('stores global and per-user snapshots through the artisan command', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $this->artisan('analytics:snapshot')->assertSuccessful();

    expect(AnalyticsSnapshot::query()->whereNull('user_id')->exists())->toBeTrue()
        ->and(AnalyticsSnapshot::query()->where('user_id', $user->getKey())->exists())->toBeTrue();
});

it('refreshes the snapshot of the day instead of duplicating it', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->getKey()]);

    $this->artisan('analytics:snapshot', ['--user' => $user->getKey()])->assertSuccessful();
    $this->artisan('analytics:snapshot', ['--user' => $user->getKey()])->assertSuccessful();

    expect(AnalyticsSnapshot::query()->where('user_id', $user->getKey())->count())->toBe(1);
});

it('requires a valid user for the snapshot command', function () {
    $this->artisan('analytics:snapshot', ['--user' => 999999])
        ->expectsOutputToContain('was not found')
        ->assertFailed();
});
