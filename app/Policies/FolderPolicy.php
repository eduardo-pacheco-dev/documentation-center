<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Folder $folder): bool
    {
        return $this->owns($user, $folder);
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
    public function update(User $user, Folder $folder): bool
    {
        return $this->owns($user, $folder);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Folder $folder): bool
    {
        return $this->owns($user, $folder);
    }

    /**
     * Whether the user owns the given folder.
     */
    protected function owns(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->getKey();
    }
}
