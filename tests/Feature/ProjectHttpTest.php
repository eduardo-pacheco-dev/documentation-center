<?php

use App\Enums\ProjectRole;
use App\Models\Baseline;
use App\Models\Calendar;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Task;
use App\Models\User;
use App\Services\Scheduling\ProjectScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the projects area', function () {
    $this->get('/admin/projects')->assertRedirect('/login');
});

it('creates a project with its default calendar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/projects', [
        'name' => 'Lançamento da loja',
        'description' => 'Loja de e-commerce',
        'start_date' => '2026-03-02',
        'status' => 'active',
        'priority' => 700,
        'currency' => 'BRL',
        'budget' => 25000,
    ])->assertRedirect();

    $project = Project::query()->where('name', 'Lançamento da loja')->firstOrFail();

    expect($project->user_id)->toBe($user->getKey())
        ->and($project->start_date->toDateString())->toBe('2026-03-02')
        ->and($project->budget)->toEqual(25000.0)
        ->and(Calendar::query()->where('project_id', $project->getKey())->where('is_default', true)->exists())->toBeTrue();
});

it('lists the projects the user owns or belongs to', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $owned = Project::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Meu projeto']);
    $shared = Project::factory()->create(['name' => 'Projeto compartilhado']);
    ProjectMember::query()->create(['project_id' => $shared->getKey(), 'user_id' => $member->getKey(), 'role' => 'viewer']);
    Project::factory()->create(['name' => 'Projeto alheio']);

    $this->actingAs($member)
        ->get('/admin/projects')
        ->assertOk()
        ->assertSee('Projeto compartilhado')
        ->assertDontSee('Projeto alheio');

    $this->actingAs($owner)
        ->get('/admin/projects')
        ->assertOk()
        ->assertSee('Meu projeto');
});

it('forbids a stranger from opening the project', function () {
    $stranger = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($stranger)->get("/admin/projects/{$project->getKey()}")->assertForbidden();
});

it('gives editors the plan but only the owner can delete', function () {
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->getKey()]);
    ProjectMember::query()->create(['project_id' => $project->getKey(), 'user_id' => $editor->getKey(), 'role' => 'editor']);

    $this->actingAs($editor)
        ->get("/admin/projects/{$project->getKey()}")
        ->assertOk();

    $this->put("/admin/projects/{$project->getKey()}", ['name' => 'Renomeado', 'start_date' => '2026-01-05'])
        ->assertRedirect();

    expect($project->refresh()->name)->toBe('Renomeado');

    $this->actingAs($editor)
        ->delete("/admin/projects/{$project->getKey()}")
        ->assertForbidden();

    $this->actingAs($owner)
        ->delete("/admin/projects/{$project->getKey()}")
        ->assertRedirect(route('admin.projects.index'));

    expect($project->refresh()->trashed())->toBeTrue();
});

it('creates a task through the web and schedules it', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/projects/{$project->getKey()}/tasks", [
            'name' => 'Fundação',
            'duration_days' => 1,
            'priority' => 500,
        ])
        ->assertRedirect();

    $task = $project->tasks()->firstOrFail();

    expect($task->wbs)->toBe('1')
        ->and($task->outline_level)->toBe(1)
        ->and($task->duration_minutes)->toBe(480)
        ->and($task->start_at->format('Y-m-d H:i'))->toBe('2026-01-05 08:00');
});

it('updates a task and moves the successors by the new duration', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $first = Task::factory()->for($project)->create(['duration_minutes' => 480, 'sort_order' => 1]);
    $second = Task::factory()->for($project)->create(['duration_minutes' => 480, 'sort_order' => 2]);
    finishToStart($first, $second);
    app(ProjectScheduler::class)->schedule($project);

    $this->actingAs($user)
        ->put("/admin/projects/{$project->getKey()}/tasks/{$second->getKey()}", [
            'name' => 'Alvenaria alongada',
            'duration_days' => 2,
        ])
        ->assertRedirect();

    expect($second->refresh()->duration_minutes)->toBe(960)
        ->and($second->start_at->format('Y-m-d H:i'))->toBe('2026-01-06 08:00')
        ->and($second->finish_at->format('Y-m-d H:i'))->toBe('2026-01-07 17:00');
});

