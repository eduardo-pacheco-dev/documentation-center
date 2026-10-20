<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'code',
    'name',
    'manufacturer',
    'model',
    'category',
    'description',
    'notes',
    'status',
])]
class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'equipments';

    /**
     * Assign a sequential code when one is not provided.
     */
    protected static function booted(): void
    {
        static::saving(function (Equipment $equipment): void {
            if (blank($equipment->code) && $equipment->user_id !== null) {
                $equipment->code = static::generateCode($equipment->user);
            }
        });
    }

    /**
     * Generate the next sequential code for the user's equipments.
     */
    public static function generateCode(User $user): string
    {
        $sequence = $user->equipments()->withTrashed()->count() + 1;

        do {
            $code = 'EQP-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($user->equipments()->withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /**
     * The user that registered the equipment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The datasheets and files attached to the equipment.
     *
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->latest();
    }

    /**
     * Restrict a query to the equipments the user owns.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /**
     * Filter the equipments by a search term.
     */
    public function scopeMatching(Builder $query, string $term): void
    {
        $like = "%{$term}%";

        $query->where(function (Builder $query) use ($like): void {
            $query->where('name', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('manufacturer', 'like', $like)
                ->orWhere('model', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
        ];
    }
}
