<?php

namespace Plugins\FollowUp\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email'])]
class FollowUpSummaryRecipient extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'follow_up_summary_recipients';
}
