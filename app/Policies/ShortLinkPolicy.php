<?php

namespace App\Policies;

use App\Models\ShortLink;
use App\Models\User;

class ShortLinkPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ShortLink $shortLink): bool
    {
        return $this->owns($user, $shortLink);
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
    public function update(User $user, ShortLink $shortLink): bool
    {
        return $this->owns($user, $shortLink);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShortLink $shortLink): bool
    {
        return $this->owns($user, $shortLink);
    }

    /**
     * Whether the user owns the given link.
     */
    protected function owns(User $user, ShortLink $shortLink): bool
    {
        return $shortLink->isOwnedBy($user);
    }
}
