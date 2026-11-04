<?php

namespace Plugins\FollowUp\Widgets;

use App\Dashboard\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Plugins\FollowUp\Models\FollowUp;

class OverdueFollowUpsWidget extends Widget
{
    public function id(): string
    {
        return 'follow-up.overdue';
    }

    public function title(): string
    {
        return 'Follow-ups';
    }

    public function view(): string
    {
        return 'follow-up::widgets.overview';
    }

    public function order(): int
    {
        return 45;
    }

    public function columnSpan(): int
    {
        return 2;
    }

    /**
     * The data passed to the widget view.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        $email = $request->user()?->email;

        $assignedToMe = fn (Builder $query): Builder => $query
            ->whereHas('assignee', fn (Builder $query) => $query->where('email', $email));

        $followUps = FollowUp::query()
            ->open()
            ->tap($assignedToMe)
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->take((int) config('follow-up.widget_limit', 5))
            ->get();

        return [
            'followUps' => $followUps,
            'openCount' => FollowUp::query()->open()->tap($assignedToMe)->count(),
            'overdueCount' => FollowUp::query()->overdue()->tap($assignedToMe)->count(),
        ];
    }
}
