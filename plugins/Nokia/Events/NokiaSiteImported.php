<?php

namespace Plugins\Nokia\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Plugins\Nokia\Models\NokiaSite;

/**
 * Fired when a site is imported from a spreadsheet.
 *
 * Other plugins can listen to this event instead of depending on the Nokia
 * importer or its models directly.
 */
class NokiaSiteImported
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly NokiaSite $site,
    ) {}
}
