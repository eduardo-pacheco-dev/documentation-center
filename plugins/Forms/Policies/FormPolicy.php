<?php

namespace Plugins\Forms\Policies;

use App\Models\User;
use Plugins\Forms\Models\Form;

class FormPolicy
{
    /**
     * Determine whether the user can list forms.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Form $form): bool
    {
        return $form->user_id === $user->getKey();
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
    public function update(User $user, Form $form): bool
    {
        return $this->view($user, $form);
    }

    /**
     * Determine whether the user can evaluate a form response.
     */
    public function evaluate(User $user, Form $form): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Form $form): bool
    {
        return $this->view($user, $form);
    }
}
