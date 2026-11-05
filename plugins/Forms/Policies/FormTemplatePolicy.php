<?php

namespace Plugins\Forms\Policies;

use App\Models\User;
use Plugins\Forms\Models\FormTemplate;

class FormTemplatePolicy
{
    /**
     * Determine whether the user can list templates.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the template.
     */
    public function view(User $user, FormTemplate $template): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create templates.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the template.
     */
    public function update(User $user, FormTemplate $template): bool
    {
        return $template->user_id === $user->getKey() || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the template.
     */
    public function delete(User $user, FormTemplate $template): bool
    {
        return $template->user_id === $user->getKey() || $user->is_admin;
    }
}
