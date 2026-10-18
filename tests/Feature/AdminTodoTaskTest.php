<?php

use App\Enums\TodoRepeat;
use App\Models\TodoList;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the tasks area', function () {
    $this->get('/admin/tasks')->assertRedirect('/login');
});

it('shows the tasks of My Day by default', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Tarefa de Hoje', 'my_day_on' => today()->toDateString()]);
    todoTaskIn($list, ['title' => 'Tarefa Sem Dia']);

    $this->actingAs($user)
        ->get('/admin/tasks')
        ->assertOk()
        ->assertSee('Tarefa de Hoje')
        ->assertDontSee('Tarefa Sem Dia');
});

it('lists the tasks of the selected list', function () {
    $user = User::factory()->create();
    $listA = todoListFor($user, 'Lista A');
    $listB = todoListFor($user, 'Lista B');

    todoTaskIn($listA, ['title' => 'Tarefa da A']);
    todoTaskIn($listB, ['title' => 'Tarefa da B']);

    $this->actingAs($user)
        ->get('/admin/tasks?view=list&list='.$listA->getKey())
        ->assertOk()
        ->assertSee('Tarefa da A')
        ->assertDontSee('Tarefa da B');
});

it('never exposes tasks owned by another user', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Tarefa Visível']);

    $stranger = User::factory()->create();
    todoTaskIn(todoListFor($stranger), ['title' => 'Tarefa Alheia']);

    $this->actingAs($user)
        ->get('/admin/tasks?view=all')
        ->assertOk()
        ->assertSee('Tarefa Visível')
        ->assertDontSee('Tarefa Alheia');
});

it('filters the tasks by search term', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Comprar cimento']);
    todoTaskIn($list, ['title' => 'Lavar o carro']);

    $this->actingAs($user)
        ->get('/admin/tasks?view=all&search=cimento')
        ->assertOk()
        ->assertSee('Comprar cimento')
        ->assertDontSee('Lavar o carro');
});

it('sorts the tasks by due date', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Vence Depois', 'due_at' => today()->addDays(5)->toDateString()]);
    todoTaskIn($list, ['title' => 'Vence Antes', 'due_at' => today()->toDateString()]);

    $this->actingAs($user)
        ->get('/admin/tasks?view=planned&sort=due')
        ->assertOk()
        ->assertSeeInOrder(['Vence Antes', 'Vence Depois']);
});

it('shows the completed tasks in the completed view', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Tarefa Finalizada', 'completed_at' => now()]);
    todoTaskIn($list, ['title' => 'Tarefa Pendente']);

    $this->actingAs($user)
        ->get('/admin/tasks?view=completed')
        ->assertOk()
        ->assertSee('Tarefa Finalizada')
        ->assertDontSee('Tarefa Pendente');
});

it('lists the tasks that are due today', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Vence Hoje', 'due_at' => today()->toDateString()]);
    todoTaskIn($list, ['title' => 'Vence Amanhã', 'due_at' => today()->addDay()->toDateString()]);

    $this->actingAs($user)
        ->get('/admin/tasks?view=today')
        ->assertOk()
        ->assertSee('Vence Hoje')
        ->assertDontSee('Vence Amanhã');
});

it('lists the important tasks', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Tarefa Importante', 'is_important' => true]);
    todoTaskIn($list, ['title' => 'Tarefa Comum']);

    $this->actingAs($user)
        ->get('/admin/tasks?view=important')
        ->assertOk()
        ->assertSee('Tarefa Importante')
        ->assertDontSee('Tarefa Comum');
});

it('creates a task inside one of the user lists', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->post(route('admin.tasks.store'), [
            'title' => 'Nova tarefa',
            'todo_list_id' => $list->getKey(),
            'due_at' => today()->toDateString(),
            'is_important' => '1',
        ])
        ->assertRedirect(route('admin.tasks.index'));

    $this->assertDatabaseHas('todo_tasks', [
        'user_id' => $user->getKey(),
        'todo_list_id' => $list->getKey(),
        'title' => 'Nova tarefa',
        'is_important' => true,
        'completed_at' => null,
    ]);
});

it('adds a new task to My Day when created from the My Day view', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->post(route('admin.tasks.store'), [
            'title' => 'Planejada',
            'todo_list_id' => $list->getKey(),
            'my_day' => '1',
        ])
        ->assertRedirect();

    $task = TodoTask::query()->sole();

    expect($task->my_day_on?->toDateString())->toBe(today()->toDateString());
});

it('rejects a task created in a list owned by another user', function () {
    $user = User::factory()->create();
    $foreign = TodoList::factory()->create();

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->post(route('admin.tasks.store'), [
            'title' => 'Tarefa invasora',
            'todo_list_id' => $foreign->getKey(),
        ])
        ->assertSessionHasErrors('todo_list_id');

    $this->assertDatabaseCount('todo_tasks', 0);
});

