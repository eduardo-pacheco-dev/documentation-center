<?php

namespace App\Models;

use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'project_id',
    'code',
    'name',
    'type',
    'status',
    'steps',
    'fields',
])]
class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Boot the model and assign a sequential code when none is provided.
     */
    protected static function booted(): void
    {
        static::creating(function (Form $form): void {
            if (blank($form->code) && $form->user_id !== null) {
                $form->code = static::generateCode($form->user);
            }
        });
    }

    /**
     * The user that owns the form.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The project the form belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The evaluations recorded for the form.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(FormEvaluation::class);
    }

    /**
     * Restrict a query to the forms the user owns.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /**
     * Generate the next sequential code for the user's forms.
     */
    public static function generateCode(User $user): string
    {
        $sequence = $user->forms()->withTrashed()->count() + 1;

        do {
            $code = 'FRM-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($user->forms()->withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'fields' => 'array',
        ];
    }
}
