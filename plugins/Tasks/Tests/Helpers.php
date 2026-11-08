<?php

use App\Models\User;
use Plugins\Tasks\Models\TodoList;
use Plugins\Tasks\Models\TodoTask;

/**
 * Create a to-do list owned by the given user.
 */
function todoListFor(User $user, string $name = 'Tarefas'): TodoList
{
    return TodoList::factory()->create([
        'user_id' => $user->getKey(),
        'name' => $name,
    ]);
}

/**
 * Create a to-do task inside the given list.
 *
 * @param  array<string, mixed>  $attributes
 */
function todoTaskIn(TodoList $list, array $attributes = []): TodoTask
{
    return TodoTask::factory()->create(array_merge([
        'user_id' => $list->user_id,
        'todo_list_id' => $list->getKey(),
    ], $attributes));
}
