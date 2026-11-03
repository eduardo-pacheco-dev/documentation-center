<?php

use App\Models\User;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\FollowUp\Database\Seeders\FollowUpSeeder;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpUpdate;
use Plugins\FollowUp\Providers\FollowUpServiceProvider;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginRepository::class);

    $plugin = app(PluginRepository::class)->findOrFail('follow-up');
    $plugin->enable();

    app()->register(FollowUpServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    app(PluginMigrator::class)->migrate($plugin);
});

afterEach(function () {
    File::delete($this->statusesPath);
});

test('guests are redirected from the follow-ups area', function () {
    $this->get('/admin/follow-ups')->assertRedirect('/login');
});

test('the follow-ups plugin appears in the navigation', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.follow-ups.index'));
});

test('follow-ups can be listed, searched and filtered', function () {
    $user = User::factory()->create();

    FollowUp::factory()->create([
        'title' => 'Retornar contato com o fornecedor',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::High->value,
    ]);

    FollowUp::factory()->create([
        'title' => 'Relatório mensal',
        'status' => FollowUpStatus::Completed->value,
        'priority' => FollowUpPriority::Low->value,
    ]);

    $this->actingAs($user)
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Retornar contato com o fornecedor')
        ->assertSee('Relatório mensal');

    $this->actingAs($user)
        ->get('/admin/follow-ups?search=fornecedor')
        ->assertOk()
        ->assertSee('Retornar contato com o fornecedor')
        ->assertDontSee('Relatório mensal');

    $this->actingAs($user)
        ->get('/admin/follow-ups?status=pendente&priority=alta')
        ->assertOk()
        ->assertSee('Retornar contato com o fornecedor')
        ->assertDontSee('Relatório mensal');
});

test('the create page renders the follow-up form', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups/create')
        ->assertOk()
        ->assertSee('Novo follow-up')
        ->assertSee('name="title"', false)
        ->assertSee('name="status"', false)
        ->assertSee('name="priority"', false)
        ->assertSee('name="due_date"', false)
        ->assertSee('name="assigned_to"', false);
});

test('a follow-up can be created with its initial timeline entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/follow-ups', [
        'title' => 'Acompanhar aprovação da proposta',
        'description' => 'Validar com o jurídico.',
        'status' => FollowUpStatus::InProgress->value,
        'priority' => FollowUpPriority::Urgent->value,
        'due_date' => now()->addDays(3)->toDateString(),
        'assigned_to' => $user->getKey(),
    ]);

    $followUp = FollowUp::query()->sole();

    $response->assertRedirect(route('admin.follow-ups.show', $followUp));

    expect($followUp)
        ->title->toBe('Acompanhar aprovação da proposta')
        ->status->toBe(FollowUpStatus::InProgress)
        ->priority->toBe(FollowUpPriority::Urgent)
        ->assigned_to->toBe($user->getKey())
        ->created_by->toBe($user->getKey())
        ->completed_at->toBeNull();

    expect($followUp->updates()->count())->toBe(1);

    expect($followUp->updates()->first())
        ->body->toBe('Follow-up criado.')
        ->status_to->toBe(FollowUpStatus::InProgress);
});

test('a follow-up created as completed receives a completion date', function () {
    $this->actingAs(User::factory()->create())->post('/admin/follow-ups', [
        'title' => 'Follow-up concluído',
        'status' => FollowUpStatus::Completed->value,
        'priority' => FollowUpPriority::Medium->value,
    ]);

    expect(FollowUp::query()->sole()->completed_at)->not->toBeNull();
});

test('follow-up creation requires title, status and priority', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups', [])
        ->assertSessionHasErrors(['title', 'status', 'priority']);

    expect(FollowUp::query()->count())->toBe(0);
});

test('the follow-up details page renders its data', function () {
    $followUp = FollowUp::factory()->create([
        'title' => 'Detalhe importante',
        'description' => 'Descrição detalhada do acompanhamento.',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::High->value,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.follow-ups.show', $followUp))
        ->assertOk()
        ->assertSee('Detalhe importante')
        ->assertSee('Descrição detalhada do acompanhamento.')
        ->assertSee('Pendente')
        ->assertSee('Prioridade Alta');
});

test('a follow-up can be updated and status changes are recorded', function () {
    $followUp = FollowUp::factory()->create([
        'title' => 'Atualizar acompanhamento',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::Medium->value,
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->put(route('admin.follow-ups.update', $followUp), [
            'title' => 'Atualizar acompanhamento',
            'status' => FollowUpStatus::Completed->value,
            'priority' => FollowUpPriority::High->value,
        ]);

    $response->assertRedirect(route('admin.follow-ups.show', $followUp));

    expect($followUp->fresh())
        ->status->toBe(FollowUpStatus::Completed)
        ->priority->toBe(FollowUpPriority::High)
        ->completed_at->not->toBeNull();

    expect($followUp->updates()->first())
        ->body->toBe('Status alterado de Pendente para Concluído.')
        ->status_from->toBe(FollowUpStatus::Pending)
        ->status_to->toBe(FollowUpStatus::Completed);
});

test('a follow-up can be deleted', function () {
    $followUp = FollowUp::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.follow-ups.destroy', $followUp))
        ->assertRedirect(route('admin.follow-ups.index'));

    expect(FollowUp::query()->find($followUp->getKey()))->toBeNull();
});

test('updates can be added and removed by their author', function () {
    $author = User::factory()->create();
    $followUp = FollowUp::factory()->create();

    $this->actingAs($author)
        ->post(route('admin.follow-ups.updates.store', $followUp), [
            'body' => 'Ligação realizada com o fornecedor.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $update = FollowUpUpdate::query()->sole();

    expect($update)
        ->follow_up_id->toBe($followUp->getKey())
        ->user_id->toBe($author->getKey())
        ->body->toBe('Ligação realizada com o fornecedor.');

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.follow-ups.updates.destroy', [$followUp, $update]))
        ->assertForbidden();

    $this->actingAs($author)
        ->delete(route('admin.follow-ups.updates.destroy', [$followUp, $update]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(FollowUpUpdate::query()->find($update->getKey()))->toBeNull();
});

test('the dashboard shows the signed in user follow-ups', function () {
    $user = User::factory()->create();

    FollowUp::factory()->overdue()->create([
        'title' => 'Follow-up atrasado do usuário',
        'assigned_to' => $user->getKey(),
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Meus follow-ups')
        ->assertSee('Follow-up atrasado do usuário')
        ->assertSee('1 atrasado');
});

test('the seeder creates sample follow-ups once', function () {
    User::factory()->count(3)->create();

    $this->seed(FollowUpSeeder::class);
    $this->seed(FollowUpSeeder::class);

    expect(FollowUp::query()->count())->toBe(8)
        ->and(FollowUpUpdate::query()->count())->toBeGreaterThanOrEqual(8);

    expect(FollowUp::query()->where('status', FollowUpStatus::Completed->value)->count())->toBe(2)
        ->and(FollowUp::query()->where('status', FollowUpStatus::Cancelled->value)->count())->toBe(1);
});
