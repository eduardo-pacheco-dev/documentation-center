<?php

use App\Models\User;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\TeamTracking\Database\Seeders\TeamTrackingSeeder;
use Plugins\TeamTracking\Enums\CheckInStatus;
use Plugins\TeamTracking\Enums\TeamStatus;
use Plugins\TeamTracking\Models\Team;
use Plugins\TeamTracking\Models\TeamCheckIn;
use Plugins\TeamTracking\Providers\TeamTrackingServiceProvider;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginRepository::class);

    $plugin = app(PluginRepository::class)->findOrFail('team-tracking');
    $plugin->enable();

    app()->register(TeamTrackingServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    app(PluginMigrator::class)->migrate($plugin);
});

afterEach(function () {
    File::delete($this->statusesPath);
});

test('guests are redirected from the teams area', function () {
    $this->get('/admin/teams')->assertRedirect('/login');
});

test('the team tracking plugin appears in the navigation', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.teams.index'));
});

test('teams can be listed, searched and filtered', function () {
    $user = User::factory()->create();

    Team::factory()->create(['name' => 'Equipe Alfa', 'area' => 'Operações', 'status' => TeamStatus::Active->value]);
    Team::factory()->create(['name' => 'Equipe Beta', 'area' => 'Comercial', 'status' => TeamStatus::Paused->value]);

    $this->actingAs($user)
        ->get('/admin/teams')
        ->assertOk()
        ->assertSee('Equipe Alfa')
        ->assertSee('Equipe Beta');

    $this->actingAs($user)
        ->get('/admin/teams?search=Alfa&status=ativa')
        ->assertOk()
        ->assertSee('Equipe Alfa')
        ->assertDontSee('Equipe Beta');
});

test('a team can be created and requires a unique name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/teams', [
            'name' => 'Equipe Nova',
            'description' => 'Equipe de teste.',
            'area' => 'Operações',
            'leader_id' => $user->getKey(),
            'status' => TeamStatus::Active->value,
            'started_at' => now()->toDateString(),
        ])
        ->assertRedirect();

    $team = Team::query()->sole();

    expect($team)
        ->name->toBe('Equipe Nova')
        ->area->toBe('Operações')
        ->leader_id->toBe($user->getKey())
        ->status->toBe(TeamStatus::Active);

    $this->actingAs($user)
        ->post('/admin/teams', [
            'name' => 'Equipe Nova',
            'status' => TeamStatus::Active->value,
        ])
        ->assertSessionHasErrors('name');
});

test('team creation requires a name and status', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/teams', [])
        ->assertSessionHasErrors(['name', 'status']);

    expect(Team::query()->count())->toBe(0);
});

test('a team can be displayed and updated', function () {
    $team = Team::factory()->create([
        'name' => 'Equipe Exibida',
        'description' => 'Descrição da equipe.',
        'area' => 'TI',
        'status' => TeamStatus::Active->value,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.teams.show', $team))
        ->assertOk()
        ->assertSee('Equipe Exibida')
        ->assertSee('Descrição da equipe.')
        ->assertSee('Membros')
        ->assertSee('Acompanhamentos');

    $this->actingAs(User::factory()->create())
        ->put(route('admin.teams.update', $team), [
            'name' => 'Equipe Atualizada',
            'description' => 'Nova descrição.',
            'area' => 'TI',
            'status' => TeamStatus::Paused->value,
        ])
        ->assertRedirect();

    expect($team->fresh())
        ->name->toBe('Equipe Atualizada')
        ->status->toBe(TeamStatus::Paused);
});

test('members can be added and removed from a team', function () {
    $team = Team::factory()->create();
    $member = User::factory()->create(['name' => 'Membro Teste']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.teams.members.store', $team), [
            'user_id' => $member->getKey(),
            'role' => 'membro',
            'joined_at' => now()->toDateString(),
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($team->members()->where('users.id', $member->getKey())->exists())->toBeTrue();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.teams.members.store', $team), [
            'user_id' => $member->getKey(),
            'role' => 'membro',
        ])
        ->assertSessionHasErrors('user_id');

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.teams.members.destroy', [$team, $member]))
        ->assertRedirect();

    expect($team->members()->where('users.id', $member->getKey())->exists())->toBeFalse();
});

test('check-ins can be created, updated and removed', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.teams.check-ins.store', $team), [
            'title' => 'Check-in semanal',
            'reference_date' => now()->toDateString(),
            'status' => CheckInStatus::OnTrack->value,
            'health_score' => 4,
            'summary' => 'Equipe bem alinhada.',
            'next_steps' => 'Manter o ritmo.',
        ])
        ->assertRedirect();

    $checkIn = TeamCheckIn::query()->sole();

    expect($checkIn)
        ->team_id->toBe($team->getKey())
        ->user_id->toBe($user->getKey())
        ->status->toBe(CheckInStatus::OnTrack)
        ->health_score->toBe(4);

    $this->actingAs($user)
        ->put(route('admin.teams.check-ins.update', [$team, $checkIn]), [
            'title' => 'Check-in atualizado',
            'reference_date' => now()->toDateString(),
            'status' => CheckInStatus::Attention->value,
            'health_score' => 2,
            'summary' => 'Atenção necessária.',
        ])
        ->assertRedirect(route('admin.teams.show', $team));

    expect($checkIn->fresh())
        ->title->toBe('Check-in atualizado')
        ->status->toBe(CheckInStatus::Attention)
        ->health_score->toBe(2);

    $this->actingAs($user)
        ->delete(route('admin.teams.check-ins.destroy', [$team, $checkIn]))
        ->assertRedirect();

    expect(TeamCheckIn::query()->count())->toBe(0);
});

test('check-in health score is limited to one through five', function () {
    $team = Team::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.teams.check-ins.store', $team), [
            'title' => 'Inválido',
            'reference_date' => now()->toDateString(),
            'status' => CheckInStatus::OnTrack->value,
            'health_score' => 6,
            'summary' => 'Nota inválida.',
        ])
        ->assertSessionHasErrors('health_score');
});

test('a team can be deleted with its members and check-ins', function () {
    $team = Team::factory()->create();
    $member = User::factory()->create();
    $team->members()->attach($member->getKey(), ['role' => 'membro']);

    TeamCheckIn::factory()->for($team)->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.teams.destroy', $team))
        ->assertRedirect(route('admin.teams.index'));

    expect(Team::query()->find($team->getKey()))->toBeNull()
        ->and(TeamCheckIn::query()->count())->toBe(0);
});

test('the dashboard shows active teams', function () {
    Team::factory()->create(['name' => 'Equipe no Dashboard', 'status' => TeamStatus::Active->value]);

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Equipe no Dashboard')
        ->assertSee('Equipes');
});

test('the seeder creates sample teams once', function () {
    User::factory()->count(4)->create();

    $this->seed(TeamTrackingSeeder::class);
    $this->seed(TeamTrackingSeeder::class);

    expect(Team::query()->count())->toBe(3)
        ->and(TeamCheckIn::query()->count())->toBe(3);
});
