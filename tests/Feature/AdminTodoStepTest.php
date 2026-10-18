<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds a step to the task checklist', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->post(route('admin.tasks.steps.store', $task), ['title' => 'Passo inicial'])
        ->assertRedirect(route('admin.tasks.index'));

    $this->assertDatabaseHas('todo_steps', [
        'todo_task_id' => $task->getKey(),
        'title' => 'Passo inicial',
        'completed_at' => null,
    ]);
});

it('validates the step payload', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->post(route('admin.tasks.steps.store', $task), ['title' => ''])
        ->assertSessionHasErrors('title');

    $this->assertDatabaseCount('todo_steps', 0);
});

it('renders the task steps on the workspace', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);
    $task = todoTaskIn($list, ['title' => 'Tarefa com etapas']);
    $task->steps()->create(['title' => 'Primeira etapa', 'position' => 0]);

    $this->actingAs($user)
        ->get('/admin/tasks?view=list&list='.$list->getKey())
        ->assertOk()
        ->assertSee('Primeira etapa');
});

it('renames a step', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));
    $step = $task->steps()->create(['title' => 'Original']);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.steps.update', [$task, $step]), ['title' => 'Renomeada'])
        ->assertRedirect();

    expect($step->fresh()->title)->toBe('Renomeada');
});

it('completes and reopens a step', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));
    $step = $task->steps()->create(['title' => 'Passo']);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.steps.update', [$task, $step]), ['completed' => '1'])
        ->assertRedirect();

    expect($step->fresh()->completed_at)->not->toBeNull();

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.steps.update', [$task, $step]), ['completed' => '0'])
        ->assertRedirect();

    expect($step->fresh()->completed_at)->toBeNull();
});

it('deletes a step', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));
    $step = $task->steps()->create(['title' => 'Passo']);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->delete(route('admin.tasks.steps.destroy', [$task, $step]))
        ->assertRedirect();

    $this->assertDatabaseMissing('todo_steps', ['id' => $step->getKey()]);
});

it('returns not found when the step belongs to another task', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);
    $task = todoTaskIn($list);
    $otherTask = todoTaskIn($list);
    $step = $otherTask->steps()->create(['title' => 'Passo de outra tarefa']);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->delete(route('admin.tasks.steps.destroy', [$task, $step]))
        ->assertNotFound();

    $this->assertDatabaseHas('todo_steps', ['id' => $step->getKey()]);
});

it('forbids managing the steps of another user task', function () {
    $owner = User::factory()->create();
    $task = todoTaskIn(todoListFor($owner));

    $this->actingAs(User::factory()->create())
        ->from(route('admin.tasks.index'))
        ->post(route('admin.tasks.steps.store', $task), ['title' => 'Invasora'])
        ->assertForbidden();

    $this->assertDatabaseCount('todo_steps', 0);
});
