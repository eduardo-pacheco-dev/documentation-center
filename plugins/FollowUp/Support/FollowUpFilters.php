<?php

namespace Plugins\FollowUp\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;

final class FollowUpFilters
{
    /**
     * Apply the search, status, priority and assignee filters to the query.
     */
    public static function apply(Builder $query, Request $request): Builder
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $priority = $request->string('priority')->trim()->toString();
        $assigned = $request->string('assigned')->trim()->toString();

        if ($search !== '') {
            $query->matching($search);
        }

        if (in_array($status, FollowUpStatus::values(), true)) {
            $query->where('status', $status);
        }

        if (in_array($priority, FollowUpPriority::values(), true)) {
            $query->where('priority', $priority);
        }

        if ($assigned === 'me') {
            $query->whereHas('assignee', fn (Builder $query) => $query->where('email', $request->user()->email));
        } elseif (ctype_digit($assigned)) {
            $query->where('assignee_id', (int) $assigned);
        }

        return $query;
    }
}
