<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Document;
use App\Models\Project;
use App\Models\ShortLink;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the administration dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $statusCounts = $this->workOrderStatusCounts($user);

        return view('admin.dashboard', [
            'greeting' => $this->greeting($user),
            'totalClients' => Client::query()->ownedBy($user)->count(),
            'openWorkOrders' => $statusCounts[WorkOrderStatus::Open->value]
                + $statusCounts[WorkOrderStatus::InProgress->value],
            'activeProjectsCount' => Project::query()
                ->accessibleBy($user)
                ->where('status', ProjectStatus::Active)
                ->count(),
            'totalDocuments' => Document::query()->whereBelongsTo($user)->count(),
            'statusCounts' => $statusCounts,
            'workOrdersTotal' => array_sum($statusCounts),
            'recentWorkOrders' => WorkOrder::query()
                ->ownedBy($user)
                ->with('client:id,name')
                ->latest('created_at')
                ->take(6)
                ->get(),
            'activeProjects' => Project::query()
                ->accessibleBy($user)
                ->where('status', ProjectStatus::Active)
                ->withCount('tasks')
                ->orderByDesc('updated_at')
                ->take(5)
                ->get(),
            'recentDocuments' => Document::query()
                ->whereBelongsTo($user)
                ->latest('created_at')
                ->take(5)
                ->get(),
            'recentLinks' => ShortLink::query()
                ->whereBelongsTo($user)
                ->latest('created_at')
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * Count the user's work orders grouped by status.
     *
     * @return array<string, int>
     */
    private function workOrderStatusCounts(User $user): array
    {
        $counts = WorkOrder::query()
            ->ownedBy($user)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $result = [];

        foreach (WorkOrderStatus::cases() as $status) {
            $result[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $result;
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
