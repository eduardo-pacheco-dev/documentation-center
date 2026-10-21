<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Colaborador;
use App\Models\Document;
use App\Models\Erb;
use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\Project;
use App\Models\RadioLink;
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
            'statusCounts' => $statusCounts,
            'workOrdersTotal' => array_sum($statusCounts),
            'counts' => [
                'clients' => Client::query()->ownedBy($user)->count(),
                'openWorkOrders' => $statusCounts[WorkOrderStatus::Open->value]
                    + $statusCounts[WorkOrderStatus::InProgress->value],
                'projects' => Project::query()
                    ->accessibleBy($user)
                    ->where('status', ProjectStatus::Active)
                    ->count(),
                'erbs' => Erb::query()->ownedBy($user)->count(),
                'radioLinks' => RadioLink::query()->ownedBy($user)->count(),
                'catalogItems' => CatalogItem::query()->ownedBy($user)->count(),
                'forms' => Form::query()->ownedBy($user)->count(),
                'formTemplates' => FormTemplate::query()->count(),
                'colaboradores' => Colaborador::query()->ownedBy($user)->count(),
                'documents' => Document::query()->whereBelongsTo($user)->count(),
                'links' => ShortLink::query()->whereBelongsTo($user)->count(),
                'users' => $user->is_admin ? User::query()->count() : null,
            ],
            'recentWorkOrders' => WorkOrder::query()
                ->ownedBy($user)
                ->with('client:id,name')
                ->latest('created_at')
                ->take(6)
                ->get(),
            'recentDocuments' => Document::query()
                ->whereBelongsTo($user)
                ->latest('created_at')
                ->take(5)
                ->get(),
            'monthlyWorkOrders' => $this->monthlyWorkOrderSeries($user),
        ]);
    }

    /**
     * Build the work order count series for the last six months.
     *
     * @return array<int, array{label: string, count: int}>
     */
    private function monthlyWorkOrderSeries(User $user): array
    {
        $currentMonth = now()->startOfMonth();

        $counts = WorkOrder::query()
            ->ownedBy($user)
            ->where('created_at', '>=', $currentMonth->copy()->subMonths(5))
            ->get(['created_at'])
            ->countBy(fn (WorkOrder $workOrder): string => $workOrder->created_at->format('Y-m'));

        return collect(range(5, 0))
            ->map(function (int $offset) use ($counts, $currentMonth): array {
                $month = $currentMonth->copy()->subMonths($offset);

                return [
                    'label' => $month->locale('pt_BR')->translatedFormat('M'),
                    'count' => (int) $counts->get($month->format('Y-m'), 0),
                ];
            })
            ->all();
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
