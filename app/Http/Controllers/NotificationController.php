<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Show the authenticated user's notifications.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications.index', [
            'notifications' => $user->notifications()->latest()->paginate(20),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function read(Request $request, string $notification): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail()
            ->markAsRead();

        return back()->with('status', 'Notificação marcada como lida.');
    }

    /**
     * Mark every notification as read.
     */
    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Todas as notificações foram marcadas como lidas.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->whereKey($notification)
            ->delete();

        return back()->with('status', 'Notificação removida.');
    }

    /**
     * Delete every notification of the authenticated user.
     */
    public function clear(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return back()->with('status', 'Todas as notificações foram removidas.');
    }
}
