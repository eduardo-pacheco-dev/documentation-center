<?php

namespace Plugins\Nokia\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteRejection;

/**
 * Fired when a rejection is registered for one of the site checklists.
 */
class NokiaSiteRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly NokiaSite $site,
        public readonly NokiaSiteRejection $rejection,
    ) {}
}
