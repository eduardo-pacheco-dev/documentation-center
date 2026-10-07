<?php

namespace App\Models;

use App\Enums\ShortLinkType;
use Database\Factories\ShortLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'code',
    'type',
    'title',
    'description',
    'is_active',
    'expires_at',
    'max_uses',
    'used_count',
    'password',
])]
class ShortLink extends Model
{
    /** @use HasFactory<ShortLinkFactory> */
    use HasFactory;

    /**
     * Generate a short code that is not yet in use.
     */
    public static function createCode(int $length = 8): string
    {
        do {
            $code = Str::random($length);
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * The owner that created the link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The documents attached to the link.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * The public URL that resolves to this link.
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('public.short-links.show', $this->code));
    }

    /**
     * Whether the link already expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Whether the link already consumed all allowed accesses.
     */
    public function hasReachedAccessLimit(): bool
    {
        return $this->max_uses !== null && $this->used_count >= $this->max_uses;
    }

    /**
     * Whether access to the link requires a password.
     */
    public function needsPassword(): bool
    {
        return $this->password !== null;
    }

    /**
     * Whether the link can still be opened by the public.
     */
    public function isUsable(): bool
    {
        return $this->is_active
            && ! $this->isExpired()
            && ! $this->hasReachedAccessLimit();
    }

    /**
     * Whether the given user owns the link.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    /**
     * Atomically count a new access to the link.
     */
    public function recordAccess(): void
    {
        static::query()->whereKey($this->getKey())->increment('used_count');
    }

    /**
     * Remove attached files before the link is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (ShortLink $shortLink): void {
            $shortLink->documents->each->delete();
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
            'type' => ShortLinkType::class,
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'max_uses' => 'integer',
            'used_count' => 'integer',
        ];
    }
}
