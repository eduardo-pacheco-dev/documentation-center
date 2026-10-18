<?php

use App\Enums\TodoColor;
use App\Enums\TodoIcon;
use App\Models\TodoList;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the tasks area', function () {
    $this->get('/admin/tasks')->assertRedirect('/login');
});

it('renders the task workspace for the authenticated user', function () {
    $user = User::factory()->create();
    TodoList::factory()->create(['user_id' => $user->getKey(), 'name' => 'Minha Lista']);

    $this->actingAs($user)
        ->get('/admin/tasks')
        ->assertOk()
        ->assertSee('Tarefas')
        ->assertSee('Minha Lista')
        ->assertSee('data-modal-open="task-list-modal"', false)
        ->assertSee('id="task-list-modal"', false)
        ->assertSee('id="task-detail-modal"', false)
        ->assertSee(route('admin.todo-lists.store'), false);
});

it('renders the empty state when the user has no lists', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/tasks')
        ->assertOk()
        ->assertSee('Crie a sua primeira lista');
});

it('creates a task list owned by the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->post(route('admin.todo-lists.store'), [
            'name' => 'Trabalho',
            'color' => TodoColor::Teal->value,
            'icon' => TodoIcon::Briefcase->value,
        ])
        ->assertRedirect();

    $list = TodoList::query()->sole();

    expect($list->user_id)->toBe($user->getKey())
        ->and($list->name)->toBe('Trabalho')
        ->and($list->color)->toBe(TodoColor::Teal)
        ->and($list->icon)->toBe(TodoIcon::Briefcase);
});

it('validates the task list payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->post(route('admin.todo-lists.store'), ['name' => ''])
        ->assertSessionHasErrors('name');

    $this->assertDatabaseCount('todo_lists', 0);
});

it('updates the task list details', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->create(['user_id' => $user->getKey(), 'name' => 'Antiga']);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->put(route('admin.todo-lists.update', $list), [
            'name' => 'Nova',
            'color' => TodoColor::Rose->value,
            'icon' => TodoIcon::Star->value,
        ])
        ->assertRedirect(route('admin.tasks.index', ['view' => 'list', 'list' => $list->getKey()]));

    $list->refresh();

    expect($list->name)->toBe('Nova')
        ->and($list->color)->toBe(TodoColor::Rose)
        ->and($list->icon)->toBe(TodoIcon::Star);
});

it('forbids updating a task list owned by another user', function () {
    $owner = User::factory()->create();
    $list = TodoList::factory()->create(['user_id' => $owner->getKey()]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.todo-lists.update', $list), [
            'name' => 'Invadida',
            'color' => TodoColor::Indigo->value,
            'icon' => TodoIcon::CheckCircle->value,
        ])
        ->assertForbidden();
});

it('deletes the task list together with its tasks', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->create(['user_id' => $user->getKey()]);
    TodoTask::factory()->count(2)->create([
        'user_id' => $user->getKey(),
        'todo_list_id' => $list->getKey(),
    ]);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->delete(route('admin.todo-lists.destroy', $list))
        ->assertRedirect(route('admin.tasks.index', ['view' => 'my_day']));

    $this->assertSoftDeleted('todo_lists', ['id' => $list->getKey()]);
    $this->assertDatabaseCount('todo_tasks', 0);
});

it('forbids deleting a task list owned by another user', function () {
    $owner = User::factory()->create();
    $list = TodoList::factory()->create(['user_id' => $owner->getKey()]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.todo-lists.destroy', $list))
        ->assertForbidden();
});