it('updates the task details', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);
    $task = todoTaskIn($list);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->put(route('admin.tasks.update', $task), [
            'title' => 'Atualizada',
            'todo_list_id' => $list->getKey(),
            'notes' => 'Detalhes da tarefa',
            'due_at' => today()->addDays(3)->toDateString(),
            'repeat_type' => TodoRepeat::Monthly->value,
            'is_important' => '1',
            'my_day' => '1',
        ])
        ->assertRedirect(route('admin.tasks.index'));

    $task->refresh();

    expect($task->title)->toBe('Atualizada')
        ->and($task->notes)->toBe('Detalhes da tarefa')
        ->and($task->due_at?->toDateString())->toBe(today()->addDays(3)->toDateString())
        ->and($task->repeat_type)->toBe(TodoRepeat::Monthly)
        ->and($task->is_important)->toBeTrue()
        ->and($task->my_day_on?->toDateString())->toBe(today()->toDateString());
});

it('moves a task to another list when updated', function () {
    $user = User::factory()->create();
    $source = todoListFor($user, 'Origem');
    $target = todoListFor($user, 'Destino');
    $task = todoTaskIn($source, ['position' => 0]);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->put(route('admin.tasks.update', $task), [
            'title' => $task->title,
            'todo_list_id' => $target->getKey(),
        ])
        ->assertRedirect();

    expect($task->fresh()->todo_list_id)->toBe($target->getKey());
});

it('completes and reopens a task', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.toggle', $task))
        ->assertRedirect(route('admin.tasks.index'));

    expect($task->fresh()->completed_at)->not->toBeNull();

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.toggle', $task))
        ->assertRedirect();

    expect($task->fresh()->completed_at)->toBeNull();
});

it('creates the next occurrence when completing a repeating task', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);
    $task = todoTaskIn($list, [
        'title' => 'Relatório semanal',
        'due_at' => '2026-10-19',
        'repeat_type' => TodoRepeat::Weekly,
    ]);
    $task->steps()->create(['title' => 'Coletar dados', 'position' => 0]);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.toggle', $task))
        ->assertRedirect();

    $next = TodoTask::query()
        ->where('title', 'Relatório semanal')
        ->whereNull('completed_at')
        ->sole();

    expect($next->due_at?->toDateString())->toBe('2026-10-26')
        ->and($next->repeat_type)->toBe(TodoRepeat::Weekly)
        ->and($next->steps()->count())->toBe(1)
        ->and($next->steps()->first()->title)->toBe('Coletar dados');
});

it('does not create an occurrence when completing a one-off task', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user), [
        'repeat_type' => TodoRepeat::None,
        'due_at' => today()->toDateString(),
    ]);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.toggle', $task))
        ->assertRedirect();

    $this->assertDatabaseCount('todo_tasks', 1);
});

it('toggles the important flag of a task', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user), ['is_important' => false]);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.important', $task))
        ->assertRedirect();

    expect($task->fresh()->is_important)->toBeTrue();
});

it('toggles the task in My Day', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.my-day', $task))
        ->assertRedirect();

    expect($task->fresh()->isMyDay())->toBeTrue();

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.my-day', $task))
        ->assertRedirect();

    expect($task->fresh()->isMyDay())->toBeFalse();
});

it('moves a task to another list', function () {
    $user = User::factory()->create();
    $source = todoListFor($user, 'Origem');
    $target = todoListFor($user, 'Destino');
    $task = todoTaskIn($source);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.move', $task), ['todo_list_id' => $target->getKey()])
        ->assertRedirect();

    expect($task->fresh()->todo_list_id)->toBe($target->getKey());
});

it('reorders a task within its list', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);
    $first = todoTaskIn($list, ['title' => 'Primeira', 'position' => 0]);
    $second = todoTaskIn($list, ['title' => 'Segunda', 'position' => 1]);

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->patch(route('admin.tasks.move', $second), ['direction' => 'up'])
        ->assertRedirect();

    expect($second->fresh()->position)->toBe(0)
        ->and($first->fresh()->position)->toBe(1);
});

it('deletes a task', function () {
    $user = User::factory()->create();
    $task = todoTaskIn(todoListFor($user));

    $this->actingAs($user)
        ->from(route('admin.tasks.index'))
        ->delete(route('admin.tasks.destroy', $task))
        ->assertRedirect(route('admin.tasks.index'));

    $this->assertDatabaseMissing('todo_tasks', ['id' => $task->getKey()]);
});

it('forbids updating a task owned by another user', function () {
    $owner = User::factory()->create();
    $task = todoTaskIn(todoListFor($owner));

    $this->actingAs(User::factory()->create())
        ->put(route('admin.tasks.update', $task), [
            'title' => 'Invadida',
            'todo_list_id' => $task->todo_list_id,
        ])
        ->assertForbidden();
});

it('forbids toggling a task owned by another user', function () {
    $owner = User::factory()->create();
    $task = todoTaskIn(todoListFor($owner));

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.tasks.toggle', $task))
        ->assertForbidden();
});

it('forbids deleting a task owned by another user', function () {
    $owner = User::factory()->create();
    $task = todoTaskIn(todoListFor($owner));

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.tasks.destroy', $task))
        ->assertForbidden();
});
