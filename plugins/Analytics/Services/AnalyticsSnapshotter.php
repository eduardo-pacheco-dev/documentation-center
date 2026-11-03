<?php

namespace Plugins\Analytics\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Plugins\Analytics\Models\AnalyticsSnapshot;

class AnalyticsSnapshotter
{
    /**
     * Store (or refresh) today's snapshot for the given user or globally.
     */
    public function capture(?User $user = null, ?CarbonImmutable $today = null): AnalyticsSnapshot
    {
        $today ??= CarbonImmutable::now();
        $from = $today->subDays(30)->startOfDay();
        $to = $today->endOfDay();

        $metrics = new AnalyticsMetrics($from, $to, $user);
        $capturedOn = $today->toDateString();

        $snapshot = AnalyticsSnapshot::query()
            ->where('user_id', $user?->getKey())
            ->whereDate('captured_on', $capturedOn)
            ->first();

        if ($snapshot !== null) {
            $snapshot->update(['metrics' => $metrics->importSummary()]);

            return $snapshot;
        }

        return AnalyticsSnapshot::query()->create([
            'user_id' => $user?->getKey(),
            'captured_on' => $capturedOn,
            'metrics' => $metrics->importSummary(),
        ]);
    }
}
