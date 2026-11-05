<?php

namespace Plugins\Forms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'types',
    'statuses',
    'default_template_id',
    'default_view',
    'per_page',
])]
class FormModuleSetting extends Model
{
    /**
     * The user the settings belong to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'types' => 'array',
            'statuses' => 'array',
            'default_template_id' => 'integer',
            'per_page' => 'integer',
        ];
    }
}
