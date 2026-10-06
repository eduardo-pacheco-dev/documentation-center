<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the administration dashboard.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalAdmins' => User::where('is_admin', true)->count(),
            'recentSignups' => User::latest('created_at')->take(5)->get(['id', 'name', 'created_at']),
            'signupsThisWeek' => User::where('created_at', '>=', now()->subWeek())->count(),
        ]);
    }
}
