<?php

namespace Plugins\Analytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Resolves the period and scope shared by the dashboard and the API.
 */
class AnalyticsContext
{
    public function __construct(
        public readonly AnalyticsMetrics $metrics,
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly int $days,
        public readonly string $scope,
    ) {}

    /**
     * Build the context from the request query string.
     */
    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'period' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'scope' => ['nullable', 'in:user,global'],
        ]);

        $days = (int) ($validated['period'] ?? (config('analytics.periods')[0] ?? 30));

        $to = isset($validated['to'])
            ? CarbonImmutable::parse($validated['to'])->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $from = isset($validated['from'])
            ? CarbonImmutable::parse($validated['from'])->startOfDay()
            : $to->subDays(max(1, $days) - 1)->startOfDay();

        $maxDays = (int) config('analytics.max_period_days', 366);

        if ($from->diffInDays($to) > $maxDays) {
            $from = $to->subDays($maxDays - 1)->startOfDay();
        }

        $scope = ($validated['scope'] ?? 'user') === 'global' ? 'global' : 'user';

        if ($scope === 'global') {
            abort_unless($request->user()?->is_admin === true, 403);
        }

        $metrics = new AnalyticsMetrics($from, $to, $scope === 'global' ? null : $request->user());

        return new self(
            $metrics,
            $from,
            $to,
            (int) $from->diffInDays($to) + 1,
            $scope,
        );
    }

    /**
     * Query parameters that should be forwarded between analytics requests.
     *
     * @return array<string, string|int>
     */
    public function query(): array
    {
        return array_filter([
            'period' => $this->days,
            'scope' => $this->scope === 'global' ? 'global' : null,
        ]);
    }

    /**
     * The JSON meta block shared by the API responses.
     *
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return [
            'period' => [
                'from' => $this->from->toDateString(),
                'to' => $this->to->toDateString(),
                'days' => $this->days,
            ],
            'scope' => $this->scope,
            'generated_at' => CarbonImmutable::now()->toIso8601String(),
        ];
    }
}
