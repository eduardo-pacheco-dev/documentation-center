<?php

namespace App\Services\Scheduling;

use Carbon\CarbonImmutable;

/**
 * The dates calculated for a single task.
 */
class TaskSchedule
{
    public function __construct(
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $finish,
        public readonly int $totalSlack,
        public readonly int $freeSlack,
        public readonly bool $critical,
    ) {}
}
