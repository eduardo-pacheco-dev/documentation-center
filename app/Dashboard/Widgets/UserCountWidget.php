<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\Widget;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Total number of registered user accounts.
 */
class UserCountWidget extends Widget
{
    public function id(): string
    {
        return 'core.users';
    }

    public function title(): string
    {
        return 'Usuários';
    }

    public function description(): ?string
    {
        return 'Total de contas cadastradas no sistema.';
    }

    public function icon(): ?string
    {
        return 'users';
    }

    public function view(): string
    {
        return 'admin.widgets.user-count';
    }

    public function order(): int
    {
        return 10;
    }

    public function visible(Request $request): bool
    {
        return (bool) $request->user()?->is_admin;
    }

    public function data(Request $request): array
    {
        return [
            'count' => User::query()->count(),
        ];
    }
}
