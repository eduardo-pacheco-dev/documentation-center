<?php

namespace Plugins\TeamTracking\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;
use Plugins\TeamTracking\Enums\CheckInStatus;
use Plugins\TeamTracking\Models\Team;
use Plugins\TeamTracking\Models\TeamCheckIn;

class TeamTrackingWidget extends Widget
{
    public function id(): string
    {
        return 'team-tracking.overview';
    }

    public function title(): string
    {
        return 'Equipes';
    }

    public function view(): string
    {
        return 'team-tracking::widgets.overview';
    }

    public function order(): int
    {
        return 47;
    }

    public function columnSpan(): int
    {
        return 2;
    }

    public function data(Request $request): array
    {
        $teams = Team::query()
            ->active()
            ->with(['leader', 'latestCheckIn'])
            ->withCount('members')
            ->orderBy('name')
            ->take((int) config('team-tracking.widget_limit', 5))
            ->get();

        return [
            'teams' => $teams,
            'activeCount' => Team::query()->active()->count(),
            'attentionCount' => TeamCheckIn::query()
                ->where('status', CheckInStatus::Attention->value)
                ->count(),
        ];
    }
}