it('deletes a task together with its subtree', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $summary = Task::factory()->summary()->for($project)->create();
    $child = Task::factory()->for($project)->create(['parent_id' => $summary->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/projects/{$project->getKey()}/tasks/{$summary->getKey()}")
        ->assertRedirect();

    expect($project->tasks()->count())->toBe(0)
        ->and(Task::query()->find($child->getKey()))->toBeNull();
});

it('rejects a link pointing to a task of another project', function () {
    $user = User::factory()->create();
    $projectAs = workingProject();
    $projectAs->update(['user_id' => $user->getKey()]);
    $projectB = workingProject();
    $taskA = Task::factory()->for($projectAs)->create();
    $taskB = Task::factory()->for($projectB)->create();

    $this->actingAs($user)
        ->from("/admin/projects/{$projectAs->getKey()}")
        ->post("/admin/projects/{$projectAs->getKey()}/dependencies", [
            'predecessor_id' => $taskB->getKey(),
            'successor_id' => $taskA->getKey(),
            'type' => 'fs',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('predecessor_id');

    expect($projectAs->dependencies()->count())->toBe(0);
});

it('rejects a dependency that would create a cycle', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $first = Task::factory()->for($project)->create();
    $second = Task::factory()->for($project)->create();
    finishToStart($first, $second);

    $this->actingAs($user)
        ->from("/admin/projects/{$project->getKey()}")
        ->post("/admin/projects/{$project->getKey()}/dependencies", [
            'predecessor_id' => $second->getKey(),
            'successor_id' => $first->getKey(),
            'type' => 'fs',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('successor_id');

    expect($project->dependencies()->count())->toBe(1);
});

it('manages resources of the project', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/projects/{$project->getKey()}/resources", [
            'name' => 'Ana Martins',
            'type' => 'work',
            'code' => 'ANA',
            'max_units' => 100,
            'cost_per_hour' => 65,
        ])
        ->assertRedirect();

    $resource = $project->resources()->firstOrFail();

    expect($resource->name)->toBe('Ana Martins')
        ->and((float) $resource->cost_per_hour)->toBe(65.0);

    $this->actingAs($user)
        ->put("/admin/projects/{$project->getKey()}/resources/{$resource->getKey()}", [
            'name' => 'Ana Maria',
            'cost_per_hour' => 70,
        ])
        ->assertRedirect();

    expect($resource->refresh()->name)->toBe('Ana Maria')
        ->and((float) $resource->cost_per_hour)->toBe(70.0);
});

it('charges the resource rate to the task on assignment', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480]);
    $resource = Resource::factory()->for($project)->create(['cost_per_hour' => 120]);

    $this->actingAs($user)
        ->post("/admin/projects/{$project->getKey()}/assignments", [
            'task_id' => $task->getKey(),
            'resource_id' => $resource->getKey(),
            'units' => 100,
        ])
        ->assertRedirect();

    $assignment = ResourceAssignment::query()
        ->where('task_id', $task->getKey())
        ->where('resource_id', $resource->getKey())
        ->firstOrFail();

    expect((float) $assignment->cost)->toBe(960.0)
        ->and($assignment->work_minutes)->toBe(480);
});

it('only the owner can change the members of the project', function () {
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $invited = User::factory()->create(['email' => 'convidado@exemplo.com']);
    $project = workingProject();
    $project->update(['user_id' => $owner->getKey()]);
    ProjectMember::query()->create(['project_id' => $project->getKey(), 'user_id' => $editor->getKey(), 'role' => 'editor']);

    $this->actingAs($editor)
        ->from("/admin/projects/{$project->getKey()}")
        ->post("/admin/projects/{$project->getKey()}/members", [
            'email' => $invited->email,
            'role' => 'viewer',
        ])
        ->assertForbidden();

    $this->actingAs($owner)
        ->post("/admin/projects/{$project->getKey()}/members", [
            'email' => $invited->email,
            'role' => 'viewer',
        ])
        ->assertRedirect();

    expect(ProjectMember::query()->where('project_id', $project->getKey())->where('user_id', $invited->getKey())->exists())->toBeTrue();

    $member = ProjectMember::query()->where('project_id', $project->getKey())->where('user_id', $invited->getKey())->firstOrFail();

    $this->actingAs($owner)
        ->put("/admin/projects/{$project->getKey()}/members/{$member->getKey()}", ['role' => 'editor'])
        ->assertRedirect();

    expect($member->refresh()->role)->toBe(ProjectRole::Editor);

    $this->actingAs($owner)
        ->delete("/admin/projects/{$project->getKey()}/members/{$member->getKey()}")
        ->assertRedirect();

    expect(ProjectMember::query()->find($member->getKey()))->toBeNull();
});

