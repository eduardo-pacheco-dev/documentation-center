<?php

namespace App\Models;

use App\Enums\AgendaCalendarRole;
use App\Enums\AgendaColor;
use Database\Factories\AgendaCalendarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'description',
    'color',
    'timezone',
    'is_default',
    'is_visible',
    'position',
    'default_reminders',
])]
class AgendaCalendar extends Model
{
    /** @use HasFactory<AgendaCalendarFactory> */
    use HasFactory;

    /**
     * The user that owns the calendar.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The events placed on the calendar.
     *
     * @return HasMany<AgendaEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(AgendaEvent::class);
    }

    /**
     * The shares granted on the calendar.
     *
     * @return HasMany<AgendaCalendarShare, $this>
     */
    public function shares(): HasMany
    {
        return $this->hasMany(AgendaCalendarShare::class);
    }

    /**
     * The users the calendar is shared with.
     *
     * @return BelongsToMany<User, $this>
     */
    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agenda_calendar_shares')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Limit the query to the calendars the user can see (owned or shared).
     *
     * @param  Builder<AgendaCalendar>  $query
     */
    public function scopeAccessibleBy(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->getKey())
                ->orWhereHas('shares', fn (Builder $query) => $query->where('user_id', $user->getKey()));
        });
    }

    /**
     * Limit the query to the calendars shown on the grid.
     *
     * @param  Builder<AgendaCalendar>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /**
     * Determine whether the calendar belongs to the given user.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    /**
     * Resolve the effective role the user has on the calendar.
     */
    public function roleFor(User $user): ?AgendaCalendarRole
    {
        if ($this->isOwnedBy($user)) {
            return AgendaCalendarRole::Owner;
        }

        $role = $this->shares->firstWhere('user_id', $user->getKey())?->role;

        return $role instanceof AgendaCalendarRole ? $role : null;
    }

    /**
     * Determine whether the user may create and edit events on the calendar.
     */
    public function canWrite(User $user): bool
    {
        return $this->roleFor($user)?->canWrite() === true;
    }

    /**
     * Determine whether the user may read the event details of the calendar.
     */
    public function canReadDetails(User $user): bool
    {
        return $this->roleFor($user)?->canReadDetails() === true;
    }

    /**
     * The default reminders applied to new events of the calendar.
     *
     * @return list<array{method: string, minutes_before: int|null}>
     */
    public function defaultReminders(): array
    {
        return array_values($this->default_reminders ?? []);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'color' => AgendaColor::class,
            'is_default' => 'boolean',
            'is_visible' => 'boolean',
            'position' => 'integer',
            'default_reminders' => 'array',
        ];
    }
}
