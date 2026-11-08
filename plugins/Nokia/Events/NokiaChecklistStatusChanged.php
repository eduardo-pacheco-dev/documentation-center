<?php

namespace Plugins\Nokia\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Plugins\Nokia\Models\NokiaSite;

/**
 * Fired when a site checklist item changes status.
 *
 * The field is the checklist column (e.g. "checklist_fam_entrega") and the
 * statuses match NokiaSite::CHECKLIST_STATUSES.
 */
class NokiaChecklistStatusChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly NokiaSite $site,
        public readonly string $field,
        public readonly string $previousStatus,
        public readonly string $status,
    ) {}
}
