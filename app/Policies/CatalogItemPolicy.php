<?php

namespace App\Policies;

use App\Models\CatalogItem;
use App\Models\User;

class CatalogItemPolicy
{
    /**
     * Determine whether the user can list catalog items.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CatalogItem $catalogItem): bool
    {
        return $catalogItem->user_id === $user->getKey();
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
    public function update(User $user, CatalogItem $catalogItem): bool
    {
        return $this->view($user, $catalogItem);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CatalogItem $catalogItem): bool
    {
        return $this->view($user, $catalogItem);
    }
}
