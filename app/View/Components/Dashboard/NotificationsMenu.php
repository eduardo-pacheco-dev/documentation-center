<?php

namespace App\View\Components\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NotificationsMenu extends Component
{
    /**
     * Render the notification bell with the latest notifications.
     */
    public function render(): View
    {
        $user = auth()->user();

        if ($user === null) {
            return view('components.dashboard.notifications-menu', [
                'notifications' => collect(),
                'unreadCount' => 0,
            ]);
        }

        return view('components.dashboard.notifications-menu', [
            'notifications' => $user->notifications()->latest()->limit(6)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
