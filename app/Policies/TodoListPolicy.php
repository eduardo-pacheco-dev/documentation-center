<?php

namespace App\Policies;

use App\Models\TodoList;
use App\Models\User;

class TodoListPolicy
{
    /**
     * Determine whether the user can list task lists.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the task list.
     */
    public function view(User $user, TodoList $todoList): bool
    {
        return $todoList->user_id === $user->getKey();
    }

    /**
     * Determine whether the user can create task lists.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the task list.
     */
    public function update(User $user, TodoList $todoList): bool
    {
        return $this->view($user, $todoList);
    }

    /**
     * Determine whether the user can delete the task list.
     */
    public function delete(User $user, TodoList $todoList): bool
    {
        return $this->view($user, $todoList);
    }
}
