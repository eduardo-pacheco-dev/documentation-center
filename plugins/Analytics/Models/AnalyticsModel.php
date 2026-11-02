<?php

namespace Plugins\Analytics\Models;

use Illuminate\Database\Eloquent\Model;
use Plugins\Analytics\Support\AnalyticsDatabase;

/**
 * Base model for the analytics plugin.
 *
 * Every model uses the dedicated analytics connection when one is
 * configured, falling back to the application connection otherwise.
 */
abstract class AnalyticsModel extends Model
{
    /**
     * The connection used by the analytics models.
     */
    public function getConnectionName(): ?string
    {
        return $this->connection ?? AnalyticsDatabase::connection();
    }
}
