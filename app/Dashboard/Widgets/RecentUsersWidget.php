<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\Widget;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * The latest accounts created in the application.
 */
class RecentUsersWidget extends Widget
{
    public function id(): string
    {
        return 'core.users.recent';
    }

    public function title(): string
    {
        return 'Usuários recentes';
    }

    public function description(): ?string
    {
        return 'As últimas contas criadas na aplicação.';
    }

    public function icon(): ?string
    {
        return 'clock';
    }

    public function view(): string
    {
        return 'admin.widgets.recent-users';
    }

    public function order(): int
    {
        return 40;
    }

    public function columnSpan(): int
    {
        return 3;
    }

    public function visible(Request $request): bool
    {
        return (bool) $request->user()?->is_admin;
    }

    public function data(Request $request): array
    {
        return [
            'users' => User::query()->latest('created_at')->take(5)->get(),
        ];
    }
}
