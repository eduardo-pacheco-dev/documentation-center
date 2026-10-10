<?php

namespace App\Policies;

use App\Models\RadioLink;
use App\Models\User;

class RadioLinkPolicy
{
    /**
     * Determine whether the user can list radio links.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RadioLink $radioLink): bool
    {
        return $radioLink->user_id === $user->getKey();
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
    public function update(User $user, RadioLink $radioLink): bool
    {
        return $this->view($user, $radioLink);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RadioLink $radioLink): bool
    {
        return $this->view($user, $radioLink);
    }
}