it('saves a baseline and restores it into the plan', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480]);
    app(ProjectScheduler::class)->schedule($project);
    $baselineStart = $task->refresh()->start_at;

    $this->actingAs($user)
        ->post("/admin/projects/{$project->getKey()}/baselines", ['name' => 'Aprovado pela diretoria'])
        ->assertRedirect();

    $baseline = Baseline::query()->where('project_id', $project->getKey())->firstOrFail();
    expect($baseline->tasks()->count())->toBe(1);

    $this->actingAs($user)
        ->put("/admin/projects/{$project->getKey()}/tasks/{$task->getKey()}", [
            'name' => $task->name,
            'duration_days' => 4,
        ])
        ->assertRedirect();

    expect($task->refresh()->duration_minutes)->toBe(1920);

    $this->actingAs($user)
        ->post("/admin/projects/{$project->getKey()}/baselines/{$baseline->getKey()}/restore")
        ->assertRedirect();

    expect($task->refresh()->duration_minutes)->toBe(480)
        ->and($task->start_at->toDateTimeString())->toBe($baselineStart->toDateTimeString());
});

it('recalculates and levels the schedule from the web', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480]);

    $this->actingAs($user)
        ->post("/admin/projects/{$project->getKey()}/schedule")
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($task->refresh()->start_at->format('Y-m-d H:i'))->toBe('2026-01-05 08:00');

    $this->actingAs($user)
        ->post("/admin/projects/{$project->getKey()}/level")
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($project->refresh()->scheduled_at)->not->toBeNull();
});

it('records task progress through the web and clamps it', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480]);

    $this->actingAs($user)
        ->patch("/admin/projects/{$project->getKey()}/tasks/{$task->getKey()}/progress", [
            'percent_complete' => 150,
            'actual_start_at' => '2026-01-05 08:00',
        ])
        ->assertRedirect();

    expect($task->refresh()->percent_complete)->toEqual(100.0)
        ->and($task->actual_start_at)->not->toBeNull();
});

it('feeds the gantt chart and applies a drag', function () {
    $user = User::factory()->create();
    $project = workingProject();
    $project->update(['user_id' => $user->getKey()]);
    $first = Task::factory()->for($project)->create(['name' => 'Marcar', 'duration_minutes' => 480, 'sort_order' => 1]);
    $second = Task::factory()->for($project)->create(['name' => 'Desenhar', 'duration_minutes' => 480, 'sort_order' => 2]);
    finishToStart($first, $second);
    app(ProjectScheduler::class)->schedule($project);

    $this->actingAs($user)
        ->get("/admin/projects/{$project->getKey()}/gantt")
        ->assertOk()
        ->assertJsonPath('data.0.text', $first->name)
        ->assertJsonPath('links.0.source', $first->getKey())
        ->assertJsonPath('links.0.target', $second->getKey());

    $this->actingAs($user)
        ->put("/admin/projects/{$project->getKey()}/gantt/tasks/{$second->getKey()}", [
            'start_date' => '2026-01-12 08:00',
            'duration_days' => 3,
        ])
        ->assertOk();

    $second->refresh();

    expect($second->duration_minutes)->toBe(1440)
        ->and($second->constraint_type->value)->toBe('start_no_earlier_than')
        ->and($second->constraint_date->format('Y-m-d'))->toBe('2026-01-12');
});
