<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the administration dashboard.
     */
    public function index(Request $request): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalAdmins' => User::where('is_admin', true)->count(),
            'recentSignups' => User::latest('created_at')->take(5)->get(['id', 'name', 'created_at']),
            'signupsThisWeek' => User::where('created_at', '>=', now()->subWeek())->count(),
            'totalLinks' => ShortLink::whereBelongsTo($request->user())->count(),
            'activeLinks' => ShortLink::whereBelongsTo($request->user())->where('is_active', true)->count(),
            'receivedDocuments' => Document::whereHas(
                'shortLink',
                fn ($query) => $query->whereBelongsTo($request->user()),
            )->count(),
        ]);
    }
}
