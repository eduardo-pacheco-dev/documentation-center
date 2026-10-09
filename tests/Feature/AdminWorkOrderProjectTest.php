<?php

use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a project from the work order with prefilled data', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'title' => 'Manutenção elétrica do galpão',
        'opened_at' => '2026-01-10',
        'due_at' => '2026-01-24',
        'total' => 1250.50,
    ]);

    $response = $this->actingAs($user)->post(route('admin.work-orders.project.store', $workOrder));

    $project = Project::query()->firstOrFail();

    $response->assertRedirect(route('admin.projects.show', $project));

    expect($project->user_id)->toBe($user->getKey())
        ->and($project->work_order_id)->toBe($workOrder->getKey())
        ->and($project->name)->toBe('Manutenção elétrica do galpão')
        ->and((float) $project->budget)->toBe(1250.50)
        ->and($project->start_date->toDateString())->toBe('2026-01-10')
        ->and($project->finish_date->toDateString())->toBe('2026-01-24')
        ->and($project->calendars()->count())->toBe(1);
});

it('shows the linked project and hides the create button once a project exists', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $project = Project::factory()->create([
        'user_id' => $user->getKey(),
        'work_order_id' => $workOrder->getKey(),
        'name' => 'Projeto Vinculado',
    ]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('Projeto Vinculado')
        ->assertSee(route('admin.projects.show', $project), false)
        ->assertDontSee('Criar projeto');
});

it('does not create a second project for the same work order', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $existing = Project::factory()->create([
        'user_id' => $user->getKey(),
        'work_order_id' => $workOrder->getKey(),
    ]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.project.store', $workOrder))
        ->assertRedirect(route('admin.projects.show', $existing));

    expect(Project::query()->count())->toBe(1);
});

it('forbids a stranger from creating a project for a work order', function () {
    $stranger = User::factory()->create();
    $workOrder = WorkOrder::factory()->create();

    $this->actingAs($stranger)
        ->post(route('admin.work-orders.project.store', $workOrder))
        ->assertForbidden();

    expect(Project::query()->count())->toBe(0);
});
