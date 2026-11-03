<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Plugins\Plugin;
use App\Plugins\PluginRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the administration dashboard.
     */
    public function index(Request $request, PluginRepository $plugins): View
    {
        $user = $request->user();

        $installedPlugins = $plugins->all();

        return view('admin.dashboard', [
            'greeting' => $this->greeting($user),
            'counts' => [
                'users' => $user->is_admin ? User::query()->count() : null,
                'plugins' => $installedPlugins->count(),
                'enabledPlugins' => $installedPlugins
                    ->filter(fn (Plugin $plugin): bool => $plugin->enabled())
                    ->count(),
            ],
            'recentUsers' => $user->is_admin
                ? User::query()->latest('created_at')->take(5)->get()
                : collect(),
        ]);
    }

    /**
     * Build a time based greeting for the user.
     */
    private function greeting(User $user): string
    {
        $greeting = match (true) {
            now()->hour < 12 => 'Bom dia',
            now()->hour < 18 => 'Boa tarde',
            default => 'Boa noite',
        };

        return $greeting.', '.str($user->name)->before(' ').'!';
    }
}
