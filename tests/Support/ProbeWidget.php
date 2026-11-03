<?php

namespace Tests\Support;

use App\Dashboard\Widget;
use Illuminate\Http\Request;

/**
 * Configurable widget used to exercise the dashboard widget registry.
 */
class ProbeWidget extends Widget
{
    public function __construct(
        private readonly string $identifier,
        private readonly int $position = 0,
        private readonly bool $isVisible = true,
        private readonly int $span = 1,
    ) {}

    public function id(): string
    {
        return $this->identifier;
    }

    public function title(): string
    {
        return $this->identifier;
    }

    public function view(): string
    {
        return 'admin.widgets.user-count';
    }

    public function order(): int
    {
        return $this->position;
    }

    public function columnSpan(): int
    {
        return $this->span;
    }

    public function visible(Request $request): bool
    {
        return $this->isVisible;
    }
}
