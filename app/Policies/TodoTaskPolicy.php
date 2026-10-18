<?php

namespace App\Policies;

use App\Models\TodoTask;
use App\Models\User;

class TodoTaskPolicy
{
    /**
     * Determine whether the user can list tasks.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, TodoTask $todoTask): bool
    {
        return $todoTask->user_id === $user->getKey();
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, TodoTask $todoTask): bool
    {
        return $this->view($user, $todoTask);
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, TodoTask $todoTask): bool
    {
        return $this->view($user, $todoTask);
    }
}
