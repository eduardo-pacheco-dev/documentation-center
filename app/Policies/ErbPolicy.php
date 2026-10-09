<?php

namespace App\Policies;

use App\Models\Erb;
use App\Models\User;

class ErbPolicy
{
    /**
     * Determine whether the user can list ERBs.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Erb $erb): bool
    {
        return $erb->user_id === $user->getKey();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Erb $erb): bool
    {
        return $this->view($user, $erb);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Erb $erb): bool
    {
        return $this->view($user, $erb);
    }
}
