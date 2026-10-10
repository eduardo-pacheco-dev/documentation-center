<?php

namespace App\Policies;

use App\Models\Colaborador;
use App\Models\User;

class ColaboradorPolicy
{
    /**
     * Determine whether the user can list colaboradores.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Colaborador $colaborador): bool
    {
        return $colaborador->user_id === $user->getKey();
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
    public function update(User $user, Colaborador $colaborador): bool
    {
        return $this->view($user, $colaborador);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Colaborador $colaborador): bool
    {
        return $this->view($user, $colaborador);
    }
}
